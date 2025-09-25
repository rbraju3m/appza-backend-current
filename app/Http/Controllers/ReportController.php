<?php

namespace App\Http\Controllers;

use App\Models\FluentInfo;
use App\Models\FreeTrial;
use App\Models\Lead;
use App\Models\LicenseMessage;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Log;

class ReportController extends Controller
{

    public function freeTrialReport(Request $request)
    {
        $products = FluentInfo::getProductTab(); // product_slug => product_name
        $reportTypes = ['daily' => 'Daily', 'monthly' => 'Monthly', 'yearly' => 'Yearly'];

        $activeTab = $request->query('tab') ?: ($products->keys()->first() ?? null);
        $type = $request->get('type', 'daily');
        $search = $request->get('search');

        // Define date format based on type
        $dateFormat = match ($type) {
            'monthly' => '%Y-%m',
            'yearly'  => '%Y',
            default   => '%Y-%m-%d',
        };

        // --- Free Trials ---
        $freeTrialsQuery = DB::table('appza_free_trial_request')
            ->select('product_slug', DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"), DB::raw('COUNT(*) as total'))
            ->groupBy('period', 'product_slug')
            ->orderBy('period');

        if ($activeTab) $freeTrialsQuery->where('product_slug', $activeTab);
        if ($search) {
            if ($type === 'daily') $freeTrialsQuery->whereDate('created_at', $search);
            elseif ($type === 'monthly') $freeTrialsQuery->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$search]);
            elseif ($type === 'yearly') $freeTrialsQuery->whereYear('created_at', $search);
        }
        $freeTrials = $freeTrialsQuery->get();

        // --- Leads ---
        $leadsQuery = DB::table('appfiy_customer_leads')
            ->select('plugin_name as product_slug', DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"), DB::raw('COUNT(*) as total'))
            ->groupBy('period', 'plugin_name')
            ->orderBy('period');

        if ($activeTab) $leadsQuery->where('plugin_name', $activeTab);
        if ($search) {
            if ($type === 'daily') $leadsQuery->whereDate('created_at', $search);
            elseif ($type === 'monthly') $leadsQuery->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$search]);
            elseif ($type === 'yearly') $leadsQuery->whereYear('created_at', $search);
        }
        $leads = $leadsQuery->get();

        // --- Premium ---
        $premiumQuery = DB::table('appza_free_trial_request')
            ->where('is_fluent_license_check', 1)
            ->select('product_slug', DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"), DB::raw('COUNT(*) as total'))
            ->groupBy('period', 'product_slug')
            ->orderBy('period');

        if ($activeTab) $premiumQuery->where('product_slug', $activeTab);
        if ($search) {
            if ($type === 'daily') $premiumQuery->whereDate('created_at', $search);
            elseif ($type === 'monthly') $premiumQuery->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$search]);
            elseif ($type === 'yearly') $premiumQuery->whereYear('created_at', $search);
        }
        $premium = $premiumQuery->get();

        // --- Merge into single reportData ---
        $periods = $freeTrials->pluck('period')->merge($leads->pluck('period'))->merge($premium->pluck('period'))->unique()->sort()->values();

        $reportData = $periods->map(function($period) use ($freeTrials, $leads, $premium) {
            return [
                'period' => $period,
                'free_trials' => $freeTrials->where('period', $period)->sum('total'),
                'leads' => $leads->where('period', $period)->sum('total'),
                'premium' => $premium->where('period', $period)->sum('total'),
            ];
        });

        return view('reports.free-trial', compact(
            'products', 'activeTab', 'type', 'search', 'reportTypes', 'reportData'
        ));
    }


    /*public function leadWiseReport(Request $request)
    {
        $activeTab = $request->get('tab'); // Selected product tab
        $search = $request->get('search');
        $perPage = 20;

        $products = FluentInfo::getProductTab();

        if (!$activeTab) {
            $activeTab = $products->keys()->first();
        }

        // Query leads with optional search, filtered by active product
        $leadsQuery = DB::table('appfiy_customer_leads as l')
            ->when($activeTab, function($q) use ($activeTab) {
                $q->where('l.plugin_name', $activeTab);
            })
            ->leftJoin('appza_free_trial_request as f', function($join) use ($activeTab) {
                $join->on('l.email', '=', 'f.email')->whereColumn('l.domain', '=', 'f.site_url')->where('f.product_slug', $activeTab);
            })
            ->leftJoin('appza_fluent_license_info as p', function($join) {
                $join->on('f.premium_license_id', '=', 'p.id');
            })
            ->select(
                'l.*',
                'f.product_slug',
                'f.product_title',
                'f.site_url',
                'f.status',
                'f.expiration_date',
                'f.grace_period_date',
                'f.license_key',
                'f.is_fluent_license_check',
                'f.activations_count',
                'f.activation_limit',
                'p.license_key as p_license_key',
                'p.activation_hash as p_activation_hash',
                'p.activation_limit as p_activation_limit',
                'p.activations_count as p_activations_count',
                'p.product_title as p_product_title',
                'p.variation_title as p_variation_title',
                'p.expiration_date as p_expiration_date',
            )
            ->when($search, function($q) use ($search) {
                $q->where(function($q2) use ($search) {
                    $q2->where('l.first_name', 'like', "%{$search}%")
                        ->orWhere('l.last_name', 'like', "%{$search}%")
                        ->orWhere('l.email', 'like', "%{$search}%")
                        ->orWhere('l.domain', 'like', "%{$search}%");
                });
            })
            ->orderBy('l.created_at', 'desc');

        $leads = $leadsQuery->paginate($perPage)->withQueryString();

        return view('reports.lead_wise', compact('products', 'leads', 'activeTab', 'search'));
    }*/

    public function leadWiseReport(Request $request)
    {
        $activeTab = $request->get('tab');
        $search = $request->get('search');
        $perPage = 20;

        $products = FluentInfo::getProductTab();

        if (!$activeTab) {
            $activeTab = $products->keys()->first();
        }

        // Subquery to get latest lead per email + domain
        $latestLeadSub = DB::table('appfiy_customer_leads')
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('email', 'domain');

        // Main query
        $leadsQuery = DB::table('appfiy_customer_leads as l')
            ->joinSub($latestLeadSub, 'latest', function($join) {
                $join->on('l.id', '=', 'latest.id');
            })
            ->when($activeTab, function($q) use ($activeTab) {
                $q->where('l.plugin_name', $activeTab);
            })
            ->leftJoin('appza_free_trial_request as f', function($join) use ($activeTab) {
                $join->on('l.email', '=', 'f.email')
                    ->whereColumn('l.domain', '=', 'f.site_url')
                    ->where('f.product_slug', $activeTab);
            })
            ->leftJoin('appza_fluent_license_info as p', function($join) {
                $join->on('f.premium_license_id', '=', 'p.id');
            })
            ->select(
                'l.*',
                'f.product_slug',
                'f.product_title',
                'f.site_url',
                'f.status',
                'f.expiration_date',
                'f.grace_period_date',
                'f.license_key',
                'f.is_fluent_license_check',
                'f.activations_count',
                'f.activation_limit',
                'p.license_key as p_license_key',
                'p.activation_hash as p_activation_hash',
                'p.activation_limit as p_activation_limit',
                'p.activations_count as p_activations_count',
                'p.product_title as p_product_title',
                'p.variation_title as p_variation_title',
                'p.expiration_date as p_expiration_date'
            )
            ->when($search, function($q) use ($search) {
                $q->where(function($q2) use ($search) {
                    $q2->where('l.first_name', 'like', "%{$search}%")
                        ->orWhere('l.last_name', 'like', "%{$search}%")
                        ->orWhere('l.email', 'like', "%{$search}%")
                        ->orWhere('l.domain', 'like', "%{$search}%");
                });
            })
            ->orderBy('l.created_at', 'desc');

        $leads = $leadsQuery->paginate($perPage)->withQueryString();

        return view('reports.lead_wise', compact('products', 'leads', 'activeTab', 'search'));
    }


    public function leadWiseGraph(Request $request)
    {
        $activeTab = $request->get('tab')?$request->get('tab'):'fcom_mobile';
        $search = $request->get('search');

        $products = FluentInfo::getProductTab();
        if (!$activeTab) {
            $activeTab = $products->keys()->first();
        }

        // Latest leads subquery
        $latestLeadSub = DB::table('appfiy_customer_leads')
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('email', 'domain');

        $leadsQuery = DB::table('appfiy_customer_leads as l')
            ->joinSub($latestLeadSub, 'latest', function($join) {
                $join->on('l.id', '=', 'latest.id');
            })
            ->when($activeTab, function($q) use ($activeTab) {
                $q->where('l.plugin_name', $activeTab);
            })
            ->leftJoin('appza_free_trial_request as f', function($join) use ($activeTab) {
                $join->on('l.email', '=', 'f.email')
                    ->whereColumn('l.domain', '=', 'f.site_url')
                    ->where('f.product_slug', $activeTab);
            })
            ->leftJoin('appza_fluent_license_info as p', function($join) {
                $join->on('f.premium_license_id', '=', 'p.id');
            })
            ->select(
                'l.*',
                'f.status',
                'f.expiration_date',
                'f.grace_period_date',
                'f.is_fluent_license_check'
            )
            ->when($search, function($q) use ($search) {
                $q->where(function($q2) use ($search) {
                    $q2->where('l.first_name', 'like', "%{$search}%")
                        ->orWhere('l.last_name', 'like', "%{$search}%")
                        ->orWhere('l.email', 'like', "%{$search}%")
                        ->orWhere('l.domain', 'like', "%{$search}%");
                });
            });

        $leads = $leadsQuery->get();

        // Prepare chart data
        $now = \Carbon\Carbon::now();
        $trialActive = $trialGrace = $trialExpired = $noTrial = 0;
        $premiumActive = $notUpgraded = 0;

        foreach ($leads as $lead) {
            if ($lead->is_fluent_license_check == 1) {
                $premiumActive++;
            } else {
                $notUpgraded++;
            }

            if ($lead->expiration_date) {
                $exp = \Carbon\Carbon::parse($lead->expiration_date);
                $grace = \Carbon\Carbon::parse($lead->grace_period_date);
                if ($now->lt($exp)) $trialActive++;
                elseif ($now->lt($grace)) $trialGrace++;
                else $trialExpired++;
            } else {
                $noTrial++;
            }
        }

//        dump($trialActive, $trialGrace, $trialExpired, $noTrial);

        return view('reports.lead_wise_graph', compact(
            'products', 'activeTab', 'search',
            'trialActive', 'trialGrace', 'trialExpired', 'noTrial',
            'premiumActive', 'notUpgraded'
        ));
    }



}

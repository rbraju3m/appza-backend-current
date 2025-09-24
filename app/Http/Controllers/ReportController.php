<?php

namespace App\Http\Controllers;

use App\Models\FluentInfo;
use App\Models\FreeTrial;
use App\Models\Lead;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Log;

class ReportController extends Controller
{
    public function freeTrialReport(Request $request)
    {
        $type = $request->get('type', 'daily');

        // Define date format based on type
        $dateFormat = match ($type) {
            'monthly' => '%Y-%m',
            'yearly'  => '%Y',
            default   => '%Y-%m-%d',
        };

        // Free Trials
        $freeTrials = DB::table('appza_free_trial_request')
            ->select(DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"), DB::raw('COUNT(*) as total'))
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Leads
        $leads = DB::table('appfiy_customer_leads')
            ->select(DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"), DB::raw('COUNT(*) as total'))
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Premium Licenses
        $premium = DB::table('appza_free_trial_request')
            ->where('is_fluent_license_check', 1)
            ->select(DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"), DB::raw('COUNT(*) as total'))
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Funnel summary
        $funnelData = [
            'free_trials' => DB::table('appza_free_trial_request')->count(),
            'leads'       => DB::table('appfiy_customer_leads')->count(),
            'premium'     => DB::table('appza_free_trial_request')->where('is_fluent_license_check', 1)->count(),
        ];

        return view('reports.index', compact('freeTrials', 'leads', 'premium', 'funnelData', 'type'));
    }


    public function leadWiseReport(Request $request)
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
            ->leftJoin('appza_free_trial_request as f', function($join) use ($activeTab) {
                $join->on('l.email', '=', 'f.email')
                    ->where('f.product_slug', $activeTab);
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
                'f.activation_limit'
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



}

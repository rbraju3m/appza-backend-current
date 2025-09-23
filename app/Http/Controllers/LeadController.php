<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetupRequest;
use App\Models\FluentInfo;
use App\Models\FreeTrial;
use App\Models\Lead;
use App\Models\LicenseLogic;
use App\Models\Setup;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Log;

class LeadController extends Controller
{
    use ValidatesRequests;


    /**
     * Display a listing of the resource.
     */

    /*public function index(Request $request)
    {
        $products = FluentInfo::getProductTab(); // now keyed by slug

        $leads = [];
        foreach ($products as $slug => $name) {
            $leads[$slug] = Lead::where('is_active', 1)
                ->where('plugin_name', $name->product_slug)
                ->orderByDesc('id')
                ->paginate(20, ['*'], $name->product_slug . '_page');
        }

        return view('lead.index', compact('leads', 'products'));
    }*/

    public function index(Request $request)
    {
        $products = FluentInfo::getProductTab(); // keyed by slug

        // active tab comes from query ?tab=xxx or default first slug
        $activeTab = $request->query('tab') ?: $products->keys()->first();
        $search = $request->query('search');

        $leads = [];
        foreach ($products as $slug => $name) {
            // Base query
            $query = Lead::where('is_active', 1)
                ->where('plugin_name', $name->product_slug)
                ->orderByDesc('id');

            // Apply search only for active tab
            if ($activeTab === $name->product_slug && $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('domain', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhere('appza_hash', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Finalize pagination
            $leads[$slug] = $query
                ->paginate(20, ['*'], $name->product_slug . '_page')
                ->appends([
                    'tab' => $slug,
                    'search' => $search, // 👈 keep search text during pagination
                ]);
        }

        return view('lead.index', compact('leads', 'products', 'activeTab', 'search'));
    }




    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $lead = Lead::findOrFail($id);

                // Find and delete related FreeTrial
                $license = FreeTrial::where('site_url', $lead->domain)
                    ->where('product_slug', $lead->plugin_name)
                    ->where('is_fluent_license_check', 0)
                    ->first();

                if ($license) {
                    $license->delete();
                }

                // Delete the lead
                $lead->delete();
            });

            return redirect()
                ->route('lead_list')
                ->with('delete', 'Lead deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Error deleting lead', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('lead_list')
                ->with('delete', 'Failed to delete the lead. Please try again.');
        }
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\FluentInfo;
use App\Models\FreeTrial;
use App\Models\Lead;
use App\Models\Setup;
use Exception;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Log;

class FreeTrialController extends Controller
{
    use ValidatesRequests;


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Key = slug, Value = name
        $products = FluentInfo::select('product_slug', 'product_name')
            ->distinct('product_slug')
            ->pluck('product_name', 'product_slug');

        $freeTrials = [];
        foreach ($products as $slug => $name) {
            $freeTrials[$slug] = FreeTrial::where('is_active', 1)
                ->where('product_slug', $slug) // grouped by slug
                ->orderByDesc('id')
                ->paginate(20, ['*'], $slug . '_page');
        }

        return view('free-trial.index', compact('freeTrials', 'products'));
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $freeTrial = FreeTrial::findOrFail($id);

                if ($freeTrial->is_fluent_license_check == 0) {
                    // Find and delete related FreeTrial
                    $lead = Lead::where('domain', $freeTrial->site_url)
                        ->where('plugin_name', $freeTrial->product_slug)
                        ->first();

                    $lead?->delete();

                    // Delete the lead
                    $freeTrial->delete();
                }
            });

            return redirect()
                ->route('free_trial_list')
                ->with('delete', 'Free trial deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Error deleting lead', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('free_trial_list')
                ->with('delete', 'Failed to delete the Free trial. Please try again.');
        }
    }

}

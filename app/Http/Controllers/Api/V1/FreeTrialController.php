<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\FreeTrialRequest;
use App\Http\Resources\FreeTrialResource;
use App\Http\Resources\LeadResource;
use App\Models\FreeTrial;
use App\Models\Lead;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class FreeTrialController extends Controller
{
    protected function normalizeUrl($url)
    {
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = strtolower($parsed['host'] ?? '');

        return $host ? "{$scheme}://{$host}" : null;
    }

    public function store(FreeTrialRequest $request, $plugin)
    {
        // Validate the product first
        $validProducts = ['appza', 'lazy_task', 'fcom_mobile'];
        if (!in_array($plugin, $validProducts)) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_PRODUCT',
                    'message' => 'Invalid product specified.',
                    'available_products' => $validProducts
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();

        try {
            $inputs = $request->validated();
            $inputs['site_url'] = $this->normalizeUrl($inputs['site_url']);
            $inputs['product_slug'] = $plugin;
            $inputs['expiration_date'] = now()->addDays(7);
            $inputs['grace_period_date'] = now()->addDays(14);

            // Check for existing trial with lock to prevent race conditions
            $trialExists = FreeTrial::where('product_slug', $plugin)
                ->where('site_url', $inputs['site_url'])
                ->lockForUpdate()
                ->exists();

            if ($trialExists) {
                DB::rollBack();
                return response()->json([
                    'status' => 400,
                    'message' => 'Free trial for this product and URL has already been used. Please purchase the product to continue using the app.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $data = FreeTrial::create($inputs);

            DB::commit();

            return response()->json(
                (new FreeTrialResource($data))->resolve(),
                Response::HTTP_OK,
                [],
                JSON_UNESCAPED_SLASHES
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => 'Failed to create free trial. Please try again later.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

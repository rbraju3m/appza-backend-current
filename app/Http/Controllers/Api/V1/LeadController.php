<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\LeadResource;
use App\Models\FreeTrial;
use App\Models\Lead;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class LeadController extends Controller
{
    use ValidatesRequests;
    protected $authorization;
    protected $domain;
    protected $pluginName;

    public function __construct(Request $request){
        $data = Lead::checkAuthorization($request);
        $this->authorization = $data['auth_type'] ?? false;
        $this->domain = $data['domain'] ?? '';
        $this->pluginName = $data['plugin_name'] ?? '';
    }

    protected function normalizeUrl($url)
    {
        // Add scheme if missing
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        $parsed = parse_url($url);

        $scheme = $parsed['scheme'] ?? 'https';
        $host = strtolower($parsed['host'] ?? '');

        return $host ? $scheme . '://' . $host : null;
    }

    /*public function store(Request $request, $plugin)
    {
        // Perform validation
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'domain' => 'required|url',
        ], [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email is not valid.',
            'domain.required' => 'Domain is required.',
            'domain.url' => 'Domain is not valid.',
        ]);

        // Use database transaction to ensure data consistency
        return DB::transaction(function () use ($request, $plugin) {
            // Prepare input data
            $input = $request->only('first_name', 'last_name', 'email', 'domain', 'note');
            $input['plugin_name'] = $plugin;
            $input['appza_hash'] = Hash::make($input['email'] . $input['domain']);
            $input['domain'] = $this->normalizeUrl($input['domain']);

            // Create Lead record
            $data = Lead::create($input);

            if ($data){
                $checkFreeTrialExists = FreeTrial::where('site_url', $input['domain'])->where('product_slug',$plugin)->first();
                if (!$checkFreeTrialExists){
                    $inputs['name'] = $input['first_name'] . ' ' . $input['last_name'];
                    $inputs['email'] = $input['email'];
                    $inputs['site_url'] = $this->normalizeUrl($input['domain']);
                    $inputs['product_slug'] = $plugin;
                    $inputs['expiration_date'] = now()->addDays(7);
                    $inputs['grace_period_date'] = now()->addDays(14);
                    FreeTrial::create($inputs);
                }
            }

            // Return the resource-based response with JSON_UNESCAPED_SLASHES
            return response()->json(
                (new LeadResource($data))->resolve(),
                Response::HTTP_OK,
                [],
                JSON_UNESCAPED_SLASHES
            );
        });
    }*/

    public function store(Request $request, $plugin)
    {
        // Perform validation
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'domain' => 'required|url',
        ], [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email is not valid.',
            'domain.required' => 'Domain is required.',
            'domain.url' => 'Domain is not valid.',
        ]);

        try {
            return DB::transaction(function () use ($request, $plugin) {
                // Prepare input data
                $input = $request->only('first_name', 'last_name', 'email', 'domain', 'note');
                $input['plugin_name'] = $plugin;
                $input['appza_hash'] = Hash::make($input['email'] . $input['domain']);
                $input['domain'] = $this->normalizeUrl($input['domain']);

                // Create Lead record
                $data = Lead::create($input);

                if (!$data) {
                    return response()->json([
                        'status' => 500,
                        'message' => 'Failed to create lead.'
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                // Free Trial creation (if not exists)
                $checkFreeTrialExists = FreeTrial::where('site_url', $input['domain'])
                    ->where('product_slug', $plugin)
                    ->first();

                if (!$checkFreeTrialExists) {
                    $inputs['name'] = $input['first_name'] . ' ' . $input['last_name'];
                    $inputs['email'] = $input['email'];
                    $inputs['site_url'] = $this->normalizeUrl($input['domain']);
                    $inputs['product_slug'] = $plugin;
                    $inputs['expiration_date'] = now()->addDays(7);
                    $inputs['grace_period_date'] = now()->addDays(14);

                    FreeTrial::create($inputs);
                }

                // Return success response
                return response()->json(
                    (new LeadResource($data))->resolve(),
                    Response::HTTP_OK,
                    [],
                    JSON_UNESCAPED_SLASHES
                );
            });
        } catch (\Exception $e) {
            // Catch transaction failure
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong while processing the lead.',
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}

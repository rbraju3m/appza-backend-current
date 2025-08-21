<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\BuildDomain;
use App\Models\FluentInfo;
use App\Models\Lead;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LicenseController_bk extends Controller
{
    use ValidatesRequests;
    protected $authorization;
    protected $domain;
    protected $pluginName;
    protected $email;

    public function __construct(Request $request){
        $data = Lead::checkAuthorization($request);
        $this->authorization = ($data && $data['auth_type'])?$data['auth_type']:false;
        $this->domain = ($data && $data['domain'])?$data['domain']:'';
        $this->email = ($data && $data['email'])?$data['email']:'';
        $this->pluginName = ($data && $data['plugin_name'])?$data['plugin_name']:'';
    }


    public function check(Request $request)
    {
        // Helper function for JSON responses
        $jsonResponse = function ($statusCode, $message, $additionalData = []) use ($request) {
            return new JsonResponse(array_merge([
                'status' => $statusCode,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => $message,
            ], $additionalData), $statusCode);
        };

        // Check for authorization
        if (!$this->authorization) {
            return $jsonResponse(Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        // Validate required parameters
        $requiredFields = ['license_key', 'site_url'];
        foreach ($requiredFields as $field) {
            if (!$request->get($field)) {
                return $jsonResponse(Response::HTTP_NOT_FOUND, ucfirst(str_replace('_', ' ', $field)) . ' missing.');
            }
        }

        // Setup API parameters
        $getFluentInfo = FluentInfo::where('product_slug', $this->pluginName)->where('is_active',true)->first();
        if (!$getFluentInfo) {
            return $jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The fluent information not set in the configuration.');
        }
        $fluentApiUrl = $getFluentInfo->api_url;
        $fluentItemId = $getFluentInfo->item_id;

        if (!config('app.is_fluent_check')){
            /* START manually added for fluent issue & after fluent is okay it will be remove*/
            return $jsonResponse(Response::HTTP_OK, 'Your License key is valid.', ['data' => [
                "success" => true,
                "license"=> "valid",
                "item_id"=> $fluentItemId,
                "item_name"=> "",
                "license_limit"=> "25",
                "site_count"=> 1,
                "expires"=> "2028-01-01 06:19:01",
                "activations_left"=> 24,
                "customer_name"=> "Testing All Product",
                "customer_email"=> "test@test.com",
                "price_id"=> "9",
                "checksum"=> "4b096d7dc1f3dc6fe741f57c8b45f6cb",
                "fluent_check" => config('app.is_fluent_check')?"True":"False"." by raju",
            ]]);
            /* END manually added for fluent issue & after fluent is okay it will be remove*/
        }

        // Check if it's null
        if (is_null($fluentApiUrl)) {
            return $jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The fluent api url is null or not set in the configuration.');
        }

        // Check if it's null
        if (is_null($fluentItemId)) {
            return $jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The fluent item id is null or not set in the configuration.');
        }

        // Check if it's a number (integer or float)
        if (!is_numeric($fluentItemId)) {
            return $jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The fluent item id is not a valid number.');
        }

        $params = [
            'fluent_cart_action' => 'check_license',
            'license' => $request->get('license_key'),
            'item_id' => $fluentItemId,
            'url' => $request->get('site_url'),
        ];

        // Send API Request
        try {
            $response = Http::get($fluentApiUrl, $params);
        } catch (\Exception $e) {
            return $jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Failed to connect to the license server.');
        }

        // Decode response
        $data = json_decode($response->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Invalid response from license server.');
        }

        // Handle license errors
        if (!$data['success'] ?? false) {
            $messages = [
                'missing' => 'License not found.',
                'expired' => 'License has expired.',
                'disabled' => 'License key revoked.',
                'invalid_item_id' => 'Item id is invalid.',
            ];
            $message = $messages[$data['error']] ?? 'License not valid.';
            return $jsonResponse(Response::HTTP_NOT_FOUND, $message);
        }

        // Success response
        return $jsonResponse(Response::HTTP_OK, 'Your License key is valid.', ['data' => $data]);
    }

    public function appLicenseCheck(Request $request)
    {
        // Helper function for JSON responses
        $jsonResponse = function ($statusCode, $message, $additionalData = []) use ($request) {
            return new JsonResponse(array_merge([
                'status' => $statusCode,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => $message,
            ], $additionalData), $statusCode);
        };

        // Validate required parameters
        $requiredFields = ['site_url'];
        foreach ($requiredFields as $field) {
            if (!$request->get($field)) {
                return $jsonResponse(Response::HTTP_NOT_FOUND, ucfirst(str_replace('_', ' ', $field)) . ' missing.');
            }
        }

        $getBuildDomain = BuildDomain::where([['site_url', $request->get('site_url')],['is_app_license_check',1]])->first();
        if (empty($getBuildDomain)) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Active domain not found.');
        }

        // Setup API parameters
        $getFluentInfo = FluentInfo::where('product_slug', $getBuildDomain->plugin_name)->where('is_active',true)->first();
        if (!$getFluentInfo) {
            return $jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The fluent information not set in the configuration.');
        }
        $fluentApiUrl = $getFluentInfo->api_url;
        $fluentItemId = $getFluentInfo->item_id;

        if (!config('app.is_fluent_check')){
            /* START manually added for fluent issue & after fluent is okay it will be remove*/
            return $jsonResponse(Response::HTTP_OK, 'Your License key is valid.', ['data' => [
                "success" => true,
                "license"=> "valid",
                "item_id"=> $fluentItemId,
                "item_name"=> "",
                "license_limit"=> "25",
                "site_count"=> 1,
                "expires"=> "2028-01-01 06:19:01",
                "activations_left"=> 24,
                "customer_name"=> "Testing All Product",
                "customer_email"=> "test@test.com",
                "price_id"=> "9",
                "checksum"=> "4b096d7dc1f3dc6fe741f57c8b45f6cb",
                "fluent_check" => config('app.is_fluent_check')?"True":"False"." by raju",
            ]]);
            /* END manually added for fluent issue & after fluent is okay it will be remove*/
        }

        // Check if it's null
        if (is_null($fluentApiUrl)) {
            return $jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The fluent api url is null or not set in the configuration.');
        }

        // Check if it's null
        if (is_null($fluentItemId)) {
            return $jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The fluent item id is null or not set in the configuration.');
        }

        // Check if it's a number (integer or float)
        if (!is_numeric($fluentItemId)) {
            return $jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The fluent item id is not a valid number.');
        }

        $params = [
            'fluent_cart_action' => 'check_license',
            'license' => $getBuildDomain->license_key,
            'item_id' => $fluentItemId,
            'url' => $request->get('site_url'),
        ];

        // Send API Request
        try {
            $response = Http::get($fluentApiUrl, $params);
        } catch (\Exception $e) {
            return $jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Failed to connect to the license server.');
        }

        // Decode response
        $data = json_decode($response->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Invalid response from license server.');
        }

        // Handle license errors
        if (!$data['success'] ?? false) {
            $messages = [
                'missing' => 'License not found.',
                'expired' => 'License has expired.',
                'disabled' => 'License key revoked.',
                'invalid_item_id' => 'Item id is invalid.',
            ];
            $message = $messages[$data['error']] ?? 'License not valid.';
            return $jsonResponse(Response::HTTP_NOT_FOUND, $message);
        }

        // Success response
        return $jsonResponse(Response::HTTP_OK, 'Your License key is valid.', ['data' => $data]);
    }


    /**
     * @throws \Throwable
     * @throws ConnectionException
     */
    public function activate(Request $request)
    {
        // Helper function for JSON responses
        $jsonResponse = function ($statusCode, $message, $additionalData = []) use ($request) {
            return new JsonResponse(array_merge([
                'status' => $statusCode,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => $message,
            ], $additionalData), $statusCode);
        };

        // Check for authorization
        if (!$this->authorization) {
            return $jsonResponse(Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'site_url' => 'required',
            'license_key' => 'required',
        ], [
            'site_url.required' => 'Site URL is required.',
            'license_key.required' => 'License Key is required.',
        ]);

        if ($validator->fails()) {
            return $jsonResponse(Response::HTTP_BAD_REQUEST, 'Validation Error', ['errors' => $validator->errors()]);
        }
        $data = $request->only('site_url', 'license_key', 'email');

        $getFluentInfo = FluentInfo::where('product_slug', $this->pluginName)->where('is_active',true)->first();

        if (!$getFluentInfo) {
            return $jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The fluent information not set in the configuration.');
        }
        $fluentApiUrl = $getFluentInfo->api_url;
        $fluentItemId = $getFluentInfo->item_id;

        if (!config('app.is_fluent_check')) {
            /* START manually added for fluent issue & after fluent is okay it will be remove*/
            // Check or Create BuildDomain Entry
            BuildDomain::firstOrCreate(
                [
                    'site_url' => $data['site_url'],
                    'license_key' => $data['license_key'],
                ],
                [
                    'package_name' => 'com.' . $this->getSubdomainAndDomain($data['site_url']) . '.live',
                    'email' => $data['email'] ?? $this->email,
                    'plugin_name' => $this->pluginName,
                    'fluent_item_id' => '540',
                ]
            );

            return $jsonResponse(Response::HTTP_OK, 'Your License key has been activated successfully.', ['data' => [
                "success" => true,
                "license" => "valid",
                "item_id" => $fluentItemId,
                "item_name" => "",
                "license_limit" => "25",
                "site_count" => 1,
                "expires" => "2028-01-01 06:19:01",
                "activations_left" => 24,
                "customer_name" => "Testing All Product",
                "customer_email" => "test@test.com",
                "price_id" => "9",
                "checksum" => "4b096d7dc1f3dc6fe741f57c8b45f6cb",
                "fluent_check" => config('app.is_fluent_check')?"True":"False"." by raju",
            ]]);
            /* END manually added for fluent issue & after fluent is okay it will be remove*/
        }

        // Check if it's null
        if (is_null($fluentItemId)) {
            return $jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The fluent item id is null or not set in the configuration.');
        }

        // Check if it's a number (integer or float)
        if (!is_numeric($fluentItemId)) {
            return $jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The fluent item id is not a valid number.');
        }

        $apiInput = [
            'url' => $data['site_url'],
            'license' => $data['license_key'],
            'item_id' => $fluentItemId,
            'fluent_cart_action' => 'activate_license',
        ];


        // Check if it's null
        if (is_null($fluentApiUrl)) {
            return $jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The fluent api url is null or not set in the configuration.');
        }

        try {
            $response = Http::get($fluentApiUrl, $apiInput);
        } catch (\Exception $e) {
            return $jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Failed to connect to the license server.');
        }

        // Decode API Response
        $res = json_decode($response->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Invalid response from license server.');
        }

        // License Activation Errors
        if (!$res['success'] ?? false) {
            $errorMessages = [
                'missing' => "License doesn't exist.",
                'invalid_item_id' => 'Item ID is invalid.',
                'missing_url' => 'Site URL is not provided.',
                'license_not_activable' => "Attempting to activate a bundle's parent license.",
                'disabled' => 'License key revoked.',
                'no_activations_left' => 'No activations left.',
                'expired' => 'License has expired.',
                'site_inactive' => 'Site is not active for this license.',
                'invalid' => 'License key does not ma72ca56675130125fa6fa8ea9f2306d16tch.',
            ];

            $errorMessage = $errorMessages[$res['error']] ?? 'License not valid.';
            return $jsonResponse(Response::HTTP_NOT_FOUND, $errorMessage);
        }


        try {
            // Begin transaction
            DB::beginTransaction();

            $buildDomain = BuildDomain::updateOrCreate(
                [
                    'site_url' => $data['site_url'],
                    'license_key' => $data['license_key'],
                ],
                [
                    'package_name' => 'com.' . $this->getSubdomainAndDomain($data['site_url']) . '.live',
                    'email' => $data['email'] ?? $this->email,
                    'plugin_name' => $this->pluginName,
                    'fluent_item_id' => $fluentItemId,
                    'is_app_license_check' => 1
                ]
            );

            // Update all other records with same site_url in a single query
            BuildDomain::where('site_url', $buildDomain->site_url)
                ->where('id', '!=', $buildDomain->id)
                ->update(['is_app_license_check' => 0]);

            // Commit the transaction
            DB::commit();

            return $jsonResponse(Response::HTTP_OK, 'Your License key has been activated successfully.', [
                'data' => $res
            ]);

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            // Handle or log the exception
            \Log::error('Failed to update build domain: ' . $e->getMessage());

            throw $e; // Or handle the error according to your application's needs
        }
    }


    function getSubdomainAndDomain($url) {
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['host'])) {
            $host = $parsedUrl['host'];

            // Remove top-level domains (e.g., .com, .co, .net)
            $hostParts = explode('.', $host);
            array_pop($hostParts); // Remove the last part (top-level domain)

            // Rejoin the remaining parts and remove any non-alphabetic characters
            $cleaned = preg_replace('/[^a-zA-Z]/', '', implode('', $hostParts));
            return strtolower($cleaned); // Return as lowercase only letters
        }
        return null;
    }

}

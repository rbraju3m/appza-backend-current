<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\BuildDomain;
use App\Models\FluentInfo;
use App\Models\FluentLicenseInfo;
use App\Models\FreeTrial;
use App\Models\Lead;
use App\Models\PopupMessage;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LicenseController extends Controller
{
    use ValidatesRequests;

    protected $authorization;
    protected $domain;
    protected $pluginName;
    protected $email;

    public function __construct(Request $request)
    {
        $data = Lead::checkAuthorization($request);
        $this->authorization = ($data && $data['auth_type']) ? $data['auth_type'] : false;
        $this->domain = $data['domain'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->pluginName = $data['plugin_name'] ?? '';
    }

    protected function jsonResponse(Request $request, int $statusCode, string $message, array $additionalData = []): JsonResponse
    {
        return response()->json(array_merge([
            'status' => $statusCode,
            'url' => $request->getUri(),
            'method' => $request->getMethod(),
            'message' => $message,
        ], $additionalData), $statusCode);
    }

    protected function getFluentErrorMessage($code, $default = 'License validation failed.')
    {
        $messages = [
            'validation_error' => "Please provide the license key, URL, and item ID.",
            'key_mismatch' => "This license key doesn't match the product. Please check your key.",
            'license_error' => "Invalid license key for this product. Please verify your key.",
            'license_not_found' => "License key not found. Please make sure it is correct.",
            'license_expired' => "Your license key has expired. Please renew or buy a new one.",
            'activation_error' => "Unable to activate. Your license may be expired.",
            'activation_limit_exceeded' => "Activation limit reached. Please upgrade or get a new license.",
            'site_not_found' => "This website is not registered under your license.",
            'deactivation_error' => "Unable to deactivate the license. Please try again later.",
            'product_not_found' => "Product not found. Please check the product ID.",
            'license_settings_not_found' => "License settings not configured for this product.",
            'license_not_enabled' => "Licensing hasn’t been enabled for this product.",
            'invalid_package_data' => "The package data is invalid. Please check the details.",
            'expired_license' => "Your license key is invalid or expired.",
            'downloadable_file_not_found' => "No downloadable file available for this product."
        ];

        return $messages[$code] ?? $default;
    }

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

    protected function getSubdomainAndDomain($url)
    {
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['host'])) {
            $hostParts = explode('.', $parsedUrl['host']);
            array_pop($hostParts);
            $cleaned = preg_replace('/[^a-zA-Z]/', '', implode('', $hostParts));
            return strtolower($cleaned);
        }
        return null;
    }

    public function activate(Request $request)
    {
        if (!$this->authorization) {
            return $this->jsonResponse($request, Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'site_url' => 'required',
            'license_key' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse($request, Response::HTTP_BAD_REQUEST, 'Validation Error', ['errors' => $validator->errors()]);
        }

        $data = $request->only('site_url', 'license_key', 'email');
        $normalizedSiteUrl = $this->normalizeUrl($data['site_url']);

        $fluentInfo = FluentInfo::where('product_slug', $this->pluginName)->where('is_active', true)->first();

        if (!$fluentInfo || !is_numeric($fluentInfo->item_id) || !$fluentInfo->api_url) {
            return $this->jsonResponse($request, Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid Fluent plugin configuration.');
        }

        $apiInput = [
            'site_url' => $normalizedSiteUrl,
            'license_key' => $data['license_key'],
            'item_id' => $fluentInfo->item_id,
            'fluent-cart' => 'activate_license',
        ];

        try {
            $response = Http::timeout(10)->get($fluentInfo->api_url, $apiInput);
            $res = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($res) ||
            !$res['success'] ?? false || ($res['status'] ?? 'invalid') !== 'valid') {

                $error = $res['error_type'] ?? $res['error'] ?? null;
                $errorMessage = $this->getFluentErrorMessage($error, $res['message'] ?? 'License activation failed.');

                return $this->jsonResponse($request, Response::HTTP_UNPROCESSABLE_ENTITY, $errorMessage,['error_type' => $error]);
            }

        } catch (Exception $e) {
            Log::error('API call failed', ['error' => $e->getMessage(), 'params' => $apiInput]);
            return $this->jsonResponse($request, Response::HTTP_INTERNAL_SERVER_ERROR, 'Failed to connect to the license server.');
        }

        try {
            DB::beginTransaction();

            $buildDomain = BuildDomain::updateOrCreate(
                ['site_url' => $normalizedSiteUrl, 'license_key' => $data['license_key']],
                [
                    'package_name' => 'com.' . $this->getSubdomainAndDomain($normalizedSiteUrl) . '.live',
                    'email' => $data['email'] ?? $this->email,
                    'plugin_name' => $this->pluginName,
                    'fluent_item_id' => $fluentInfo->item_id,
                    'is_app_license_check' => 1,
                    'is_deactivated' => 0
                ]
            );

            BuildDomain::where('site_url', $normalizedSiteUrl)
                ->where('id', '!=', $buildDomain->id)
                ->update(['is_app_license_check' => 0]);

            FluentLicenseInfo::updateOrCreate(
                [
                    'build_domain_id' => $buildDomain->id,
                    'site_url' => $normalizedSiteUrl,
                    'product_id' => $fluentInfo->item_id,
                    'license_key' => $data['license_key'],
                    'activation_hash' => $res['activation_hash'] ?? null,
                ],
                [
                    'variation_id' => $res['variation_id'] ?? null,
                    'product_title' => $res['product_title'] ?? null,
                    'variation_title' => $res['variation_title'] ?? null,
                    'activation_limit' => $res['activation_limit'] ?? null,
                    'activations_count' => $res['activations_count'] ?? null,
                    'expiration_date' => $res['expiration_date'] ?? null,
                ]
            );

            DB::commit();

            return $this->jsonResponse($request, Response::HTTP_OK, 'Your License key has been activated successfully.', ['data' => $res]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('DB transaction failed', ['error' => $e->getMessage()]);
            return $this->jsonResponse($request, Response::HTTP_INTERNAL_SERVER_ERROR, 'Failed to save license info.');
        }
    }

    public function versionCheck(Request $request)
    {
        if (!$this->authorization) {
            return $this->jsonResponse($request, Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        $validator = Validator::make($request->all(), ['license_key' => 'required']);

        if ($validator->fails()) {
            return $this->jsonResponse($request, Response::HTTP_BAD_REQUEST, 'Validation Error', ['errors' => $validator->errors()]);
        }

        $key = $request->get('license_key');

        $fluentInfo = FluentInfo::where('product_slug', $this->pluginName)->where('is_active', true)->first();

        if (!$fluentInfo || !is_numeric($fluentInfo->item_id) || !$fluentInfo->api_url) {
            return $this->jsonResponse($request, Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid Fluent plugin configuration.');
        }

        $activationHash = FluentLicenseInfo::where('license_key', $key)->value('activation_hash');

        if (is_null($activationHash)) {
            return $this->jsonResponse($request, Response::HTTP_NOT_FOUND, 'License record not found. Please activate first.');
        }

        $params = [
            'activation_hash' => $activationHash,
            'license_key' => $key,
            'item_id' => $fluentInfo->item_id,
            'fluent-cart' => 'get_license_version',
        ];

        try {
            $response = Http::timeout(10)->get($fluentInfo->api_url, $params);
            $data = $response->json();

            if (!is_array($data) || !($data['success'] ?? false)) {
                $error = $data['error_type'] ?? $data['error'] ?? null;
                $message = $this->getFluentErrorMessage($error, $data['message'] ?? 'License version fetch failed.');
                return $this->jsonResponse($request, Response::HTTP_NOT_FOUND, $message);
            }

            return $this->jsonResponse($request, Response::HTTP_OK, 'License version fetched successfully.', ['version_data' => $data]);

        } catch (Exception $e) {
            Log::error('Version check error', ['error' => $e->getMessage()]);
            return $this->jsonResponse($request, Response::HTTP_INTERNAL_SERVER_ERROR, 'Failed to connect to license server.');
        }
    }

    public function check(Request $request)
    {
        if (!$this->authorization) {
            return $this->jsonResponse($request, Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'site_url' => 'required',
            'license_key' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse($request, Response::HTTP_BAD_REQUEST, 'Validation Error', ['errors' => $validator->errors()]);
        }

        $siteUrl = $this->normalizeUrl($request->get('site_url'));
        $key = $request->get('license_key');

        $fluentInfo = FluentInfo::where('product_slug', $this->pluginName)->where('is_active', true)->first();
        if (!$fluentInfo || !is_numeric($fluentInfo->item_id)) {
            return $this->jsonResponse($request, Response::HTTP_UNPROCESSABLE_ENTITY, 'Fluent plugin configuration error.');
        }

        $activationHash = FluentLicenseInfo::where('license_key', $key)->where('site_url', $siteUrl)->value('activation_hash');

        if (is_null($activationHash)) {
            return $this->jsonResponse($request, Response::HTTP_NOT_FOUND, 'License record not found for this site.');
        }

        $params = [
            'fluent-cart' => 'check_license',
            'license_key' => $key,
            'activation_hash' => $activationHash,
            'item_id' => $fluentInfo->item_id,
            'site_url' => $siteUrl,
        ];

        try {
            $response = Http::timeout(10)->get($fluentInfo->api_url, $params);
            $data = $response->json();

            if (!is_array($data) || !($data['success'] ?? false) || ($data['status'] ?? 'invalid') !== 'valid') {
                $error = $data['error_type'] ?? $data['error'] ?? null;
                $message = $this->getFluentErrorMessage($error, $data['message'] ?? 'License is not valid.');
                return $this->jsonResponse($request, Response::HTTP_NOT_FOUND, $message,['error_type' => $error]);
            }

            return $this->jsonResponse($request, Response::HTTP_OK, 'Your License key is valid.', ['data' => $data]);

        } catch (Exception $e) {
            Log::error('License check error', ['error' => $e->getMessage()]);
            return $this->jsonResponse($request, Response::HTTP_INTERNAL_SERVER_ERROR, 'Failed to connect to license server.');
        }
    }

    public function deactivate(Request $request)
    {
        if (!$this->authorization) {
            return $this->jsonResponse($request, Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'site_url' => 'required',
            'license_key' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse($request, Response::HTTP_BAD_REQUEST, 'Validation Error', ['errors' => $validator->errors()]);
        }

        $siteUrl = $this->normalizeUrl($request->get('site_url'));
        $key = $request->get('license_key');

        $fluentInfo = FluentInfo::where('product_slug', $this->pluginName)->where('is_active', true)->first();
        if (!$fluentInfo || !is_numeric($fluentInfo->item_id)) {
            return $this->jsonResponse($request, Response::HTTP_UNPROCESSABLE_ENTITY, 'Fluent plugin configuration error.');
        }

        $activationHash = FluentLicenseInfo::where('license_key', $key)->where('site_url', $siteUrl)->value('activation_hash');

        if (is_null($activationHash)) {
            return $this->jsonResponse($request, Response::HTTP_NOT_FOUND, 'License record not found for this site.');
        }

        $params = [
            'fluent-cart' => 'deactivate_license',
            'license_key' => $key,
            'activation_hash' => $activationHash,
            'item_id' => $fluentInfo->item_id,
            'site_url' => $siteUrl,
        ];

        try {
            $response = Http::timeout(10)->get($fluentInfo->api_url, $params);
            $data = $response->json();

            if (!is_array($data) || !($data['success'] ?? false) || ($data['status'] ?? 'invalid') !== 'deactivated') {
                $error = $data['error_type'] ?? $data['error'] ?? null;
                $message = $this->getFluentErrorMessage($error, $data['message'] ?? 'License is not deactivated.');
                return $this->jsonResponse($request, Response::HTTP_NOT_FOUND, $message,['error_type' => $error]);
            }

            $findBuildDomain = BuildDomain::where('site_url', $siteUrl)->where('license_key',$key)->first();
            $findBuildDomain->update(['is_deactivated' => true]);

            return $this->jsonResponse($request, Response::HTTP_OK, 'Your License key is deactivate.', ['data' => $data]);

        } catch (Exception $e) {
            Log::error('License check error', ['error' => $e->getMessage()]);
            return $this->jsonResponse($request, Response::HTTP_INTERNAL_SERVER_ERROR, 'Failed to connect to license server.');
        }
    }


    public function appLicenseCheck1(Request $request)
    {
        // Validate required parameter
        $validator = Validator::make($request->all(), [
            'site_url' => 'required',
            'product' => 'required',
        ]);

        // Validate the product first
        $validProducts = ['appza', 'lazy_task', 'fcom_mobile'];
        if (!in_array($request->get('product'), $validProducts)) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid product specified.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Fetch popup messages and format them for response
        $popupMessages = PopupMessage::where('is_active', true)->get()->map(function ($message) {
            return [
                'type' => $message->message_type,
                'message' => $message->message,
            ];
        })->toArray();

        if ($validator->fails()) {
            return $this->jsonResponse($request, Response::HTTP_BAD_REQUEST, 'Validation Error', [
                'errors' => $validator->errors(),
                'popup_message' => $popupMessages
            ]);
        }

        $siteUrl = $this->normalizeUrl($request->get('site_url'));

        // START CHECK TO FREE TRIAL
        $freeTrial = FreeTrial::where('site_url', $siteUrl)
                                ->where('product_slug',$request->get('product'))
                                ->where('grace_period_date','>=', date('Y-m-d'))
                                ->where('is_active', true)
                                ->first();

        if ($freeTrial){
            $data = [
              'success' => true,
              'status' => $freeTrial->status,
              'activation_limit' => $freeTrial->activation_limit,
              'activation_hash' => $freeTrial->activation_hash,
              'activations_count' => $freeTrial->activations_count,
              'license_key' => $freeTrial->license_key,
              'expiration_date' => $freeTrial->expiration_date,
              'product_id' => $freeTrial->product_id,
              'variation_id' => $freeTrial->variation_id,
              'variation_title' => $freeTrial->variation_title,
              'product_title' => $freeTrial->product_title,
            ];

            return $this->jsonResponse(
                $request,
                Response::HTTP_OK,
                'Your License key is valid.',
                ['data' => $data, 'popup_message' => $popupMessages]
            );
        }

        /*START TO FLUENT CHECK*/
        // Get active build domain
        $getBuildDomain = BuildDomain::where([
            ['site_url', $siteUrl],
            ['is_app_license_check', 1]
        ])->first();

        if (!$getBuildDomain) {
            return $this->jsonResponse(
                $request,
                Response::HTTP_NOT_FOUND,
                'Active domain not found.',
                ['popup_message' => $popupMessages]
            );
        }

        // Get Fluent plugin info for the plugin attached to domain
        $fluentInfo = FluentInfo::where('product_slug', $getBuildDomain->plugin_name)
            ->where('is_active', true)
            ->first();

        if (!$fluentInfo || !is_numeric($fluentInfo->item_id) || !$fluentInfo->api_url) {
            return $this->jsonResponse($request, Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid Fluent plugin configuration.',['popup_message' => $popupMessages]);
        }

        // Get activation hash
        $activationHash = FluentLicenseInfo::where('license_key', $getBuildDomain->license_key)
            ->where('site_url', $siteUrl)
            ->value('activation_hash');

        if (is_null($activationHash)) {
            return $this->jsonResponse($request, Response::HTTP_NOT_FOUND, 'License data not found. Please activate first.',['popup_message' => $popupMessages]);
        }

        // Prepare external request
        $params = [
            'fluent-cart'     => 'check_license',
            'license_key'     => $getBuildDomain->license_key,
            'activation_hash' => $activationHash,
            'item_id'         => $fluentInfo->item_id,
            'site_url'        => $siteUrl,
        ];

        try {
            $response = Http::timeout(10)->get($fluentInfo->api_url, $params);
            $data = $response->json();

            if (!is_array($data) || !($data['success'] ?? false) || ($data['status'] ?? 'invalid') !== 'valid') {
                $error = $data['error_type'] ?? $data['error'] ?? null;
                $message = $this->getFluentErrorMessage($error, $data['message'] ?? 'License is invalid.');

                return $this->jsonResponse($request, Response::HTTP_NOT_FOUND, $message,[
                    'popup_message' => $popupMessages,
                    'error_type' => $error
                ]);
            }

            // Add popup messages to data
            return $this->jsonResponse(
                $request,
                Response::HTTP_OK,
                'Your License key is valid.',
                ['data' => $data, 'popup_message' => $popupMessages]
            );

        } catch (Exception $e) {
            Log::error('App license check failed', ['error' => $e->getMessage()]);
            return $this->jsonResponse($request, Response::HTTP_INTERNAL_SERVER_ERROR, 'Failed to connect to license server.',['popup_message' => $popupMessages]);
        }
    }

    public function appLicenseCheck(Request $request)
    {
        // Validate required parameters
        $validator = Validator::make($request->all(), [
            'site_url' => 'required|url',
            'product' => 'required|string',
        ]);

        // Validate product
        $validProducts = ['appza', 'lazy_task', 'fcom_mobile'];
        $product = $request->get('product');

        if (!in_array($product, $validProducts)) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid product specified.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Fetch popup messages once (cached for better performance)
        $popupMessages = Cache::remember('active_popup_messages', 3600, function () {
            return PopupMessage::where('is_active', true)
                ->get()
                ->map(function ($message) {
                    return [
                        'type' => $message->message_type,
                        'message' => $message->message,
                    ];
                })->toArray();
        });

        if ($validator->fails()) {
            return $this->jsonResponse(
                $request,
                Response::HTTP_BAD_REQUEST,
                'Validation Error',
                [
                    'errors' => $validator->errors(),
                    'popup_message' => $popupMessages
                ]
            );
        }

        $siteUrl = $this->normalizeUrl($request->get('site_url'));

        // Check Free Trial with better query optimization
        $freeTrial = FreeTrial::where('site_url', $siteUrl)
            ->where('product_slug', $product)
            #->where('grace_period_date', '>=', now()->format('Y-m-d'))
            ->where('expiration_date', '>=', now()->format('Y-m-d'))
            ->where('is_active', true)
            ->select([
                'status', 'activation_limit', 'activation_hash', 'activations_count',
                'license_key', 'expiration_date', 'product_id', 'variation_id',
                'variation_title', 'product_title','grace_period_date'
            ])
            ->first();

        if ($freeTrial) {
            return $this->jsonResponse(
                $request,
                Response::HTTP_OK,
                'Your free trial license is valid.',
                [
                    'data' => $freeTrial,
                    'license_type' => 'free_trial',
                    'popup_message' => $popupMessages
                ]
            );
        }

        // Check Fluent License with transaction and better error handling
        return DB::transaction(function () use ($request, $siteUrl, $product, $popupMessages) {
            $getBuildDomain = BuildDomain::where('site_url', $siteUrl)
                ->where('is_app_license_check', true)
                ->where('plugin_name', $product)
                ->lockForUpdate() // Prevent race conditions
                ->first();

            if (!$getBuildDomain) {
                return $this->jsonResponse(
                    $request,
                    Response::HTTP_NOT_FOUND,
                    'Active domain not found for this product.',
                    ['popup_message' => $popupMessages]
                );
            }

            $fluentInfo = FluentInfo::where('product_slug', $getBuildDomain->plugin_name)
                ->where('is_active', true)
                ->first();

            if (!$fluentInfo) {
                return $this->jsonResponse(
                    $request,
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    'Fluent plugin not configured for this product.',
                    ['popup_message' => $popupMessages]
                );
            }

            if (!is_numeric($fluentInfo->item_id) || !filter_var($fluentInfo->api_url, FILTER_VALIDATE_URL)) {
                return $this->jsonResponse(
                    $request,
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    'Invalid Fluent plugin configuration.',
                    ['popup_message' => $popupMessages]
                );
            }

            $activationHash = FluentLicenseInfo::where('license_key', $getBuildDomain->license_key)
                ->where('site_url', $siteUrl)
                ->value('activation_hash');

            if (!$activationHash) {
                return $this->jsonResponse(
                    $request,
                    Response::HTTP_NOT_FOUND,
                    'License data not found. Please activate first.',
                    ['popup_message' => $popupMessages]
                );
            }

            // External API call with better timeout and retry handling
            try {
                $response = Http::timeout(15)
                    ->retry(2, 100)
                    ->withHeaders([
                        'User-Agent' => 'AppLicenseCheck/1.0',
                        'Accept' => 'application/json',
                    ])
                    ->get($fluentInfo->api_url, [
                        'fluent-cart' => 'check_license',
                        'license_key' => $getBuildDomain->license_key,
                        'activation_hash' => $activationHash,
                        'item_id' => (int) $fluentInfo->item_id,
                        'site_url' => $siteUrl,
                    ]);

                if (!$response->successful()) {
                    throw new Exception('API responded with status: ' . $response->status());
                }

                $data = $response->json();

                if (!is_array($data) || !($data['success'] ?? false) || ($data['status'] ?? 'invalid') !== 'valid') {
                    $error = $data['error_type'] ?? $data['error'] ?? 'unknown_error';
                    $message = $this->getFluentErrorMessage($error, $data['message'] ?? 'License is invalid.');

                    return $this->jsonResponse(
                        $request,
                        Response::HTTP_FORBIDDEN,
                        $message,
                        [
                            'popup_message' => $popupMessages,
                            'error_type' => $error
                        ]
                    );
                }

                return $this->jsonResponse(
                    $request,
                    Response::HTTP_OK,
                    'Your premium license key is valid.',
                    [
                        'data' => $data,
                        'license_type' => 'premium',
                        'popup_message' => $popupMessages
                    ]
                );

            } catch (ConnectException $e) {
                Log::warning('License server connection failed', [
                    'error' => $e->getMessage(),
                    'api_url' => $fluentInfo->api_url
                ]);

                return $this->jsonResponse(
                    $request,
                    Response::HTTP_SERVICE_UNAVAILABLE,
                    'License server is temporarily unavailable.',
                    ['popup_message' => $popupMessages]
                );

            } catch (Exception $e) {
                Log::error('App license check failed', [
                    'error' => $e->getMessage(),
                    'site_url' => $siteUrl,
                    'product' => $product
                ]);

                return $this->jsonResponse(
                    $request,
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    'Failed to validate license. Please try again later.',
                    ['popup_message' => $popupMessages]
                );
            }
        });
    }
}

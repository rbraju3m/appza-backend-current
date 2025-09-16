<?php

namespace App\Http\Controllers\Api\V2;

use App\Enums\LicenseResponseStatus;
use App\Models\BuildDomain;
use App\Models\FluentInfo;
use App\Models\FluentLicenseInfo;
use App\Models\FreeTrial;
use App\Models\Lead;
use App\Models\LicenseMessage;
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

    protected function jsonResponse(string $status, array $message, array $additionalData = []): JsonResponse
    {
        return response()->json(array_merge([
            'status' => $status,
            'message' => $message,
        ], $additionalData));
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



    public function check(Request $request)
    {
        if (!$this->authorization) {
            return $this->jsonResponse(Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'site_url' => 'required',
            'license_key' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse( Response::HTTP_BAD_REQUEST, 'Validation Error', ['errors' => $validator->errors()]);
        }

        $siteUrl = $this->normalizeUrl($request->get('site_url'));
        $key = $request->get('license_key');

        $fluentInfo = FluentInfo::where('product_slug', $this->pluginName)->where('is_active', true)->first();
        if (!$fluentInfo || !is_numeric($fluentInfo->item_id)) {
            return $this->jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Fluent plugin configuration error.');
        }

        $activationHash = FluentLicenseInfo::where('license_key', $key)->where('site_url', $siteUrl)->value('activation_hash');

        if (is_null($activationHash)) {
            return $this->jsonResponse(Response::HTTP_NOT_FOUND, 'License record not found for this site.');
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
                return $this->jsonResponse(Response::HTTP_NOT_FOUND, $message,['error_type' => $error]);
            }

            return $this->jsonResponse(Response::HTTP_OK, 'Your License key is valid.', ['data' => $data]);

        } catch (Exception $e) {
            Log::error('License check error', ['error' => $e->getMessage()]);
            return $this->jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Failed to connect to license server.');
        }
    }

    public function appLicenseCheck(Request $request)
    {
        // Caching popup messages is a good practice, keep it.
        /*$popupMessages = Cache::remember('active_popup_messages', 3600, function () {
            return PopupMessage::where('is_active', true)
                ->get(['message_type as type', 'message'])
                ->toArray();
        });*/
        $popupMessages = [];

        // Validate parameters
        $validator = Validator::make($request->all(), [
            'site_url' => 'required|url',
            'product' => 'required|string|in:appza,lazy_task,fcom_mobile',
        ]);


        if ($validator->fails()) {
            /*return $this->jsonResponse(
                LicenseResponseStatus::Invalid->value,
                [
                    'user' => ['message' => $validator->errors()->first(),'message_id' => 1],
                    'admin' => ['message' => 'admin message','message_id' => 2] ,
                    'special' => ['message' => 'special message','message_id' => 3]
                ],
                ['popup_message' => $popupMessages]
            );*/

            return $this->jsonResponse(
                LicenseResponseStatus::Invalid->value,
                [
                    'user' => ['message' => $validator->errors()->first(),'message_id' => 1],
                    'admin' => ['message' => 'admin message','message_id' => 2] ,
                    'special' => ['message' => 'special message','message_id' => 3]
                ],
                ['popup_message' => $popupMessages]
            );
        }

        $siteUrl = $this->normalizeUrl($request->get('site_url'));
        $product = $request->get('product');

//        dump($siteUrl, $product);

        // Fetch the license data in a single, efficient query
        $freeTrailLicenseData = FreeTrial::where('site_url', $siteUrl)
            ->where('product_slug', $product)
            ->where('is_active', true)
            ->first();
//        dump($freeTrailLicenseData);

        if (!$freeTrailLicenseData) {
            /*$getMessages = LicenseMessage::join('appza_fluent_informations','appza_fluent_informations.id','=','license_messages.product_id')
                ->join('license_logics','license_logics.id','=','license_messages.license_logic_id')
                ->where('appza_fluent_informations.product_slug', $product)
                ->where('license_logics.slug','license_not_found')
                ->where('license_messages.is_active', true)
                ->select([
                    'license_messages.id','license_messages.message_user','license_messages.message_admin','license_messages.message_special'
                ])
                ->first();*/

            $getMessages = LicenseMessage::with('message_details')
                ->whereHas('product', fn($q) => $q->where('product_slug', $product))
                ->whereHas('logic', fn($q) => $q->where('slug', 'license_not_found'))
                ->where('is_active', true)
                ->first();

            return $this->jsonResponse(
                LicenseResponseStatus::Invalid->value,
                [
                    'user'       => [
                        'id' => $getMessages?->message_details->firstWhere('type','user')?->id,
                        'message' => $getMessages?->message_details->firstWhere('type','user')?->message,
                    ],
                    'admin'       => [
                        'id' => $getMessages?->message_details->firstWhere('type','admin')?->id,
                        'message' => $getMessages?->message_details->firstWhere('type','admin')?->message,
                    ],
                    'special'       => [
                        'id' => $getMessages?->message_details->firstWhere('type','special')?->id,
                        'message' => $getMessages?->message_details->firstWhere('type','special')?->message,
                    ],
                ],
                ['popup_message' => $popupMessages]
            );

            dump($getMessages);

            /*return $this->jsonResponse(
                LicenseResponseStatus::Invalid->value,
                [
                    'message_id' => $getMessages->id,
                    'user' => $getMessages->message_user,
                    'admin' => $getMessages->message_admin,
                    'special' => $getMessages->message_special,
                ],
                ['popup_message' => $popupMessages]
            );*/
        }

        // Handle free trial license check
        if ($freeTrailLicenseData->is_fluent_license_check === 0) {
            $isValidFreeTrial = ($freeTrailLicenseData->grace_period_date >= now()->format('Y-m-d'));

            $data = [
              'status' => $freeTrailLicenseData->status,
              'site_url' => $freeTrailLicenseData->site_url,
              'name' => $freeTrailLicenseData->name,
              'email' => $freeTrailLicenseData->email,
              'product_id' => $freeTrailLicenseData->product_id,
              'variation_id' => $freeTrailLicenseData->variation_id,
              'license_key' => $freeTrailLicenseData->license_key,
              'product_title' => $freeTrailLicenseData->product_title,
              'variation_title' => $freeTrailLicenseData->variation_title,
              'activation_limit' => $freeTrailLicenseData->activation_limit,
              'activations_count' => $freeTrailLicenseData->activations_count,
              'expiration_date' => $freeTrailLicenseData->expiration_date,
//              'grace_period_date' => $freeTrailLicenseData->grace_period_date,
            ];
            if ($isValidFreeTrial) {
                return $this->jsonResponse(
                    Response::HTTP_OK,
                    'Your free trial license is valid.',
                    ['license_type' => 'free_trial', 'data' => $data, 'popup_message' => $popupMessages]
                );
            } else {
                $data['status'] = 'expired'; // Only for response, not database.
                return $this->jsonResponse(
                    Response::HTTP_OK,
                    'Your free trial has expired. Please purchase a paid license.',
                    ['license_type' => 'free_trial', 'data' => $data, 'popup_message' => $popupMessages]
                );
            }
        }

        // Handle premium/fluent license check
        /*if ($freeTrailLicenseData->is_fluent_license_check === 1) {
            return DB::transaction(function () use ($request, $siteUrl, $product, $popupMessages) {
                $buildDomain = BuildDomain::where('site_url', $siteUrl)
                    ->where('is_app_license_check', true)
                    ->where('plugin_name', $product)
                    ->lockForUpdate()
                    ->first();

                // Handle the case where the free trial exists but the premium license domain is not configured.
                if (!$buildDomain) {
                    return $this->jsonResponse(
                        Response::HTTP_FORBIDDEN,
                        'Premium license not configured for this domain. Please activate the paid license in your plugin settings.',
                        ['license_type' => 'invalid', 'data' => [], 'popup_message' => $popupMessages]
                    );
                }

                // A single, cleaner query for fluent info.
                $fluentInfo = FluentInfo::where('product_slug', $buildDomain->plugin_name)
                    ->where('is_active', true)
                    ->first();

                if (!$fluentInfo || !is_numeric($fluentInfo->item_id) || !filter_var($fluentInfo->api_url, FILTER_VALIDATE_URL)) {
                    return $this->jsonResponse(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        'Invalid Fluent plugin configuration. Please contact support.',
                        ['license_type' => 'invalid', 'data' => [],'popup_message' => $popupMessages]
                    );
                }

                $activationHash = FluentLicenseInfo::where('license_key', $buildDomain->license_key)
                    ->where('site_url', $siteUrl)
                    ->value('activation_hash');

                if (!$activationHash) {
                    return $this->jsonResponse(
                        Response::HTTP_FORBIDDEN,
                        'License data not found. Please activate your license first.',
                        ['license_type' => 'invalid', 'data' => [],'popup_message' => $popupMessages]
                    );
                }

                try {
                    $response = Http::timeout(15)
                        ->retry(2, 100)
                        ->get($fluentInfo->api_url, [
                            'fluent-cart' => 'check_license',
                            'license_key' => $buildDomain->license_key,
                            'activation_hash' => $activationHash,
                            'item_id' => (int) $fluentInfo->item_id,
                            'site_url' => $siteUrl,
                        ]);

                    if (!$response->successful()) {
                        Log::warning('Fluent API call failed', ['status' => $response->status(), 'response' => $response->body()]);

                        return $this->jsonResponse(
                            Response::HTTP_FORBIDDEN,
                            "API responded with an error.",
                            ['license_type' => 'invalid', 'data' => [],'popup_message' => $popupMessages, 'error_type' => $response->status()]
                        );
                    }

                    $data = $response->json();

                    if (!isset($data['success']) || $data['success'] !== true) {
                        $error = $data['error_type'] ?? $data['error'] ?? 'unknown_error';
                        $message = $this->getFluentErrorMessage($error, $data['message'] ?? 'License is invalid.');

                        return $this->jsonResponse(
                            Response::HTTP_FORBIDDEN,
                            $message,
                            ['license_type' => 'invalid', 'data' => [],'popup_message' => $popupMessages,'error_type' => $error]
                        );
                    }

                    $message = "Your premium license key is valid.";
                    if ($data['status'] == 'expired'){
                        $message = "Your Premium License is expire , please renew again.";
                    }

                    return $this->jsonResponse(
                        Response::HTTP_OK,
                        $message,
                        ['license_type' => 'premium', 'data' => $data, 'popup_message' => $popupMessages]
                    );

                } catch (ConnectException $e) {
                    Log::warning('License server connection failed', ['error' => $e->getMessage(), 'api_url' => $fluentInfo->api_url]);
                    return $this->jsonResponse(
                        Response::HTTP_SERVICE_UNAVAILABLE,
                        'License server is temporarily unavailable. Please try again later.',
                        ['license_type' => 'invalid', 'data' => [],'popup_message' => $popupMessages]
                    );
                } catch (Exception $e) {
                    Log::error('App license check failed', ['error' => $e->getMessage(), 'site_url' => $siteUrl, 'product' => $product]);
                    return $this->jsonResponse(
                        Response::HTTP_INTERNAL_SERVER_ERROR,
                        'An unexpected error occurred while validating the license.',
                        ['license_type' => 'invalid', 'data' => [],'popup_message' => $popupMessages]
                    );
                }
            });
        }*/
    }

}

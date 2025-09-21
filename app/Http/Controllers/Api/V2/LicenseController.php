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
use App\Services\LicenseService;
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
        $response = [
            'status' => $status,
        ];

        // Merge optional keys in desired order
        if (isset($additionalData['sub_status'])) {
            $response['sub_status'] = $additionalData['sub_status'];
            unset($additionalData['sub_status']); // remove to avoid duplication
        }

        $response['message'] = $message;

        // Merge remaining additional data (like popup_message)
        $response = array_merge($response, $additionalData);

        return response()->json($response);
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



    public function webLicenseCheck(Request $request, LicenseService $licenseService)
    {
        // Fetch active popup messages from cache. This should not be cleared later.
        $popupMessages = Cache::remember('active_popup_messages', 3600, function () {
            return PopupMessage::where('is_active', true)->get(['message_type as type', 'message'])->toArray();
        });
        $popupMessages = [];

        if (!$this->authorization) {
            $resp = $licenseService->formatInvalidResponse('unauthorized', 'Unauthorized', null);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $validator = Validator::make($request->all(), [
            'site_url' => 'required',
            'license_key' => 'required',
        ]);

        if ($validator->fails()) {
            $resp = $licenseService->formatInvalidResponse('validation_error', $validator->errors()->first(), null);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $siteUrl = $this->normalizeUrl($request->get('site_url'));
        $key = $request->get('license_key');

        $fluentInfo = FluentInfo::where('product_slug', $this->pluginName)->where('is_active', true)->first();
        if (!$fluentInfo || !is_numeric($fluentInfo->item_id)) {
            log::warning('Web:: Fluent api call internal not found for site_url: '.$siteUrl.' product: '.$this->pluginName);
            $resp = $licenseService->formatInvalidResponse('plugin_not_installed','',$this->pluginName);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages,'sub_status' => $resp['sub_status']]);

        }

        $buildDomain = BuildDomain::where('site_url', $siteUrl)
            ->where('is_app_license_check', true)
            ->where('plugin_name', $this->pluginName)
            ->lockForUpdate()
            ->first();

        if (!$buildDomain) {
            log::warning('Mobile:: Build domain not found for site_url: '.$siteUrl.' product: '.$this->pluginName);
            $resp = $licenseService->formatInvalidResponse('plugin_not_installed','',$this->pluginName);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages,'sub_status' => $resp['sub_status']]);
        }

        return $this->callExternalAPI($siteUrl, $this->pluginName,$key,$popupMessages, $fluentInfo->item_id, $fluentInfo->api_url, 'Web');
    }

    /**
     * Handles the mobile application license check.
     */
    public function appLicenseCheck(Request $request, LicenseService $licenseService)
    {
        // Fetch active popup messages from cache. This should not be cleared later.
        $popupMessages = Cache::remember('active_popup_messages', 3600, function () {
            return PopupMessage::where('is_active', true)->get(['message_type as type', 'message'])->toArray();
        });
        $popupMessages = [];

        // Validate input
        $validator = Validator::make($request->all(), [
            'site_url' => 'required|url',
            'product' => 'required|string|in:appza,lazy_task,fcom_mobile',
        ]);

        if ($validator->fails()) {
            $resp = $licenseService->formatInvalidResponse('validation_error', $validator->errors()->first(), null);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $siteUrl = $this->normalizeUrl($request->get('site_url'));
        $productSlug = $request->get('product');

        // --- Free-trial (local DB) ---
        $localLicenseData = FreeTrial::where('site_url', $siteUrl)
            ->where('product_slug', $productSlug)
            ->where('is_active', true)
            ->first();

        if ($localLicenseData) {
            // Check if the free trial uses local or external logic
            if ($localLicenseData->is_fluent_license_check === 0) {
                $resp = $licenseService->evaluate($localLicenseData,'free_trial');
                $statusCode = $resp['status'] === 'expire' ? LicenseResponseStatus::Expired->value : LicenseResponseStatus::Active->value;
                return $this->jsonResponse($statusCode, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
            }

            if ($localLicenseData->is_fluent_license_check === 1) {
                // --- Premium (external API) ---

                $buildDomain = BuildDomain::where('site_url', $localLicenseData->site_url)
                    ->where('is_app_license_check', true)
                    ->where('plugin_name', $localLicenseData->product_slug)
                    ->lockForUpdate()
                    ->first();

                if (!$buildDomain) {
                    log::warning('Mobile:: Build domain not found for site_url: '.$siteUrl.' product: '.$productSlug);
                    $resp = $licenseService->formatInvalidResponse('plugin_not_installed','',$productSlug);
                    return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages,'sub_status' => $resp['sub_status']]);
                }

                $findLicense = FluentLicenseInfo::where('build_domain_id', $buildDomain->id)->select(['license_key'])->first();

                if (!$findLicense) {
                    log::warning('Mobile:: License not found for site_url: '.$siteUrl.' product: '.$productSlug);
                    $resp = $licenseService->formatInvalidResponse('plugin_not_installed','',$productSlug);
                    return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages,'sub_status' => $resp['sub_status']]);
                }

                $fluentInfo = FluentInfo::where('product_slug', $productSlug)->where('is_active', true)->first();

                if (!$fluentInfo || !is_numeric($fluentInfo->item_id)) {
                    log::warning('Mobile:: Fluent api call internal not found for site_url: '.$siteUrl.' product: '.$productSlug);
                    $resp = $licenseService->formatInvalidResponse('plugin_not_installed','',$productSlug);
                    return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages,'sub_status' => $resp['sub_status']]);
                }

                return $this->callExternalAPI($localLicenseData->site_url, $localLicenseData->product_slug,$findLicense->license_key, $popupMessages, $fluentInfo->item_id,$fluentInfo->api_url, 'Mobile');

            }
        }else{
            $resp = $licenseService->formatInvalidResponse('license_not_found','',$productSlug);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages,'sub_status' => $resp['sub_status']]);
        }
    }

    private function callExternalAPI(string $siteUrl, string $productSlug, string $licenseKey,array $popupMessages, int $itemId, string $apiUrl, string $device='Mobile')
    {
        $activationHash = FluentLicenseInfo::where('license_key', $licenseKey)
            ->where('site_url', $siteUrl)
            ->value('activation_hash');

        if (!$activationHash) {
            log::warning($device.':: Fluent api call internal not found for site_url: '.$siteUrl.' product: '.$productSlug);
            $resp = LicenseService::formatInvalidResponse('plugin_not_installed','',$productSlug);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages,'sub_status' => $resp['sub_status']]);
        }

        $apiParams = [
            'fluent-cart' => 'check_license',
            'license_key' => $licenseKey,
            'activation_hash' => $activationHash,
            'item_id' => $itemId,
            'site_url' => $siteUrl,
        ];

        try {
            $response = Http::timeout(15)
                ->retry(2, 100)
                ->get($apiUrl, $apiParams);

            if (!$response->ok()) {
                log::warning($device.':: Fluent api response error for site_url: '.$siteUrl.' product: '.$productSlug);
                $resp = LicenseService::formatInvalidResponse('plugin_not_installed','',$productSlug);
                return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages,'sub_status' => $resp['sub_status']]);
            }

            $data = $response->json();

            if ($data['status'] === 'valid') {
                $licenseObj = (object) [
                    'product_slug' => $productSlug,
                    'expiration_date' => $data['expiration_date'],
                    'grace_period_date' => \Carbon\Carbon::parse($data['expiration_date'])->addDays(15)->format('Y-m-d H:i:s'),
                ];

                $resp = app(LicenseService::class)->evaluate($licenseObj,'premium');

                $statusCode = $resp['status'] === 'expire' ? LicenseResponseStatus::Expired->value : LicenseResponseStatus::Active->value;
                return $this->jsonResponse($statusCode, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
            } else {
                log::warning($device.':: Fluent api response not valid for site_url: '.$siteUrl.' product: '.$productSlug);
                $resp = app(LicenseService::class)->formatInvalidResponse('plugin_not_installed','',$productSlug);
                return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages,'sub_status' => $resp['sub_status']]);
            }
        } catch (\Exception $e) {
            log::warning($device.':: '.$e->getMessage().' for site_url: '.$siteUrl.' product: '.$productSlug);
            $resp = app(LicenseService::class)->formatInvalidResponse('plugin_not_installed','',$productSlug);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages,'sub_status' => $resp['sub_status']]);
        }
    }
}

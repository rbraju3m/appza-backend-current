<?php


namespace App\Http\Controllers\Api\V2;

use App\Enums\LicenseResponseStatus;
use App\Http\Controllers\Controller;
use App\Models\BuildDomain;
use App\Models\FluentInfo;
use App\Models\FluentLicenseInfo;
use App\Models\FreeTrial;
use App\Models\PopupMessage;
use App\Services\ExternalLicenseProvider;
use App\Services\LicenseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LicenseController extends Controller
{
    protected $authorization;
    protected $domain;
    protected $pluginName;
    protected $email;

    public function __construct(Request $request)
    {
        $data = \App\Models\Lead::checkAuthorization($request);
        $this->authorization = ($data && $data['auth_type']) ? $data['auth_type'] : false;
        $this->domain = $data['domain'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->pluginName = $data['plugin_name'] ?? null;
    }

    protected function licenseCheckJsonResponse(string $status, array $message, array $additionalData = []): \Illuminate\Http\JsonResponse
    {
        $response = ['status' => $status];

        if (isset($additionalData['sub_status'])) {
            $response['sub_status'] = $additionalData['sub_status'];
            unset($additionalData['sub_status']);
        }

        $response['message'] = $message;

        return response()->json(array_merge($response, $additionalData));
    }

    protected function JsonResponse(string $status, string $message, array $additionalData = []): \Illuminate\Http\JsonResponse
    {
        $response = ['status' => $status];

        if (isset($additionalData['sub_status'])) {
            $response['sub_status'] = $additionalData['sub_status'];
            unset($additionalData['sub_status']);
        }

        $response['message'] = $message;

        if (isset($additionalData['data'])) {
            $response['data'] = $additionalData['data'];
            unset($additionalData['data']);
        }

        return response()->json(array_merge($response, $additionalData));
    }


    protected function normalizeUrl(string $url): ?string
    {
        if (!$url) return null;
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }
        $parts = parse_url($url);
        $scheme = $parts['scheme'] ?? 'https';
        $host = strtolower($parts['host'] ?? '');
        if (!$host) return null;
        return rtrim("{$scheme}://{$host}", '/');
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

    public function activate(Request $request)
    {
        if (!$this->authorization) {
            return $this->jsonResponse(
                LicenseResponseStatus::Suspended->value,
                'This site url is not authorized for the license. Please contact support.',
                ['sub_status' => 'unauthorized']
            );
        }

        $validator = Validator::make($request->all(), [
            'site_url' => 'required|string',
            'license_key' => 'required|string',
            'email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse(
                LicenseResponseStatus::Suspended->value,
                $validator->errors()->first(),
                ['sub_status' => 'validation_error']
            );
        }

        $data = $request->only('site_url', 'license_key', 'email');
        $normalizedSiteUrl = $this->normalizeUrl($data['site_url']);

        $localLicenseData = FreeTrial::where('site_url', $normalizedSiteUrl)
            ->where('product_slug', $this->pluginName)
            ->where('is_active', true)
            ->first();

        if (!$localLicenseData) {
            return $this->jsonResponse(
                LicenseResponseStatus::Suspended->value,
                'This site url is not registered for this license activation. Please contact support.',
                ['sub_status' => 'free_trial_not_found']
            );
        }

        $fluentInfo = FluentInfo::where('product_slug', $this->pluginName)
            ->where('is_active', true)
            ->first();

        if (!$fluentInfo || !is_numeric($fluentInfo->item_id) || !$fluentInfo->api_url) {
            return $this->jsonResponse(
                LicenseResponseStatus::Suspended->value,
                'This site url is not registered due to missing product configuration. Please contact support.',
                ['sub_status' => 'product_configuration_not_found']
            );
        }

        $apiInput = [
            'site_url'   => $normalizedSiteUrl,
            'license_key'=> $data['license_key'],
            'item_id'    => $fluentInfo->item_id,
            'fluent-cart'=> 'activate_license',
        ];

        try {
            $res = Http::retry(2, 500)->timeout(10)->get($fluentInfo->api_url, $apiInput)->json();

            if (!is_array($res) || !($res['success'] ?? false) || ($res['status'] ?? 'invalid') !== 'valid') {
                $error = $res['error_type'] ?? $res['error'] ?? null;
                $errorMessage = $this->getFluentErrorMessage($error, $res['message'] ?? 'License activation failed.');
                return $this->jsonResponse(LicenseResponseStatus::Suspended->value, $errorMessage, ['sub_status' => $error]);
            }
        } catch (Exception $e) {
            Log::error('API call failed', ['error' => $e->getMessage(), 'params' => $apiInput]);
            return $this->jsonResponse(
                LicenseResponseStatus::Suspended->value,
                "This site url is not registered due to configuration. Please contact support.",
                ['sub_status' => "fail_to_connect_fluent_license_server"]
            );
        }

        try {
            DB::beginTransaction();

            $buildDomain = BuildDomain::updateOrCreate(
                ['site_url' => $normalizedSiteUrl, 'license_key' => $data['license_key'], 'plugin_name'=> $this->pluginName],
                [
                    'package_name' => 'com.' . $this->getSubdomainAndDomain($normalizedSiteUrl) . '.live',
                    'email' => $data['email'] ?? $this->email,
                    'fluent_item_id' => $fluentInfo->item_id,
                    'is_app_license_check' => 1,
                    'is_deactivated' => 0,
                ]
            );

            BuildDomain::where('site_url', $normalizedSiteUrl)
                ->where('plugin_name', $this->pluginName)
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

            $localLicenseData->update(['is_fluent_license_check' => true]);

            DB::commit();

            Log::info("License activated successfully", [
                'site_url' => $normalizedSiteUrl,
                'license_key' => $data['license_key'],
                'product' => $this->pluginName,
            ]);

            return $this->jsonResponse(
                LicenseResponseStatus::Activate->value,
                "Your License key has been activated successfully.",
//                ['sub_status' => "activate_license", 'data' => $res]
                ['sub_status' => "activate_license"]
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('DB transaction failed', ['error' => $e->getMessage()]);
            return $this->jsonResponse(
                LicenseResponseStatus::Suspended->value,
                "This site url is not registered due to local license issue. Please contact support.",
                ['sub_status' => "fail_to_connect_local_license_server"]
            );
        }
    }

    public function webLicenseCheck(Request $request, LicenseService $licenseService, ExternalLicenseProvider $external)
    {
        $popupMessages = Cache::remember('active_popup_messages', 3600, function () {
            return PopupMessage::where('is_active', true)->get(['message_type as type', 'message'])->toArray();
        });

        $validator = Validator::make($request->all(), [
            'site_url' => 'required|string',
            'license_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            $resp = $licenseService->formatInvalidResponse('validation_error', $validator->errors()->first());
            return $this->licenseCheckJsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $siteUrl = $this->normalizeUrl($request->get('site_url'));
        $licenseKey = $request->get('license_key');

        $productSlug = $this->pluginName;
        if (!$productSlug) {
            Log::warning("WebLicenseCheck: authorization fail for site_url {$siteUrl} license_key {$licenseKey}");
            $resp = $licenseService->formatInvalidResponse('unauthorized', 'Unauthorized');
            return $this->licenseCheckJsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $freeTrial = FreeTrial::where('site_url', $siteUrl)
            ->where('product_slug', $productSlug)
            ->where('is_active', true)
            ->first();

        if (!$freeTrial) {
            Log::warning("WebLicenseCheck: plugin not install for site_url {$siteUrl} license_key {$licenseKey}");
            $resp = $licenseService->formatInvalidResponse('license_not_found', null, $productSlug);
            return $this->licenseCheckJsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        if ($freeTrial->is_fluent_license_check === 0) {
            $licenseData = [
                'product_slug' => $freeTrial->product_slug,
                'expiration_date' => $freeTrial->expiration_date,
                'grace_period_date' => $freeTrial->grace_period_date,
                'license_type' => "free_trial"
            ];

            $resp = $licenseService->evaluate($licenseData);
            $statusCode = $resp['status'] === 'expired' ? LicenseResponseStatus::Expired->value : LicenseResponseStatus::Active->value;
            return $this->licenseCheckJsonResponse($statusCode, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $fluentInfo = FluentInfo::where('product_slug', $productSlug)->where('is_active', true)->first();
        if (!$fluentInfo || !is_numeric($fluentInfo->item_id)) {
            Log::warning("WebLicenseCheck: Fluent info missing for product {$productSlug}");
            $resp = $licenseService->formatInvalidResponse('plugin_not_installed', null, $productSlug);
            return $this->licenseCheckJsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $fluentLicense = FluentLicenseInfo::where('license_key', $licenseKey)
            ->where('site_url', $siteUrl)
            ->first();

        if (!$fluentLicense) {
            Log::warning("WebLicenseCheck: FluentLicenseInfo not found for key {$licenseKey} and site {$siteUrl}");
            $resp = $licenseService->formatInvalidResponse('license_not_found', null, $productSlug);
            return $this->licenseCheckJsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        try {
            $externalDto = $external->fetchLicenseByKey(
                $licenseKey,
                $fluentLicense->activation_hash,
                (int)$fluentInfo->item_id,
                $fluentInfo->api_url,
                $siteUrl,
                $productSlug
            );

            if (!$externalDto) {
                Log::warning("WebLicenseCheck: External api response not found for key {$licenseKey} and site {$siteUrl}");
                $resp = $licenseService->formatInvalidResponse('license_not_found', null, $productSlug);
                return $this->licenseCheckJsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
            }

            $externalDto['license_type'] = "premium";
            $resp = $licenseService->evaluate($externalDto);
            $statusCode = $resp['status'] === 'expired' ? LicenseResponseStatus::Expired->value : LicenseResponseStatus::Active->value;

            return $this->licenseCheckJsonResponse($statusCode, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status'], 'meta' => $resp['meta'] ?? []]);

        } catch (\Exception $e) {
            Log::error("WebLicenseCheck external error: " . $e->getMessage(), ['site' => $siteUrl, 'product' => $productSlug]);
            $resp = $licenseService->formatInvalidResponse('external_api_error', $e->getMessage(), $productSlug);
            return $this->licenseCheckJsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }
    }

    /**
     * Mobile (app) check (params: site_url + product)
     */
    public function appLicenseCheck(Request $request, LicenseService $licenseService, ExternalLicenseProvider $external)
    {
        $popupMessages = Cache::remember('active_popup_messages', 3600, function () {
            return PopupMessage::where('is_active', true)->get(['message_type as type', 'message'])->toArray();
        });
        $popupMessages = [];

        $validator = Validator::make($request->all(), [
            'site_url' => 'required|string',
            'product'  => 'required|string',
        ]);

        if ($validator->fails()) {
            $resp = $licenseService->formatInvalidResponse('validation_error', $validator->errors()->first());
            return $this->licenseCheckJsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $siteUrl = $this->normalizeUrl($request->get('site_url'));
        $productSlug = $request->get('product');

        $freeTrial = FreeTrial::where('site_url', $siteUrl)
            ->where('product_slug', $productSlug)
            ->where('is_active', true)
            ->first();

        if (! $freeTrial) {
            Log::warning("AppLicenseCheck: plugin not install for site_url {$siteUrl} product {$productSlug}");
            $resp = $licenseService->formatInvalidResponse('license_not_found', null, $productSlug);
            return $this->licenseCheckJsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        if ($freeTrial->is_fluent_license_check === 0) {
            // local free trial
            $licenseData = [
                'product_slug' => $freeTrial->product_slug,
                'expiration_date' => $freeTrial->expiration_date,
                'grace_period_date' => $freeTrial->grace_period_date,
                'license_type' => "free_trial",
            ];
            $resp = $licenseService->evaluate($licenseData);
            $statusCode = $resp['status'] === 'expired' ? LicenseResponseStatus::Expired->value : LicenseResponseStatus::Active->value;
            return $this->licenseCheckJsonResponse($statusCode, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status'],'meta'=>$resp['meta'] ?? []]);
        }

        // premium for mobile: find build domain & license key stored server-side
        $buildDomain = BuildDomain::where('site_url', $siteUrl)
            ->where('is_app_license_check', true)
            ->where('plugin_name', $productSlug)
            ->lockForUpdate()
            ->first();

        if (! $buildDomain) {
            Log::warning("AppLicenseCheck: BuildDomain not found and license not active for {$siteUrl} / {$productSlug}");
            $resp = $licenseService->formatInvalidResponse('plugin_not_installed', null, $productSlug);
            return $this->licenseCheckJsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $fluentLicense = FluentLicenseInfo::where('build_domain_id', $buildDomain->id)->select(['license_key','activation_hash'])->first();
        if (! $fluentLicense) {
            Log::warning("AppLicenseCheck: FluentLicenseInfo missing for build_domain_id and license not active {$buildDomain->id}");
            $resp = $licenseService->formatInvalidResponse('license_not_found', null, $productSlug);
            return $this->licenseCheckJsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $fluentInfo = FluentInfo::where('product_slug', $productSlug)->where('is_active', true)->first();
        if (! $fluentInfo || ! is_numeric($fluentInfo->item_id)) {
            Log::warning("AppLicenseCheck: FluentInfo missing for product and configuration missing {$productSlug}");
            $resp = $licenseService->formatInvalidResponse('plugin_not_installed', null, $productSlug);
            return $this->licenseCheckJsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        // call external provider
        try {
            $externalDto = $external->fetchLicenseByKey(
                $fluentLicense->license_key,
                $fluentLicense->activation_hash,
                (int) $fluentInfo->item_id,
                $fluentInfo->api_url,
                $siteUrl,
                $productSlug
            );

            if (! $externalDto) {
                Log::warning("AppLicenseCheck: External api response not found for product {$productSlug} and site {$siteUrl}");
                $resp = $licenseService->formatInvalidResponse('license_not_found', null, $productSlug);
                return $this->licenseCheckJsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
            }

            $externalDto['license_type'] = "premium";
            $resp = $licenseService->evaluate($externalDto);
            $statusCode = $resp['status'] === 'expired' ? LicenseResponseStatus::Expired->value : LicenseResponseStatus::Active->value;
            return $this->licenseCheckJsonResponse($statusCode, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status'], 'meta' => $resp['meta'] ?? []]);

        } catch (\Exception $e) {
            Log::error("AppLicenseCheck:: external error: " . $e->getMessage(), ['site' => $siteUrl, 'product' => $productSlug]);
            $resp = $licenseService->formatInvalidResponse('external_api_error', $e->getMessage(), $productSlug);
            return $this->licenseCheckJsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }
    }
}


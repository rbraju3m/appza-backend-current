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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

    protected function jsonResponse(string $status, array $message, array $additionalData = []): \Illuminate\Http\JsonResponse
    {
        $response = ['status' => $status];

        if (isset($additionalData['sub_status'])) {
            $response['sub_status'] = $additionalData['sub_status'];
            unset($additionalData['sub_status']);
        }

        $response['message'] = $message;

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
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $siteUrl = $this->normalizeUrl($request->get('site_url'));
        $licenseKey = $request->get('license_key');

        $productSlug = $this->pluginName;
        if (!$productSlug) {
            Log::warning("WebLicenseCheck: authorization fail for site_url {$siteUrl} license_key {$licenseKey}");
            $resp = $licenseService->formatInvalidResponse('unauthorized', 'Unauthorized');
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $freeTrial = FreeTrial::where('site_url', $siteUrl)
            ->where('product_slug', $productSlug)
            ->where('is_active', true)
            ->first();

        if (!$freeTrial) {
            Log::warning("WebLicenseCheck: plugin not install for site_url {$siteUrl} license_key {$licenseKey}");
            $resp = $licenseService->formatInvalidResponse('license_not_found', null, $productSlug);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
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
            return $this->jsonResponse($statusCode, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $fluentInfo = FluentInfo::where('product_slug', $productSlug)->where('is_active', true)->first();
        if (!$fluentInfo || !is_numeric($fluentInfo->item_id)) {
            Log::warning("WebLicenseCheck: Fluent info missing for product {$productSlug}");
            $resp = $licenseService->formatInvalidResponse('plugin_not_installed', null, $productSlug);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $fluentLicense = FluentLicenseInfo::where('license_key', $licenseKey)
            ->where('site_url', $siteUrl)
            ->first();

        if (!$fluentLicense) {
            Log::warning("WebLicenseCheck: FluentLicenseInfo not found for key {$licenseKey} and site {$siteUrl}");
            $resp = $licenseService->formatInvalidResponse('license_not_found', null, $productSlug);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
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
                return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
            }

            $externalDto['license_type'] = "premium";
            $resp = $licenseService->evaluate($externalDto);
            $statusCode = $resp['status'] === 'expired' ? LicenseResponseStatus::Expired->value : LicenseResponseStatus::Active->value;

            return $this->jsonResponse($statusCode, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status'], 'meta' => $resp['meta'] ?? []]);

        } catch (\Exception $e) {
            Log::error("WebLicenseCheck external error: " . $e->getMessage(), ['site' => $siteUrl, 'product' => $productSlug]);
            $resp = $licenseService->formatInvalidResponse('external_api_error', $e->getMessage(), $productSlug);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
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

        $validator = Validator::make($request->all(), [
            'site_url' => 'required|string',
            'product'  => 'required|string',
        ]);

        if ($validator->fails()) {
            $resp = $licenseService->formatInvalidResponse('validation_error', $validator->errors()->first());
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
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
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
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
            return $this->jsonResponse($statusCode, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
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
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $fluentLicense = FluentLicenseInfo::where('build_domain_id', $buildDomain->id)->select(['license_key','activation_hash'])->first();
        if (! $fluentLicense) {
            Log::warning("AppLicenseCheck: FluentLicenseInfo missing for build_domain_id and license not active {$buildDomain->id}");
            $resp = $licenseService->formatInvalidResponse('license_not_found', null, $productSlug);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }

        $fluentInfo = FluentInfo::where('product_slug', $productSlug)->where('is_active', true)->first();
        if (! $fluentInfo || ! is_numeric($fluentInfo->item_id)) {
            Log::warning("AppLicenseCheck: FluentInfo missing for product and configuration missing {$productSlug}");
            $resp = $licenseService->formatInvalidResponse('plugin_not_installed', null, $productSlug);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
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
                return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
            }

            $externalDto['license_type'] = "premium";
            $resp = $licenseService->evaluate($externalDto);
            $statusCode = $resp['status'] === 'expired' ? LicenseResponseStatus::Expired->value : LicenseResponseStatus::Active->value;
            return $this->jsonResponse($statusCode, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status'], 'meta' => $resp['meta'] ?? []]);

        } catch (\Exception $e) {
            Log::error("AppLicenseCheck:: external error: " . $e->getMessage(), ['site' => $siteUrl, 'product' => $productSlug]);
            $resp = $licenseService->formatInvalidResponse('external_api_error', $e->getMessage(), $productSlug);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages, 'sub_status' => $resp['sub_status']]);
        }
    }
}


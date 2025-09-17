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

    public function appLicenseCheck1(Request $request, LicenseService $licenseService)
    {
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
            $resp = $licenseService->formatInvalidResponse('validation_error',$validator->errors()->first(),null);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages,'sub_status' => $resp['sub_status']]);
        }

        $siteUrl = $this->normalizeUrl($request->get('site_url'));
        $productSlug = $request->get('product');

        // --- Free-trial (local DB) ---
        $freeTrial = FreeTrial::where('site_url', $siteUrl)
            ->where('product_slug', $productSlug)
            ->where('is_active', true)
            ->first();

        if (!$freeTrial){
            $resp = $licenseService->formatInvalidResponse('license_not_found','',$productSlug);
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $popupMessages,'sub_status' => $resp['sub_status']]);
        }


        if ( $freeTrial->is_fluent_license_check === 0) {
            // Ensure your FreeTrial model has 'expiration_date' and 'grace_period_date' as strings/dates
            $resp = $licenseService->evaluate($freeTrial);
            $statusCode = $resp['status'] === 'expire' ? LicenseResponseStatus::Expired->value : LicenseResponseStatus::Active->value;
            return $this->jsonResponse($statusCode, $resp['message'], ['popup_message' => $popupMessages,'sub_status' => $resp['sub_status']]);
        }

        // --- Premium (external API) ---
        // Try external provider: create an adapter to fetch license info and normalize to same DTO shape
        try {
            $externalDto = app(\App\Services\ExternalLicenseProvider::class)->fetchLicense($siteUrl, $productSlug);
            if (! $externalDto) {
                $resp = $licenseService->evaluate(null, 'user', ['error_slug' => 'license_not_found']);
                $resp['popup_message'] = $popupMessages;
                return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $resp['popup_message']]);
            }
            // externalDto must have expiration_date and grace_period_date (compute grace_period_date = expiration + 15 inside provider)
            $resp = $licenseService->evaluate($externalDto, 'user');
            $resp['popup_message'] = $popupMessages;
            $statusCode = $resp['status'] === 'expire' ? LicenseResponseStatus::Expired->value : LicenseResponseStatus::Active->value;
            return $this->jsonResponse($statusCode, $resp['message'], ['popup_message' => $resp['popup_message'], 'meta'=>$resp['meta']]);
        } catch (\Exception $e) {
            // Treat external failures as invalid (or log and return specific 'plugin' slug)
            \Log::error('External license check failed', ['err'=>$e->getMessage()]);
            $resp = $licenseService->evaluate(null, 'user', ['error_slug' => 'external_api_error']);
            $resp['popup_message'] = $popupMessages;
            return $this->jsonResponse(LicenseResponseStatus::Invalid->value, $resp['message'], ['popup_message' => $resp['popup_message']]);
        }
    }

    public function appLicenseCheck(Request $request)
    {
        try {
            // 🔹 Cache popup messages (per product)
            $product = $request->input('product');
            $popupMessages = Cache::remember('active_popup_messages', 3600, function () {
                return PopupMessage::where('is_active', true)->get(['message_type as type', 'message'])->toArray();
            });
            $popupMessages = [];

            // 🔹 Validate request
            $validator = Validator::make($request->all(), [
                'product' => 'required|string',
                'site_url' => 'required|string',
            ]);

            if ($validator->fails()) {
                return LicenseService::formatInvalidResponse(
                    'validation_error',
                    $validator->errors()->first()
                );
            }

            // 🔹 Free Trial branch
            $license = FreeTrial::where('site_url', $request->site_url)
                ->where('product_slug', $request->product)
                ->where('is_active', 1)
                ->first();

            if (!$license) {
                return LicenseService::formatInvalidResponse('license_not_found');
            }

            $resp = LicenseService::evaluate($license, 'free_trial', $request->product);

            return response()->json([
                'status' => $resp['status'], // invalid | active | expired
                'sub_status' => $resp['sub_status'], // before_exp, in_grace, etc.
                'message' => $resp['message'],
                'popup_message' => $popupMessages,
            ]);
        } catch (\Exception $e) {
            return LicenseService::formatInvalidResponse('server_error', $e->getMessage());
        }
    }


}

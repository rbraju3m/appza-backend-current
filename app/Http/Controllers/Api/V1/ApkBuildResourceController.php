<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\ApkBuildRequest;
use App\Http\Requests\AppNameRequest;
use App\Http\Requests\IosBuildRequest;
use App\Models\AppVersion;
use App\Models\BuildDomain;
use App\Models\FluentInfo;
use App\Models\FluentLicenseInfo;
use App\Models\Lead;
use App\Services\IosBuildValidationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApkBuildResourceController extends Controller
{
    protected $authorization;
    protected $domain;
    protected $pluginName;
    protected $iosBuildValidationService;


    public function __construct(Request $request,IosBuildValidationService $iosBuildValidationService){
        $data = Lead::checkAuthorization($request);
        $this->authorization = ($data && $data['auth_type'])?$data['auth_type']:false;
        $this->domain = ($data && $data['domain'])?$data['domain']:'';
        $this->pluginName = ($data && $data['plugin_name'])?$data['plugin_name']:'';
        $this->iosBuildValidationService = $iosBuildValidationService;
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


    public function buildResource(ApkBuildRequest $request){

        $input = $request->validated();

        $jsonResponse = function ($statusCode, $message, $additionalData = []) use ($request) {
            return new JsonResponse(array_merge([
                'status' => $statusCode,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => $message,
            ], $additionalData), $statusCode);
        };

        if (!$this->authorization){
            return $jsonResponse(Response::HTTP_UNAUTHORIZED, 'Unauthorized.');
        }

        if ($this->pluginName == 'lazy_task'){
            return $jsonResponse(Response::HTTP_LOCKED, 'Build process off for lazy task.');
        }

        $siteUrl = $this->normalizeUrl($input["site_url"]);
        $findSiteUrl = BuildDomain::where('site_url',$siteUrl)->where('license_key',$input['license_key'])->first();

        if (!$findSiteUrl){
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Domain Not found.');
        }

        if (!$findSiteUrl->fluent_item_id){
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Item id not found.');
        }

        $activationHash = FluentLicenseInfo::where('license_key', $input['license_key'])->where('site_url', $siteUrl)->value('activation_hash');

        if (is_null($activationHash)) {
            return $this->jsonResponse($request, Response::HTTP_NOT_FOUND, 'License record not found for this site.');
        }

        $params = [
            'fluent-cart' => 'check_license',
            'license_key' => $input['license_key'],
            'activation_hash' => $activationHash,
            'item_id' => $findSiteUrl->fluent_item_id,
            'site_url' => $siteUrl,
        ];

        // Send API Request
        $getFluentInfo = FluentInfo::where('product_slug', $findSiteUrl->plugin_name)->where('is_active',true)->first();
        if (!$getFluentInfo) {
            return $jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The fluent information not set in the configuration.');
        }

        $fluentApiUrl = $getFluentInfo->api_url;

        /*try {
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
        }*/

        $response = Http::timeout(10)->get($fluentApiUrl, $params);
        $data = $response->json();

        if (!is_array($data) || !($data['success'] ?? false) || ($data['status'] ?? 'invalid') !== 'valid') {
            $error = $data['error_type'] ?? $data['error'] ?? null;
            $message = $this->getFluentErrorMessage($error, $data['message'] ?? 'License is not valid.');
            return $jsonResponse(Response::HTTP_NOT_FOUND, $message);
        }

        $targetLocationLogo = public_path().'/upload/build-apk/logo/';
        $targetLocationSplash = public_path().'/upload/build-apk/splash/';

        if ($input['app_logo']) {
            $url = $input['app_logo'];
            $fileHeaders = @get_headers($url);
            if (!$fileHeaders || $fileHeaders[0] == 'HTTP/1.1 404 Not Found') {
                return $jsonResponse(Response::HTTP_BAD_REQUEST, 'App logo invalid file URL.');
            }

            if(!File::exists($targetLocationLogo)) {
                File::makeDirectory($targetLocationLogo, 0777, true);
            }

            $fileName = bin2hex(random_bytes(5)).'_'.basename($url);
            $fileContent = @file_get_contents($url);

            // Check if the file was able to be opened
            if ($fileContent === FALSE) {
                return $jsonResponse(Response::HTTP_NOT_FOUND, 'App logo could not open file at URL.');
            }

            file_put_contents($targetLocationLogo . $fileName, $fileContent);
            $appLogo = $fileName;
        }

        if ($input['app_splash_screen_image']) {
            // Check if the URL points to a valid file
            $url = $input['app_splash_screen_image'];
            $fileHeaders = @get_headers($url);
            if (!$fileHeaders || $fileHeaders[0] == 'HTTP/1.1 404 Not Found') {
                return $jsonResponse(Response::HTTP_BAD_REQUEST, 'App splash screen image invalid file URL.');
            }

            if(!File::exists($targetLocationSplash)) {
                File::makeDirectory($targetLocationSplash, 0777, true);
            }

            $fileName = bin2hex(random_bytes(5)).'_'.basename($url);
            $fileContent = @file_get_contents($url);

            // Check if the file was able to be opened
            if ($fileContent === FALSE) {
                return $jsonResponse(Response::HTTP_NOT_FOUND, 'App splash screen image could not open file at URL.');
            }

            file_put_contents($targetLocationSplash . $fileName, $fileContent);
            $splash_screen_image = $fileName;
        }

        $findAppVersion = AppVersion::where('is_active', 1)->latest()->first();

        // First, extract the platform array from the request
        $platforms = $request->input('platform', []);

        // Set the boolean values based on whether the array contains these values
        $isAndroid = in_array('android', $platforms);
        $isIos = in_array('ios', $platforms);

        $findSiteUrl->update([
            'plugin_name' => $this->pluginName,
            'version_id' => $findAppVersion->id,
            'build_domain_id' => $findSiteUrl->id,
            'fluent_id' => $findSiteUrl->fluent_item_id,
            'app_name' => $request->input('app_name'),
            'app_logo' => $appLogo,
            'app_splash_screen_image' => $splash_screen_image,
            'is_android' => $isAndroid,
            'is_ios' => $isIos,
            'confirm_email' => $request->input('email'),
            'build_plugin_slug' => $request->input('plugin_slug'),
        ]);

        // for response
        $status = Response::HTTP_OK;
        $payload = [
            'status' => $status,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'message' => 'App selection for build requests is confirmed.',
            'data' => [
                'package_name' => $findSiteUrl->package_name,
                'bundle_name' => $findSiteUrl->package_name,
            ]
        ];
        // Log the response
//        Log::info("=============================================================================================================");
//        Log::info('Build resource response:', ['status' => $status, 'response' => $payload,'payload' => $request->validated()]);
        // Return it
        return response()->json($payload, $status);
    }

    public function iosResourceAndVerify(IosBuildRequest $request)
    {
        $input = $request->validated();
        $jsonResponse = fn($status, $message, $data = []) => response()->json(array_merge([
            'status' => $status,
            'url' => $request->fullUrl(),
            'method' => $request->getMethod(),
            'message' => $message,
        ], $data), $status);

        $siteUrl = $this->normalizeUrl($input['site_url']);

        $findSiteUrl = BuildDomain::where('site_url', $siteUrl)
            ->where('license_key', $input['license_key'])->first();

        if (!$findSiteUrl) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Domain or license key is incorrect');
        }

        $p8Dir = public_path('/upload/build-apk/p8file/');
        File::ensureDirectoryExists($p8Dir, 0777, true);

        $p8FileName = 'key_' . uniqid() . '.p8';
        File::put($p8Dir . $p8FileName, $input['ios_p8_file_content']);

        $findSiteUrl->update([
            'ios_issuer_id' => $input['ios_issuer_id'],
            'ios_key_id' => $input['ios_key_id'],
            'team_id' => $input['ios_team_id'],
            'ios_p8_file_content' => $p8FileName,
        ]);

        $service = app(IosBuildValidationService::class);
        $result = $service->iosBuildProcessValidation($findSiteUrl);

        if ($result['success'] === false) {
            Log::warning('IOS validation failed', $result);
            return $jsonResponse($result['status'], $result['message']);
        }

        return $jsonResponse($result['status'], $result['message'], [
            'data' => [
                'package_name' => $findSiteUrl->package_name,
                'bundle_name' => $result['data'] ?? $findSiteUrl->package_name
            ]
        ]);
    }

    public function iosCheckAppName(AppNameRequest $request)
    {
        $input = $request->validated();
        $jsonResponse = fn($status, $message, $data = []) => response()->json(array_merge([
            'status' => $status,
            'url' => $request->fullUrl(),
            'method' => $request->getMethod(),
            'message' => $message,
        ], $data), $status);

        $siteUrl = $this->normalizeUrl($input['site_url']);

        $findSiteUrl = BuildDomain::where('site_url', $siteUrl)
            ->where('license_key', $input['license_key'])->first();

        if (!$findSiteUrl) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Domain or license key is incorrect');
        }

        $service = app(IosBuildValidationService::class);
        $result = $service->iosBuildProcessValidation2($findSiteUrl);

        if ($result['success'] === false) {
            return $jsonResponse($result['status'], $result['message'], [
                'data' => [
                    'package_name' => $findSiteUrl->package_name,
                    'bundle_name' => $findSiteUrl->package_name,
                    'ios_app_name' => null,
                ]
            ]);
        }

        $findSiteUrl->update(['ios_app_name' => $result['app_name']]);

        return $jsonResponse(Response::HTTP_OK, $result['message'], [
            'data' => [
                'package_name' => $findSiteUrl->package_name,
                'bundle_name' => $findSiteUrl->package_name,
                'ios_app_name' => $result['app_name'],
            ]
        ]);
    }
}

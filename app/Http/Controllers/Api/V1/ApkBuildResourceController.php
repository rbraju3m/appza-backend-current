<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\ApkBuildRequest;
use App\Http\Requests\AppNameRequest;
use App\Http\Requests\IosBuildRequest;
use App\Models\AppVersion;
use App\Models\BuildDomain;
use App\Models\Fluent;
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

        $findSiteUrl = BuildDomain::where('site_url',$input["site_url"])->where('license_key',$input['license_key'])->first();

        if (!$findSiteUrl){
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Domain Not found.');
        }

        if (!$findSiteUrl->fluent_item_id){
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Item id not found.');
        }

        $params = [
            'url' => $input['site_url'],
            'license' => $input['license_key'],
            'fluent_cart_action' => 'check_license',
            'item_id' => $findSiteUrl->fluent_item_id,
        ];

        // Send API Request
        $getFluentInfo = Fluent::where('product_slug', $findSiteUrl->plugin_name)->where('is_active',true)->first();
        if (!$getFluentInfo) {
            return $jsonResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'The fluent information not set in the configuration.');
        }
        $fluentApiUrl = $getFluentInfo->api_url;

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

        if (!config('app.is_fluent_check')) {
            /* START manually added for fluent issue & after fluent is okay it will be remove*/
            if (!$data['success']) {
                $data['success'] = true;
                $data['status'] = true;
                $data['item_id'] = null;
            }
            /* END manually added for fluent issue & after fluent is okay it will be remove*/
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
            'fluent_id' => $data['item_id'],
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

        $findSiteUrl = BuildDomain::where('site_url', $input['site_url'])
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

        $findSiteUrl = BuildDomain::where('site_url', $input['site_url'])
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

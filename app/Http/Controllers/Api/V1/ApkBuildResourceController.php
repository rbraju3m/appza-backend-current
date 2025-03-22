<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\ApkBuildRequest;
use App\Http\Requests\AppNameRequest;
use App\Http\Requests\IosBuildRequest;
use App\Models\AppVersion;
use App\Models\BuildDomain;
use App\Models\Lead;
use App\Services\IosBuildValidationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
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

        $input = $request->all();

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
        $fluentApiUrl = config('app.fluent_api_url');

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

        $findSiteUrl->update([
            'version_id' => $findAppVersion->id,
            'build_domain_id' => $findSiteUrl->id,
            'fluent_id' => $data['item_id'],
            'app_name' => $request->input('app_name'),
            'app_logo' => $appLogo,
            'app_splash_screen_image' => $splash_screen_image,
            'is_android' => $request->input('is_android'),
            'is_ios' => $request->input('is_ios'),
            'confirm_email' => $request->input('email'),
            'build_plugin_slug' => $request->input('plugin_slug'),
        ]);

        return $jsonResponse(Response::HTTP_OK, 'App selection for build requests is confirmed.',[
            'data' => [
                'package_name' => $findSiteUrl->package_name,
                'bundle_name' => $findSiteUrl->package_name,
            ]
        ]);
    }

    public function iosResource(IosBuildRequest $request) {
        $jsonResponse = function ($statusCode, $message, $additionalData = []) use ($request) {
            return new JsonResponse(array_merge([
                'status' => $statusCode,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => $message,
            ], $additionalData), $statusCode, ['Content-Type' => 'application/json']);
        };

        if (!$this->authorization) {
            return $jsonResponse(Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        if ($this->pluginName == 'lazy_task') {
            return $jsonResponse(Response::HTTP_OK, 'Build process off for lazy task');
        }

        $input = $request->all();

        $findSiteUrl = BuildDomain::where('site_url', $input["site_url"])
            ->where('license_key', $input['license_key'])
            ->first();

        if (!$findSiteUrl) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Domain or license key wrong');
        }

        $targetLocationP8File = public_path() . '/upload/build-apk/p8file/';

        if (!File::exists($targetLocationP8File)) {
            File::makeDirectory($targetLocationP8File, 0777, true, true);
        }

        $p8FileName = bin2hex(random_bytes(5)) . '_' . $input['ios_issuer_id'] . '.p8';
        $fileFullPath = $targetLocationP8File . $p8FileName;
        File::put($fileFullPath, $input['ios_p8_file_content']);

        $findSiteUrl->update([
            'ios_issuer_id' => $input['ios_issuer_id'],
            'ios_key_id' => $input['ios_key_id'],
            'team_id' => $input['ios_team_id'],
            'ios_p8_file_content' => $p8FileName,
        ]);

        if ($findSiteUrl->ios_p8_file_content) {
            $iosResponse = $this->iosBuildValidationService->iosBuildProcessValidation($findSiteUrl);

            if ($iosResponse === 'Unauthorized') {
                return $jsonResponse(Response::HTTP_UNAUTHORIZED, 'Your given information is not right, Please try with proper information.');
            }

            return $jsonResponse(Response::HTTP_OK, 'IOS Resource information is valid.', [
                'data' => [
                    'package_name' => $findSiteUrl->package_name,
                    'bundle_name' => $findSiteUrl->package_name,
                ]
            ]);
        }

        return $jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'An unknown error occurred while processing IOS resources.');
    }


    public function iosAppName(AppNameRequest $request) {
        $jsonResponse = function ($statusCode, $message, $additionalData = []) use ($request) {
            return new JsonResponse(array_merge([
                'status' => $statusCode,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => $message,
            ], $additionalData), $statusCode, ['Content-Type' => 'application/json']);
        };

        $input = $request->validated();

        $findSiteUrl = BuildDomain::where('site_url', $input["site_url"])
            ->where('license_key', $input['license_key'])
            ->first();

        if (!$findSiteUrl) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Domain or license key wrong');
        }

        $iosResponse = $this->iosBuildValidationService->iosBuildProcessValidation2($findSiteUrl, $input['app']);

        if ($iosResponse) {
            $findSiteUrl->update(['ios_app_name' => $input['app']]);
            return $jsonResponse(Response::HTTP_OK, 'The iOS app name is matched with your account.', [
                'data' => [
                    'package_name' => $findSiteUrl->package_name,
                    'bundle_name' => $findSiteUrl->package_name,
                ]
            ]);
        } else {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Please give the app name you have created on the app store.', [
                'data' => [
                    'package_name' => $findSiteUrl->package_name,
                    'bundle_name' => $findSiteUrl->package_name,
                ]
            ]);
        }
    }
}

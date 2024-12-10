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
        if (!$this->authorization){
            $response = new JsonResponse([
                'status'=>Response::HTTP_UNAUTHORIZED,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message'=>'Unauthorized',
            ],Response::HTTP_UNAUTHORIZED);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        if ($this->pluginName == 'lazy_task'){
            $response = new JsonResponse([
                'status'=>Response::HTTP_OK,
                'message'=> 'Build process off for lazy task',
            ],Response::HTTP_OK);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $input = $request->all();

        $dataForFluentCheck = [
            'url' => $input['site_url'],
            'license' => $input['license_key'],
            'fluent_cart_action' => 'activate_license'
        ];

        $fluentApiUrl = config('app.fluent_api_url');
        $response = Http::withHeaders([])->get($fluentApiUrl,$dataForFluentCheck);
        $body = $response->getBody()->getContents();
        $fluentRes = json_decode($body,true);

        if (isset($fluentRes['code']) && $fluentRes['code'] == 'rest_no_route'){
            $response = new JsonResponse([
                'status'=>Response::HTTP_NOT_FOUND,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message'=>$fluentRes['data']['error'],
            ],Response::HTTP_NOT_FOUND);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        if (!$fluentRes['success']){
            $response = new JsonResponse([
                'status'=>Response::HTTP_NOT_FOUND,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message'=>$fluentRes['data']['error'],
            ],Response::HTTP_NOT_FOUND);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        if ($fluentRes['success']){
            $findSiteUrl = BuildDomain::where('site_url',$input["site_url"])->where('license_key',$input['license_key'])->first();
            if ($findSiteUrl){
                $targetLocationLogo = public_path().'/upload/build-apk/logo/';
                $targetLocationSplash = public_path().'/upload/build-apk/splash/';

                if ($input['app_logo']) {
                    $url = $input['app_logo'];
                    $fileHeaders = @get_headers($url);
                    if (!$fileHeaders || $fileHeaders[0] == 'HTTP/1.1 404 Not Found') {
                        $response = new JsonResponse([
                            'status' => Response::HTTP_NOT_FOUND,
                            'url' => $request->getUri(),
                            'method' => $request->getMethod(),
                            'message' => 'App logo invalid file URL',
                        ], Response::HTTP_OK);
                        $response->headers->set('Content-Type', 'application/json');
                        return $response;
                    }

                    if(!File::exists($targetLocationLogo)) {
                        File::makeDirectory($targetLocationLogo, 0777, true);
                    }

                    $fileName = bin2hex(random_bytes(5)).'_'.basename($url);
                    $fileContent = @file_get_contents($url);

                    // Check if the file was able to be opened
                    if ($fileContent === FALSE) {
                        $response = new JsonResponse([
                            'status' => Response::HTTP_NOT_FOUND,
                            'url' => $request->getUri(),
                            'method' => $request->getMethod(),
                            'message' => 'App logo could not open file at URL',
                        ], Response::HTTP_OK);
                        $response->headers->set('Content-Type', 'application/json');
                        return $response;
                    }

                    file_put_contents($targetLocationLogo . $fileName, $fileContent);
                    $appLogo = $fileName;
                }

                if ($input['app_splash_screen_image']) {
                    // Check if the URL points to a valid file
                    $url = $input['app_splash_screen_image'];
                    $fileHeaders = @get_headers($url);
                    if (!$fileHeaders || $fileHeaders[0] == 'HTTP/1.1 404 Not Found') {
                        $response = new JsonResponse([
                            'status' => Response::HTTP_NOT_FOUND,
                            'url' => $request->getUri(),
                            'method' => $request->getMethod(),
                            'message' => 'app splash screen image invalid file URL',
                        ], Response::HTTP_OK);
                        $response->headers->set('Content-Type', 'application/json');
                        return $response;
                    }

                    if(!File::exists($targetLocationSplash)) {
                        File::makeDirectory($targetLocationSplash, 0777, true);
                    }

                    $fileName = bin2hex(random_bytes(5)).'_'.basename($url);
                    $fileContent = @file_get_contents($url);

                    // Check if the file was able to be opened
                    if ($fileContent === FALSE) {
                        $response = new JsonResponse([
                            'status' => Response::HTTP_NOT_FOUND,
                            'url' => $request->getUri(),
                            'method' => $request->getMethod(),
                            'message' => 'app splash screen image could not open file at URL',
                        ], Response::HTTP_OK);
                        $response->headers->set('Content-Type', 'application/json');
                        return $response;
                    }

                    file_put_contents($targetLocationSplash . $fileName, $fileContent);
                    $splash_screen_image = $fileName;
                }

                $findAppVersion = AppVersion::where('is_active', 1)->latest()->first();

                $findSiteUrl->update([
                    'version_id' => $findAppVersion->id,
                    'build_domain_id' => $findSiteUrl->id,
                    'fluent_id' => $fluentRes['data']['item_id'],
                    'app_name' => $request->input('app_name'),
                    'app_logo' => $appLogo,
                    'app_splash_screen_image' => $splash_screen_image,
                    'is_android' => $request->input('is_android'),
                    'is_ios' => $request->input('is_ios'),
                    'confirm_email' => $request->input('email'),
                ]);

                $response = new JsonResponse([
                    'status' => Response::HTTP_OK,
                    'url' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'message' => 'App selection for build requests is confirmed.',
                    'data' => $findSiteUrl
                ], Response::HTTP_OK);
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }else{
                $response = new JsonResponse([
                    'status' => Response::HTTP_NOT_FOUND,
                    'url' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'message' => 'Domain Not found',
                ], Response::HTTP_OK);
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
        }
    }

    public function iosResource(IosBuildRequest $request){
        if (!$this->authorization){
            $response = new JsonResponse([
                'status'=>Response::HTTP_UNAUTHORIZED,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message'=>'Unauthorized',
            ],Response::HTTP_UNAUTHORIZED);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        if ($this->pluginName == 'lazy_task'){
            $response = new JsonResponse([
                'status'=>Response::HTTP_OK,
                'message'=> 'Build process off for lazy task',
            ],Response::HTTP_OK);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $input = $request->all();

        $findSiteUrl = BuildDomain::where('site_url',$input["site_url"])->where('license_key',$input['license_key'])->first();
        if ($findSiteUrl){
            $targetLocationP8File = public_path().'/upload/build-apk/p8file/';

            if(!File::exists($targetLocationP8File)) {
                File::makeDirectory($targetLocationP8File, 0777, true, true);
            }

            $p8FileName = bin2hex(random_bytes(5)).'_'.$input['ios_issuer_id'].'.p8';
            $fileFullPath = $targetLocationP8File.$p8FileName;
            File::put($fileFullPath, $input['ios_p8_file_content']);

            $findSiteUrl->update([
                'ios_issuer_id'=>$input['ios_issuer_id'],
                'ios_key_id'=>$input['ios_key_id'],
                'team_id'=>$input['ios_team_id'],
                'ios_p8_file_content'=>$p8FileName,
            ]);
            if ($findSiteUrl->ios_p8_file_content){
                $iosResponse = $this->iosBuildValidationService->iosBuildProcessValidation($findSiteUrl);

                if ($iosResponse==='Unauthorized'){
                    $response = new JsonResponse([
                        'status' => Response::HTTP_UNAUTHORIZED,
                        'url' => $request->getUri(),
                        'method' => $request->getMethod(),
                        'message' => 'Your given information is not right, Please try with proper information.'
                    ], Response::HTTP_UNAUTHORIZED);
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }else{
                    $response = new JsonResponse([
                        'status' => Response::HTTP_OK,
                        'url' => $request->getUri(),
                        'method' => $request->getMethod(),
                        'message' => 'IOS Resource information is valid.',
                        'data' => [
                            'package_name' => $findSiteUrl->package_name,
                            'bundle_name' => $findSiteUrl->package_name,
                        ]
                    ], Response::HTTP_OK);
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }
            }
        }else{
            $response = new JsonResponse([
                'status' => Response::HTTP_NOT_FOUND,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => 'Domain or license key wrong',
            ], Response::HTTP_NOT_FOUND);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    public function iosAppName(AppNameRequest $request)
    {
        $input = $request->validated();
        $findSiteUrl = BuildDomain::where('site_url',$input["site_url"])->where('license_key',$input['license_key'])->first();

        if ($findSiteUrl) {
            $iosResponse = $this->iosBuildValidationService->iosBuildProcessValidation2($findSiteUrl, $input['app']);
            if ($iosResponse) {
                $response = new JsonResponse([
                    'status' => Response::HTTP_OK,
                    'url' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'message' => 'The iOS app name is matched with your account.',
                    'data' => [
                        'package_name' => $findSiteUrl->package_name,
                        'bundle_name' => $findSiteUrl->package_name,
                    ]
                ], Response::HTTP_OK);
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            } else {
                $response = new JsonResponse([
                    'status' => Response::HTTP_NOT_FOUND,
                    'url' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'message' => 'Please give the app name you have created on the app store.',
                    'data' => [
                        'package_name' => $findSiteUrl->package_name,
                        'bundle_name' => $findSiteUrl->package_name,
                    ]
                ], Response::HTTP_NOT_FOUND);
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
        }else{
            $response = new JsonResponse([
                'status' => Response::HTTP_NOT_FOUND,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => 'Domain or license key wrong',
            ], Response::HTTP_NOT_FOUND);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

}

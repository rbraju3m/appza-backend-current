<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\BuildResponseRequest;
use App\Http\Requests\FinalBuildRequest;
use App\Jobs\ProcessBuild;
use App\Models\ApkBuildHistory;
use App\Models\BuildDomain;
use App\Models\BuildOrder;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Services\IosBuildValidationService;
use Carbon\Carbon;


class ApkBuildHistoryController extends Controller
{
    protected $authorization;
    protected $domain;
    protected $pluginName;
    protected $customerName;
    protected $iosBuildValidationService;

    public function __construct(Request $request,IosBuildValidationService $iosBuildValidationService){
        $data = Lead::checkAuthorization($request);
        $this->authorization = ($data && $data['auth_type'])?$data['auth_type']:false;
        $this->domain = ($data && $data['domain'])?$data['domain']:'';
        $this->pluginName = ($data && $data['plugin_name'])?$data['plugin_name']:'';
        $this->customerName = ($data && $data['customer_name'])?$data['customer_name']:'';
        $this->iosBuildValidationService = $iosBuildValidationService;
    }

    public function apkBuild(FinalBuildRequest $request) {
        $jsonResponse = function ($statusCode, $message, $additionalData = []) use ($request) {
            return new JsonResponse(array_merge([
                'status' => $statusCode,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => $message,
            ], $additionalData), $statusCode, ['Content-Type' => 'application/json']);
        };

        $isBuilderON = config('app.is_builder_on');

        if (!$isBuilderON) {
            return $jsonResponse(Response::HTTP_LOCKED, 'Builder is busy. Please try again later.');
        }

        if (!$this->authorization) {
            return $jsonResponse(Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        if ($this->pluginName == 'lazy_task') {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Build process off for lazy task');
        }

        $input = $request->validated();

        $findSiteUrl = BuildDomain::where('site_url', $input["site_url"])
            ->where('license_key', $input['license_key'])
            ->where('package_name', $input['package_name'])
            ->first();

        if (!$findSiteUrl) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Domain or license key wrong');
        }

        // for builder application supports
        $builderSupportsPlugin = ['woocommerce', 'tutor-lms','wordpress'];
        if (empty($findSiteUrl->build_plugin_slug)) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Plugin slug missing , first request build resource api.');
        }

        if (!in_array($findSiteUrl->build_plugin_slug, $builderSupportsPlugin, true)) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Builder not supported this plugin');
        }

        $apkBuildExists = BuildOrder::whereIn('status', ['processing','pending'])
            ->where('issuer_id', $findSiteUrl->ios_issuer_id)
            ->exists();

        if ($apkBuildExists) {
            return $jsonResponse(Response::HTTP_CONFLICT, 'An app building process is already going on. Please try again later.');
        }

        $buildHistory = ApkBuildHistory::create([
            'version_id' => $findSiteUrl->version_id,
            'build_domain_id' => $findSiteUrl->id,
            'fluent_id' => $findSiteUrl->fluent_id,
            'app_name' => $findSiteUrl->app_name,
            'ios_app_name' => $findSiteUrl->ios_app_name,
            'app_logo' => $findSiteUrl->app_logo,
            'app_splash_screen_image' => $findSiteUrl->app_splash_screen_image,
            'ios_issuer_id' => $findSiteUrl->ios_issuer_id,
            'ios_key_id' => $findSiteUrl->ios_key_id,
            'ios_team_id' => $findSiteUrl->team_id,
            'ios_p8_file_content' => $findSiteUrl->ios_p8_file_content,
        ]);

        // Start APK build job
        $this->buildRequestProcessForJob($buildHistory, $findSiteUrl,$isBuilderON);

        return $jsonResponse(Response::HTTP_OK, 'Your App building process has been started successfully.', [
            'data' => $buildHistory
        ]);
    }

    private function buildRequestProcessForJob($buildHistory, $findSiteUrl, $isBuilderON)
    {
        $data = [
            'build_plugin_slug' => $findSiteUrl->build_plugin_slug,
            'package_name' => $findSiteUrl->package_name,
            'domain' => $findSiteUrl->site_url,
            'base_suffix' => '/wp-json/appza/api/v1/',
            'base_url' => rtrim($findSiteUrl->site_url, '/') . '/wp-json/appza/api/v1/',
            'icon_url' => url('') . '/upload/build-apk/logo/' . $buildHistory->app_logo
        ];

        // Send email notification
        if (config('app.is_send_mail')) {
            if (!empty($findSiteUrl->confirm_email) && filter_var($findSiteUrl->confirm_email, FILTER_VALIDATE_EMAIL)) {
                $details = [
                    'customer_name' => $this->customerName,
                    'subject' => 'Your App Build Request is in Progress 🚀',
                    'app_name' => $buildHistory->app_name,
                    'is_android' => $findSiteUrl->is_android,
                    'is_ios' => $findSiteUrl->is_ios,
                    'mail_template' => 'build_request'
                ];
                $isMailSend = config('app.is_send_mail');
                $isMailSend && Mail::to($findSiteUrl->confirm_email)->send(new \App\Mail\BuildRequestMail($details));
            } else {
                Log::error('Invalid email detected', ['email' => $findSiteUrl->confirm_email]);
            }
        }

        // Process Android Build
        if ($findSiteUrl->is_android) {
            $data['app_name'] = $buildHistory->app_name;
            $this->processBuildOrder($findSiteUrl, $buildHistory, $data, 'android', $isBuilderON);
        }

        // Process iOS Build
        if ($findSiteUrl->is_ios) {
            $data['app_name'] = $buildHistory->ios_app_name;
            $this->processBuildOrder($findSiteUrl, $buildHistory, $data, 'ios', $isBuilderON);
        }
    }

    private function processBuildOrder($findSiteUrl, $buildHistory, $data, $platform, $isBuilderON)
    {
//        dump($findSiteUrl->package_name);
        $data['license_key'] = $findSiteUrl->license_key;
        $data['build_domain_id'] = $findSiteUrl->id;
        $data['build_target'] = $platform;
        $data['build_number'] = $this->getNextBuildNumber($findSiteUrl->site_url, $findSiteUrl->package_name, $platform);

        // Specific fields for Android
        if ($platform === 'android') {
            /*$output = $this->handleJksFileRequest($findSiteUrl);
            if ($output['return_code'] == 0) {
                $data['jks_url'] = url('').Storage::url('jks/'.$findSiteUrl->package_name.'/upload-keystore.jks');
                $data['key_properties_url'] = url('').Storage::url('jks/'.$findSiteUrl->package_name.'/key.properties');
            }
            \Log::info($output['output']);*/

            $data['jks_url'] = url('') . '/android/upload-keystore.jks';
            $data['key_properties_url'] = url('') . '/android/key.properties';
        }
//        dump($data);

        // Specific fields for iOS
        if ($platform === 'ios') {
            $data['jks_url'] = null;
            $data['key_properties_url'] = null;

            $data['issuer_id'] = $buildHistory->ios_issuer_id;
            $data['key_id'] = $buildHistory->ios_key_id;
            $data['api_key_url'] = url('') . '/upload/build-apk/p8file/' . $buildHistory->ios_p8_file_content;
            $data['team_id'] = $buildHistory->ios_team_id;
            $data['app_identifier'] = $findSiteUrl->package_name;
        }

        try {
            $order = BuildOrder::create($data);
            if ($isBuilderON) {
                dispatch(new ProcessBuild($order->id));
            }
        } catch (\Exception $e) {
            Log::error("BuildOrder creation failed for {$platform}: " . $e->getMessage());
        }
    }

    // for handle jks file
    private function handleJksFileRequest($findSiteUrl)
    {
        $folder = $findSiteUrl->package_name;

        // Define the script path in the storage directory
        $storageScriptPath = storage_path('jks/jks_builder.sh');

        // Define the source script path in the root directory
        $rootScriptPath = base_path('jks_builder.sh');

        // Ensure the storage/jks directory exists
        if (!file_exists(dirname($storageScriptPath))) {
            mkdir(dirname($storageScriptPath), 0755, true);
        }

        // Check if the script exists in the storage directory
        if (!file_exists($storageScriptPath)) {
            // If the script does not exist in storage, copy it from the root directory
            if (!file_exists($rootScriptPath)) {
                return response()->json(['success' => false, 'error' => 'Source script file not found in root directory'], 404);
            }

            // Copy the script from the root directory to the storage directory
            if (!copy($rootScriptPath, $storageScriptPath)) {
                return response()->json(['success' => false, 'error' => 'Failed to copy script to storage directory'], 500);
            }

            // Ensure the copied script has execution permissions
            chmod($storageScriptPath, 0755);
        }

        // Ensure the script exists and is executable
        if (!file_exists($storageScriptPath)) {
            return response()->json(['success' => false, 'error' => 'Script file not found in storage directory'], 404);
        }

        if (!is_executable($storageScriptPath)) {
            return response()->json(['success' => false, 'error' => 'Script is not executable'], 403);
        }

        // Wrap arguments with shell escaping
        $command = escapeshellcmd($storageScriptPath)." ".
            "--store-pass " . escapeshellarg($folder) . " ".
            "--key-pass " . escapeshellarg($folder) . " ".
            "--replace " . escapeshellarg('y') . " ".
            "--folder " . escapeshellarg($folder) . " ".
            "--cn " . escapeshellarg('My App') . " ".
            "--ou " . escapeshellarg('My Unit') . " ".
            "--org " . escapeshellarg('My Company') . " ".
            "--location " . escapeshellarg('San Francisco') . " ".
            "--state " . escapeshellarg('CA') . " ".
            "--country " . escapeshellarg('US');
        // Execute and capture output
        $output = [];
        $returnCode = 0;

        exec("$command 2>&1", $output, $returnCode);

        $outputText = implode("\n", $output);

        return [
            'output' => $outputText,
            'return_code' => $returnCode
        ];
    }

    private function getNextBuildNumber($domain, $packageName, $buildTarget)
    {
        $latestBuild = BuildOrder::where([
            ['domain', $domain],
            ['package_name', $packageName],
            ['build_target', $buildTarget]
        ])->latest()->first();
        $number = $this->generateThreeDigitNumbers($latestBuild ? $latestBuild->build_number : 0);
        return $number;
    }

    private function generateThreeDigitNumbers($num)
    {
        return str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }

    public function apkBuildResponse(BuildResponseRequest $request, $id) {
        $jsonResponse = function ($statusCode, $message, $additionalData = []) {
            return new JsonResponse(array_merge([
                'status' => $statusCode,
                'message' => $message,
            ], $additionalData), $statusCode, ['Content-Type' => 'application/json']);
        };

        $input = $request->validated();
        $orderItem = BuildOrder::find($id);

        if (!$orderItem) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Build order not found');
        }

        $orderItem->update($input);

        $getUserInfo = Lead::where('domain', $orderItem->domain)->ActiveAndOpen()->latest()->first();

        $getBuildDomain = BuildDomain::where('site_url', $orderItem->domain)
            ->where('license_key', $orderItem->license_key)
            ->where('package_name', $orderItem->package_name)
            ->first();

        if ($orderItem->status->value === 'failed') {
            $details = [
                'customer_name' => $getUserInfo->first_name . ' ' . $getUserInfo->last_name,
                'subject' => $orderItem->build_target=='ios'?'Update on Your iOS App Build: Action Required':'Update on Your Android App Build: Action Required',
                'app_name' => $orderItem->build_target=='android'?$getBuildDomain->app_name:$getBuildDomain->ios_app_name,
                'mail_template' => $orderItem->build_target=='ios'?'build_failed_ios':'build_failed_android'
            ];

            // send mail
            $isMailSend = config('app.is_send_mail');
            if (!empty($getBuildDomain->confirm_email) && filter_var($getBuildDomain->confirm_email, FILTER_VALIDATE_EMAIL)) {
                $isMailSend && Mail::to($getBuildDomain->confirm_email)->send(new \App\Mail\BuildRequestMail($details));
            } else {
                Log::error('Invalid email detected', ['email' => $getBuildDomain->confirm_email,'order_id' => $orderItem->id]);
            }
        } elseif ($orderItem->status->value === 'completed') {
            $details = [
                'customer_name' => $getUserInfo->first_name . ' ' . $getUserInfo->last_name,
                'subject' => $orderItem->build_target=='ios'?'Your IOS App Build Is Complete! 🎉':'Your Android App Build Is Complete! 🎉',
                'app_name' => $orderItem->build_target=='android'?$getBuildDomain->app_name:$getBuildDomain->ios_app_name,
                'apk_url' => $orderItem->apk_url,
                'aab_url' => $orderItem->aab_url,
                'mail_template' => $orderItem->build_target=='ios'?'build_complete_ios':'build_complete_android'
            ];

            // send mail
            $isMailSend = config('app.is_send_mail');
            if (!empty($getBuildDomain->confirm_email) && filter_var($getBuildDomain->confirm_email, FILTER_VALIDATE_EMAIL)) {
                $isMailSend && Mail::to($getBuildDomain->confirm_email)->send(new \App\Mail\BuildRequestMail($details));
            } else {
                Log::error('Invalid email detected', ['email' => $getBuildDomain->confirm_email,'order_id' => $orderItem->id]);
            }
        }

        return $jsonResponse(Response::HTTP_OK, 'success');
    }


    // this is for test , not functional in the application , it's for builder application
    public function uploadApkIntoR2(Request $request) {
        $directory = "/var/www/html/appza-backend/public/apk-upload";
        $folder = "android-apk";

        try {
            $path = $this->getPublicUrlForUploadApk($directory, $folder);
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'success',
                'download-path' => $path
            ], Response::HTTP_OK, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws \Exception
     */
    private function getPublicUrlForUploadApk($directory, $r2Folder)
    {
        // Get the first APK file in the directory
        $apkFiles = collect(glob($directory . '/*.apk'));

        if ($apkFiles->isEmpty()) {
            throw new \Exception("No APK file found in the specified directory: $directory");
        }

        $apkFile = $apkFiles->first();
        $fileName = basename($apkFile);
        $filePath = $r2Folder . '/' . $fileName;
        $fileContents = file_get_contents($apkFile);

        // Upload with correct MIME type
        Storage::disk('r2')->put($filePath, $fileContents, [
            'visibility' => 'public',
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);

        // Return public download URL
        return config('app.image_public_path') . $filePath;
    }

    public function apkBuildList(Request $request) {
        $jsonResponse = function ($statusCode, $message, $additionalData = []) {
            return new JsonResponse(array_merge([
                'status' => $statusCode,
                'message' => $message,
            ], $additionalData), $statusCode, ['Content-Type' => 'application/json']);
        };

        if (!$this->authorization) {
            return $jsonResponse(Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        $domain = $request->query('site_url');

        if (!$domain) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Site URL is required');
        }

        $findSiteUrl = BuildDomain::where('site_url', $domain)->first();

        if (!$findSiteUrl) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Domain not found');
        }

        $buildOrders = BuildOrder::where('domain', $domain)->get();
        if ($buildOrders->isEmpty()) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Build domain not found');
        }

        // Grouping by build_target
        $grouped_builds = []; // Initialize the array

        foreach ($buildOrders as $build) {
            $item = [
                'package_name' => $build->package_name,
                'app_name' => $build->app_name,
                'domain' => $build->domain,
                'build_number' => $build->build_number,
                'icon_url' => $build->icon_url,
                'build_target' => $build->build_target,
                'status' => $build->status->name ?? 'Unknown',
                'build_plugin_slug' => $build->build_plugin_slug,
            ];

            // Ensure timestamps are not null before parsing
            if ($build->created_at && $build->updated_at) {
                $created_at = Carbon::parse($build->created_at);
                $finished_at = Carbon::parse($build->updated_at);

                $diffInMinutes = $created_at->diffInMinutes($finished_at);
                $item['created_at'] = $build->created_at->format('Y-m-d H:i:s A');
                $item['process_time'] = $diffInMinutes . ' minutes';
            } else {
                $item['created_at'] = null;
                $item['process_time'] = 'Unknown';
            }

            // Assign values based on build_target
            if ($build->build_target === 'android') {
                $item['jks_url'] = $build->jks_url;
                $item['key_properties_url'] = $build->key_properties_url;
                $item['apk_url'] = $build->apk_url;
                $item['aab_url'] = $build->aab_url;
            } elseif ($build->build_target === 'ios') {
                $item['issuer_id'] = $build->issuer_id;
                $item['key_id'] = $build->key_id;
                $item['p8_file_url'] = $build->api_key_url;
                $item['team_id'] = $build->team_id;
            }

            $grouped_builds[$build->build_target][] = $item; // Use object notation
        }

        return $jsonResponse(Response::HTTP_OK, 'Data found', [
            'data' => $grouped_builds
        ]);
    }



    public function downloadApk()
    {
//        $path = storage_path('app/public/your-app.apk');
        $path = "https://pub-df31c5b8360c4944bed15058d93cf4cc.r2.dev/android-apk/woocommercelazycoders_build_018.apk";
        return response()->download($path, 'check-app.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }


}

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
        $builderSupportsPlugin = ['woocommerce', 'tutor-lms'];
        if (empty($findSiteUrl->build_plugin_slug)) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Plugin slug missing , first request build resource api.');
        }

        if (!in_array($findSiteUrl->build_plugin_slug, $builderSupportsPlugin, true)) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Builder not supported this plugin');
        }

        $apkBuildExists = BuildOrder::where('status', 'processing')
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
            'app_name' => $buildHistory->app_name,
            'domain' => $findSiteUrl->site_url,
            'base_suffix' => '/wp-json/appza/api/v1/',
            'base_url' => rtrim($findSiteUrl->site_url, '/') . '/wp-json/appza/api/v1/',
            'icon_url' => url('') . '/upload/build-apk/logo/' . $buildHistory->app_logo
        ];

        // Send email notification
        if (config('app.is_send_mail')) {
            Mail::to($findSiteUrl->confirm_email)->send(new \App\Mail\BuildRequestMail([
                'customer_name' => $this->customerName,
                'subject' => 'Your App Build Request is in Progress',
                'app_name' => $buildHistory->app_name,
                'mail_template' => 'build_request'
            ]));
        }

        // Process Android Build
        if ($findSiteUrl->is_android) {
            $this->processBuildOrder($findSiteUrl, $buildHistory, $data, 'android', $isBuilderON);
        }

        // Process iOS Build
        if ($findSiteUrl->is_ios) {
            $this->processBuildOrder($findSiteUrl, $buildHistory, $data, 'ios', $isBuilderON);
        }
    }

    private function processBuildOrder($findSiteUrl, $buildHistory, $data, $platform, $isBuilderON)
    {
//        dump($findSiteUrl->package_name);
        $data['build_target'] = $platform;
        $data['build_number'] = $this->getNextBuildNumber($findSiteUrl->site_url, $findSiteUrl->package_name, $platform);

        // Specific fields for Android
        if ($platform === 'android') {
            /*$folder = $findSiteUrl->package_name;
            $scriptPath = public_path('jks/jks_builder.sh'); // Correct file path

            // Ensure the file exists and has execution permission
            if (!file_exists($scriptPath)) {
                return response()->json(['success' => false, 'error' => 'Script file not found'], 404);
            }

            if (!is_executable($scriptPath)) {
                return response()->json(['success' => false, 'error' => 'Script is not executable'], 403);
            }

            // Wrap arguments with shell escaping
            $command = escapeshellcmd($scriptPath)." ".
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

            // Log exact command being executed
            \Log::info("[Bash Script] Running Command: $command");

            // Execute and capture output
            $output = shell_exec("$command 2>&1");
            dump($output);*/
            $output = $this->handleJksFileRequest($findSiteUrl);
            if ($output['return_code'] == 0) {
                $data['jks_url'] = url('').Storage::url('jks/'.$findSiteUrl->package_name.'/upload-keystore.jks');
                $data['key_properties_url'] = url('').Storage::url('jks/'.$findSiteUrl->package_name.'/key.properties');
            }
//            dump($output);

//            $data['jks_url'] = url('') . '/android/upload-keystore.jks';
//            $data['key_properties_url'] = url('') . '/android/key.properties';
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
        $getUserInfo = BuildDomain::where('package_name', $orderItem->package_name)->first();

        if ($input['status'] === 'failed') {
            $details = [
                'customer_name' => $getUserInfo->app_name,
                'subject' => 'Update on Your App Build: Action Required',
                'app_name' => $getUserInfo->app_name,
                'mail_template' => 'build_failed'
            ];

            // send mail
            $isMailSend = config('app.is_send_mail');
            $isMailSend && Mail::to($getUserInfo->confirm_email)->send(new \App\Mail\BuildRequestMail($details));

        } elseif ($input['status'] === 'completed') {
            $details = [
                'customer_name' => $getUserInfo->app_name,
                'subject' => 'Your App Build Is Complete!',
                'app_name' => $getUserInfo->app_name,
                'apk_url' => $orderItem->apk_url,
                'mail_template' => 'build_complete'
            ];

            // send mail
            $isMailSend = config('app.is_send_mail');
            $isMailSend && Mail::to($getUserInfo->confirm_email)->send(new \App\Mail\BuildRequestMail($details));

        }

        return $jsonResponse(Response::HTTP_OK, 'success');
    }

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

}

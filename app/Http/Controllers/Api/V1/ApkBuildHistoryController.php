<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\BuildResponseRequest;
use App\Http\Requests\FinalBuildRequest;
use App\Jobs\ProcessBuild;
use App\Models\ApkBuildHistory;
use App\Models\BuildDomain;
use App\Models\BuildOrder;
use App\Models\Lead;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
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

    public function apkBuild(FinalBuildRequest $request)
    {
        $jsonResponse = function ($statusCode, $message, $additionalData = []) use ($request) {
            return new JsonResponse(array_merge([
                'status' => $statusCode,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => $message,
            ], $additionalData), $statusCode, ['Content-Type' => 'application/json']);
        };

        $isBuilderON = config('app.is_builder_on', false);
        if (!$isBuilderON) {
            return $jsonResponse(Response::HTTP_LOCKED, 'Builder is busy. Please try again later.');
        }

        if (!$this->authorization) {
            return $jsonResponse(Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        if ($this->pluginName === 'lazy_task') {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Build process off for lazy task');
        }

        $input = $request->validated();
        $siteUrl = $this->normalizeUrl($input['site_url']);
        $findSiteUrl = BuildDomain::where('site_url', $siteUrl)
            ->where('license_key', $input['license_key'])
            #->where('package_name', $input['package_name'])
            ->first();

        if (!$findSiteUrl) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Domain or license key wrong');
        }

        // Supported plugins
        $builderSupportsPlugin = ['woocommerce', 'tutor-lms', 'wordpress'];
        if (empty($findSiteUrl->build_plugin_slug)) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Plugin slug missing, first request build resource API.');
        }

        if (!in_array($findSiteUrl->build_plugin_slug, $builderSupportsPlugin, true)) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Builder does not support this plugin');
        }

        // Check iOS concurrency lock
        if ($findSiteUrl->is_ios == 1) {
            $apkBuildExists = BuildOrder::whereIn('status', ['processing', 'pending'])
                ->where('issuer_id', $findSiteUrl->ios_issuer_id)
                ->exists();

            if ($apkBuildExists) {
                return $jsonResponse(Response::HTTP_CONFLICT, 'An app building process is already going on. Please try again later.');
            }
        }

        // Transactionally create build history
        try {
            $buildHistory = DB::transaction(function () use ($findSiteUrl) {
                $buildData = [
                    'version_id' => $findSiteUrl->version_id,
                    'build_domain_id' => $findSiteUrl->id,
                    'fluent_id' => $findSiteUrl->fluent_id,
                    'app_name' => $findSiteUrl->app_name ?? $findSiteUrl->ios_app_name,
                    'app_logo' => $findSiteUrl->app_logo,
                    'app_splash_screen_image' => $findSiteUrl->app_splash_screen_image,
                ];

                if ($findSiteUrl->is_ios == 1) {
                    $buildData += [
                        'ios_app_name' => $findSiteUrl->ios_app_name,
                        'ios_issuer_id' => $findSiteUrl->ios_issuer_id,
                        'ios_key_id' => $findSiteUrl->ios_key_id,
                        'ios_team_id' => $findSiteUrl->team_id,
                        'ios_p8_file_content' => $findSiteUrl->ios_p8_file_content,
                    ];
                }

                return ApkBuildHistory::create($buildData);
            });

            // Dispatch job after transaction
            $this->buildRequestProcessForJob($buildHistory, $findSiteUrl, $isBuilderON);

            $findBuildOrder = BuildOrder::where('history_id', $buildHistory->id)->first();

            $data = $buildHistory->only([
                'id',
                'version_id',
                'build_domain_id',
                'app_name',
            ]);

            if ($buildHistory->ios_app_name !== null) {
                $data['ios_app_name'] = $buildHistory->ios_app_name;
            }

            if ($buildHistory->app_name !== null) {
                $data['app_name'] = $buildHistory->app_name;
            }

            if ($findBuildOrder->id !== null) {
                $data['build_id'] = $findBuildOrder->id;
            }

            $status = Response::HTTP_OK;

            $payload = [
                'status' => $status,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'message' => 'Your App building process has been started successfully.',
                'data' => $data
            ];

            // Log the response
//            Log::info("=============================================================================================================");
//            Log::info('Build process response:', ['status' => $status, 'response' => $payload,'payload' => $input]);

            // Return it
            return response()->json($payload, $status);

        } catch (\Throwable $e) {
            // Log the exception
            Log::error('Failed to start APK build process.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $jsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Failed to create build. Error: ' . $e->getMessage());
        }
    }
    private function buildRequestProcessForJob($buildHistory, $findSiteUrl, $isBuilderON)
    {
        $data = [
            'build_plugin_slug' => $findSiteUrl->build_plugin_slug,
            'package_name' => $findSiteUrl->package_name,
            'domain' => $findSiteUrl->site_url,
            'base_suffix' => '/wp-json/appza/api/v1/',
            'base_url' => rtrim($findSiteUrl->site_url, '/') . '/wp-json/appza/api/v1/',
            'icon_url' => url('') . '/upload/build-apk/logo/' . $buildHistory->app_logo,
            'history_id' => $buildHistory->id,
        ];

        // Send email notification
        if (config('app.is_send_mail',false)) {
            if (!empty($findSiteUrl->confirm_email) && filter_var($findSiteUrl->confirm_email, FILTER_VALIDATE_EMAIL)) {
                $details = [
                    'customer_name' => $this->customerName,
                    'subject' => 'Your App Build Request is in Progress 🚀',
                    'app_name' => $buildHistory->app_name,
                    'is_android' => $findSiteUrl->is_android,
                    'is_ios' => $findSiteUrl->is_ios,
                    'mail_template' => 'build_request'
                ];
                $isMailSend = config('app.is_send_mail',false);
                $isMailSend && Mail::to($findSiteUrl->confirm_email)->send(new \App\Mail\BuildRequestMail($details));
            } else {
                Log::error('Invalid email detected', ['email' => $findSiteUrl->confirm_email]);
            }
        }

        $platforms = array_filter([
            $findSiteUrl->is_android ? 'android' : null,
            $findSiteUrl->is_ios ? 'ios' : null
        ]);

        foreach ($platforms as $platform) {
            $this->processBuildOrder($findSiteUrl, $buildHistory, $data, $platform, $isBuilderON);
        }
    }

    /**
     * @throws Exception
     */
    private function processBuildOrder($findSiteUrl, $buildHistory, $data, $platform, $isBuilderON)
    {
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
            $data['app_name'] = $buildHistory->app_name;
        }
        // Specific fields for iOS
        if ($platform === 'ios') {
            $data['jks_url'] = null;
            $data['key_properties_url'] = null;

            $data['issuer_id'] = $buildHistory->ios_issuer_id;
            $data['key_id'] = $buildHistory->ios_key_id;
            $data['api_key_url'] = url('') . '/upload/build-apk/p8file/' . $buildHistory->ios_p8_file_content;
            $data['team_id'] = $buildHistory->ios_team_id;
            $data['app_identifier'] = $findSiteUrl->package_name;
            $data['app_name'] = $buildHistory->ios_app_name;
        }

        try {
            $order = BuildOrder::create($data);
            if ($isBuilderON) {
                dispatch(new ProcessBuild($order->id));
                /*dispatch(new ProcessBuild($order->id))
                    ->onQueue('builds')
                    ->delay(now()->addSeconds(10)); // Small delay to prevent race conditions*/
            }
        } catch (QueryException $e) {
            Log::error("Database error creating build order: " . $e->getMessage());
            // Notify admins
        } catch (Exception $e) {
            Log::error("Unexpected error in build process: " . $e->getMessage());
            throw $e; // Re-throw for Laravel to handle
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

    public function apkBuildResponse(BuildResponseRequest $request, $id)
    {
        // Helper function for consistent JSON responses
        $jsonResponse = function ($statusCode, $message, $additionalData = []) {
            return new JsonResponse(array_merge([
                'status' => $statusCode,
                'message' => $message,
            ], $additionalData), $statusCode, ['Content-Type' => 'application/json']);
        };

        $input = $request->validated();
        $buildOrder = BuildOrder::find($id);

        if (!$buildOrder) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Build order not found');
        }

        $buildOrder->update($input);

        // Only proceed with email logic if the status is 'failed' or 'completed'
        if (!in_array($buildOrder->status->value, ['failed', 'completed'])) {
            return $jsonResponse(Response::HTTP_OK, 'success');
        }

        // Get user info
        $userInfo = Lead::where('domain', $buildOrder->domain)
            ->ActiveAndOpen()
            ->latest()
            ->first();

        // Get build domain info
        $buildDomain = BuildDomain::where('site_url', $buildOrder->domain)
            ->where('license_key', $buildOrder->license_key)
            ->where('package_name', $buildOrder->package_name)
            ->first();

        // If we don't have the necessary information, return early
        if (!$userInfo || !$buildDomain) {
            Log::error('Missing information for build notification', [
                'order_id' => $buildOrder->id,
                'has_user_info' => (bool)$userInfo,
                'has_build_domain' => (bool)$buildDomain
            ]);
            return $jsonResponse(Response::HTTP_OK, 'success');
        }

        // Determine if we're dealing with iOS or Android
        $isIos = $buildOrder->build_target == 'ios';

        // Configure email details based on build status and target platform
        $customerName = $userInfo->first_name . ' ' . $userInfo->last_name;
        $appName = $isIos ? $buildDomain->ios_app_name : $buildDomain->app_name;

        $details = [
            'customer_name' => $customerName,
            'app_name' => $appName,
        ];

        if ($buildOrder->status->value === 'failed') {
            $details['subject'] = $isIos
                ? 'Update on Your iOS App Build: Action Required'
                : 'Update on Your Android App Build: Action Required';
            $details['mail_template'] = $isIos ? 'build_failed_ios' : 'build_failed_android';
        } else { // completed
            $details['subject'] = $isIos
                ? 'Your iOS App Build Is Complete! 🎉'
                : 'Your Android App Build Is Complete! 🎉';
            $details['mail_template'] = $isIos ? 'build_complete_ios' : 'build_complete_android';
            $details['apk_url'] = $buildOrder->apk_url;
            $details['aab_url'] = $buildOrder->aab_url;
        }

        // Send email if configuration allows and email is valid
        $isMailSendEnabled = config('app.is_send_mail', false);
        $recipientEmail = $buildDomain->confirm_email;

        if ($isMailSendEnabled && !empty($recipientEmail) && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            Mail::to($recipientEmail)->send(new \App\Mail\BuildRequestMail($details));
            Log::info('Mail sent', [
                'email' => $recipientEmail,
                'order_id' => $buildOrder->id,
                'status' => $buildOrder->status->value
            ]);
        } else {
            Log::error('Email not sent', [
                'is_mail_enabled' => $isMailSendEnabled,
                'email' => $recipientEmail ?? 'null',
                'is_valid_email' => !empty($recipientEmail) && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL),
                'order_id' => $buildOrder->id
            ]);
        }

        return $jsonResponse(Response::HTTP_OK, 'success');
    }

    public function processStart(BuildResponseRequest $request, $id) {
        $jsonResponse = function ($statusCode, $message, $additionalData = []) {
            return new JsonResponse(array_merge([
                'status' => $statusCode,
                'message' => $message,
            ], $additionalData), $statusCode, ['Content-Type' => 'application/json']);
        };

        $orderItem = BuildOrder::find($id);

        if (!$orderItem) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Build order not found');
        }

        $orderItem->update(['process_start' => now()]);

        return $jsonResponse(Response::HTTP_OK, 'success');
    }

    public function apkBuildHistoryList(Request $request) {
        $jsonResponse = function ($statusCode, $message, $additionalData = []) {
            return new JsonResponse(array_merge([
                'status' => $statusCode,
                'message' => $message,
            ], $additionalData), $statusCode, ['Content-Type' => 'application/json']);
        };

        if (!$this->authorization) {
            return $jsonResponse(Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        $siteUrl = $this->normalizeUrl($request->query('site_url'));
        $licenseKey = $request->query('license_key');

        if (!$siteUrl) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Site URL is required');
        }

        if (!$licenseKey) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'License key is required');
        }

        $findSiteUrl = BuildDomain::where('site_url', $siteUrl)->where('license_key',$licenseKey)->first();

        if (!$findSiteUrl) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Domain not found');
        }

        $buildOrders = BuildOrder::where('domain', $siteUrl)->orderBy('id','desc')->get();
        if ($buildOrders->isEmpty()) {
            return $jsonResponse(Response::HTTP_NOT_FOUND, 'Build domain not found');
        }

        // Grouping by build_target
        $grouped_builds = []; // Initialize the array

        foreach ($buildOrders as $build) {
            $item = [
//                'package_name' => $build->package_name,
                'app_name' => $build->app_name,
//                'domain' => $build->domain,
//                'build_number' => $build->build_number,
//                'icon_url' => $build->icon_url,
                'build_target' => $build->build_target,
                'status' => $build->status->name ?? 'Unknown',
//                'build_plugin_slug' => $build->build_plugin_slug,
            ];

            // Ensure timestamps are not null before parsing
            if ($build->created_at && $build->updated_at) {
                $created_at = Carbon::parse($build->created_at);
                $finished_at = Carbon::parse($build->updated_at);

                $diffInMinutes = $created_at->diffInMinutes($finished_at);
                $item['created_date'] = $build->created_at->format('d-M-Y');
                $item['created_time'] = $build->created_at->format('H:i:s A');
//                $item['process_time'] = $diffInMinutes . ' minutes';
            } else {
                $item['created_at'] = null;
                $item['process_time'] = 'Unknown';
            }

            // Assign values based on build_target
            if ($build->build_target === 'android') {
//                $item['jks_url'] = $build->jks_url;
//                $item['key_properties_url'] = $build->key_properties_url;
                $item['apk_name'] = basename($build->apk_url);
                $item['aab_name'] = basename($build->aab_url);
                $item['apk_url'] = $build->apk_url;
                $item['aab_url'] = $build->aab_url;
            } elseif ($build->build_target === 'ios') {
//                $item['issuer_id'] = $build->issuer_id;
//                $item['key_id'] = $build->key_id;
//                $item['p8_file_url'] = $build->api_key_url;
//                $item['team_id'] = $build->team_id;
            }

//            $grouped_builds[$build->build_target][] = $item; // Use object notation
            $grouped_builds[] = $item; // Use object notation
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

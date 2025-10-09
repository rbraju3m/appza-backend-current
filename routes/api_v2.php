<?php

use App\Http\Controllers\Api\V1\ApkBuildHistoryController;
use App\Http\Controllers\Api\V1\ApkBuildResourceController;
use App\Http\Controllers\Api\V1\FirebaseController;
use App\Http\Controllers\Api\V1\FreeTrialController;
use App\Http\Controllers\Api\V1\GlobalConfigController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\LicenseController as LicenseControllerV1;
use App\Http\Controllers\Api\V2\LicenseController as LicenseControllerV2;
use App\Http\Controllers\Api\V1\PageComponentController;
use App\Http\Controllers\Api\V1\PluginController;
use App\Http\Controllers\Api\V1\ThemeController;
use App\Http\Middleware\LogActivity;
use App\Http\Middleware\LogRequestResponse;
use Illuminate\Support\Facades\Route;


Route::prefix('/appza/v2')
    ->middleware([LogRequestResponse::class,LogActivity::class])
    ->group(function () {
        // lead api
        Route::prefix('lead')->name('v2_')->group(function () {
            Route::post('store/{product}', [LeadController::class, 'store'])
                ->name('create_lead')
                ->whereIn('product', ['appza', 'lazy_task','fcom_mobile']);
        });

        // theme api
        Route::prefix('themes')->name('v2_')->group(function () {
            Route::get('', [ThemeController::class,'index'])->name('themes');
            Route::get('get-theme', [ThemeController::class,'getTheme'])->name('get_theme');
        });

        // page component api
        Route::prefix('page-component')->name('v2_')->group(function () {
            Route::get('', [PageComponentController::class,'index'])
                ->name('page_component');
        });

        // global config api
        Route::prefix('global-config')->name('v2_')->group(function () {
            Route::get('', [GlobalConfigController::class,'index'])
                ->name('global_config');
        });

        // free trial request api
        Route::prefix('free')->name('v2_')->group(function () {
            Route::post('trial/{product}', [FreeTrialController::class, 'store'])
                ->name('create_free_trial')
                ->whereIn('product', ['appza', 'lazy_task','fcom_mobile']);
        });

        // firebase credential api
        Route::prefix('firebase')->group(function () {
            Route::get('credential/{product}', [FirebaseController::class,'credential'])
                ->name('firebase_credential')
                ->whereIn('product', ['appza', 'lazy_task','fcom_mobile']);;
        });

        // license api
        Route::prefix('license')->name('v2_')->group(function () {
            Route::get('check', [LicenseControllerV2::class,'webLicenseCheck'])->name('license_check');
            Route::post('activate', [LicenseControllerV2::class,'activate'])->name('license_activate');
            Route::get('deactivate', [LicenseControllerV1::class,'deactivate'])->name('license_deactivate');
            Route::post('version/check', [LicenseControllerV1::class,'versionCheck'])->name('license_version_check');
        });

        // for app api
        Route::prefix('app')->name('v2_')->group(function () {
            Route::get('license-check', [LicenseControllerV2::class,'appLicenseCheck'])->name('app_license_check');
        });

        // build api
        Route::prefix('build')->name('v2_')->group(function () {
            Route::post('', [ApkBuildHistoryController::class,'apkBuild'])->name('create_building_apk');
            Route::get('history', [ApkBuildHistoryController::class,'apkBuildHistoryList'])->name('build_history_list');
            Route::post('resource', [ApkBuildResourceController::class,'buildResource'])->name('create_build_resource');
            Route::post('ios-keys-verify', [ApkBuildResourceController::class,'iosResourceAndVerify'])->name('create_ios_resource_and_verify');
            Route::post('ios-check-app-name', [ApkBuildResourceController::class,'iosCheckAppName'])->name('ios_app_name_check');;

            // build response by builder application
            Route::post('/response/{id}', [ApkBuildHistoryController::class,'apkBuildResponse'])->name('building_apk_response');
            Route::post('/process-start/{id}', [ApkBuildHistoryController::class,'processStart'])->name('building_apk_process_start');
        });

        // plugin api
        Route::prefix('plugins')->name('v2_')->group(function () {
            Route::get('', [PluginController::class,'allPlugin'])->name('get_all_plugins');
        });


        // for app api
        Route::prefix('plugin')->name('v2_')->group(function () {
            Route::get('check-disable', [PluginController::class,'checkDisablePlugin'])->name('check_disable_plugin');
            Route::get('version-check', [PluginController::class,'pluginVersionCheck'])->name('plugin_version_check');
        });
    });


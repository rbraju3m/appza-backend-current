<?php

use App\Http\Controllers\Api\V1\ApkBuildHistoryController;
use App\Http\Controllers\Api\V1\ApkBuildResourceController;
use App\Http\Controllers\Api\V1\GlobalConfigController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\LicenseController;
use App\Http\Controllers\Api\V1\PageComponentController;
use App\Http\Controllers\Api\V1\PluginController;
use App\Http\Controllers\Api\V1\ThemeController;
use Illuminate\Support\Facades\Route;


Route::prefix('/appza/v1')
    #->middleware('auth:sanctum')
    ->group(function () {
        // lead api
        Route::prefix('lead')->group(function () {
            Route::post('store/{target_plugin}', [LeadController::class, 'store'])
                ->name('create_lead')
                ->whereIn('target_plugin', ['appza', 'lazy_task']);
        });

        // theme api
        Route::prefix('themes')->group(function () {
            Route::get('', [ThemeController::class,'index'])->name('themes');
            Route::get('get-theme', [ThemeController::class,'getTheme'])->name('get_theme');
        });

        // page component api
        Route::prefix('page-component')->group(function () {
            Route::get('', [PageComponentController::class,'index'])
                ->name('page_component');
        });

        // global config api
        Route::prefix('global-config')->group(function () {
            Route::get('', [GlobalConfigController::class,'index'])
                ->name('global_config');
        });

        // license api
        Route::prefix('license')->group(function () {
            Route::get('check', [LicenseController::class,'check'])->name('license_check');
            Route::post('activate', [LicenseController::class,'activate'])->name('license_activate');
        });

        // build api
        Route::prefix('build')->group(function () {
            Route::get('list', [ApkBuildHistoryController::class,'apkBuildList'])->name('build_apk_list');

            Route::post('resource', [ApkBuildResourceController::class,'buildResource'])->name('create_build_resource');
            Route::post('ios-keys-verify', [ApkBuildResourceController::class,'iosResourceAndVerify'])->name('create_ios_resource_and_verify');
            Route::post('ios-check-app-name', [ApkBuildResourceController::class,'iosCheckAppName'])->name('ios_app_name_check');;
            Route::post('', [ApkBuildHistoryController::class,'apkBuild'])->name('create_building_apk');

            // build response by builder application
            Route::post('/response/{id}', [ApkBuildHistoryController::class,'apkBuildResponse'])->name('building_apk_response');
            Route::post('/process-start/{id}', [ApkBuildHistoryController::class,'processStart'])->name('building_apk_process_start');
            // check apk upload into r2
            Route::get('/r2/upload', [ApkBuildHistoryController::class,'uploadApkIntoR2'])->name('upload_apk_into_r2');
//            Route::get('/r2/upload', [ApkBuildHistoryController::class,'checkSh'])->name('upload_apk_into_r2');
        });

        // plugin api
        Route::prefix('plugins')->group(function () {
            Route::get('', [PluginController::class,'allPlugin'])->name('get_all_plugins');
        });
        Route::get('plugin/check-disable', [PluginController::class,'checkDisablePlugin'])->name('check_disable_plugin');
    });


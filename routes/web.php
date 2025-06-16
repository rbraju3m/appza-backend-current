<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BuildOrderController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\ComponentGroupController;
use App\Http\Controllers\GlobalConfigController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LayoutTypeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PluginController;
use App\Http\Controllers\RequestLogController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\StyleGroupController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Show the form to request a password reset link
Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
// Send the reset link
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
// Show the form to reset password with token
Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
// Handle reset form submission
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');


Route::get('/', function () {
    return view('welcome');
});

Route::get('/api', function () {
    return view('welcome');
});

Auth::routes();

Route::prefix('admin')
    ->middleware(['auth']) // Example: Apply authentication and admin-specific middleware
    ->group(function () {
        Route::get('/dashboard', [HomeController::class, 'index'])->name('admin.dashboard');
        Route::get('/home', [HomeController::class, 'index'])->name('admin.home');
    });

Route::post('/build-orders/{id}/delete-dir', [BuildOrderController::class, 'deleteBuildDir'])
    ->name('build-orders.delete-dir');

Route::prefix('/appza')->middleware(['auth'])->group(function() {
    // for download test
    Route::get('download/apk', [\App\Http\Controllers\Api\V1\ApkBuildHistoryController::class, 'downloadApk'])->name('download_apk');
    /* layout type route start */
    Route::prefix('layout-type')->group(function () {
        Route::get('list', [LayoutTypeController::class, 'index'])->name('layout_type_list');
    });
    /* layout type route start */

    /* style group route start */
    Route::prefix('style-group')->group(function () {
        Route::get('list', [StyleGroupController::class, 'index'])->name('style_group_list');
        Route::get('create', [StyleGroupController::class, 'create'])->name('style_group_create');
        Route::post('store', [StyleGroupController::class, 'store'])->name('style_group_store');
        Route::get('edit/{id}', [StyleGroupController::class, 'edit'])->name('style_group_edit');
        Route::patch('update/{id}', [StyleGroupController::class, 'update'])->name('style_group_update');
        Route::get('assign/properties/{id}', [StyleGroupController::class, 'assignProperties'])->name('style_group_assign_properties');
        Route::PATCH('properties/update/{id}',[StyleGroupController::class,'assignPropertiesUpdate'])->name('style_group_properties_update');
    });
    /* style group route start */

    /* component group route start */
    Route::prefix('component-group')->group(function () {
        Route::get('list', [ComponentGroupController::class, 'index'])->name('component_group_list');
        Route::get('create',[ComponentGroupController::class,'create'])->name('component_group_add');
        Route::POST('add',[ComponentGroupController::class,'store'])->name('component_group_create');
        Route::get('edit/{id}',[ComponentGroupController::class, 'edit'])->name('component_group_edit');
        Route::PATCH('update/{id}',[ComponentGroupController::class, 'update'])->name('component_group_update');
        Route::get('delete/{id}',[ComponentGroupController::class,'destroy'])->name('component_group_delete');
    });
    /* component group route end */


    /* component route start */
    Route::prefix('component')->group(function () {
        Route::get('list',[ComponentController::class,'index'])->name('component_list');
        Route::get('create',[ComponentController::class,'create'])->name('component_add');
        Route::post('store',[ComponentController::class,'store'])->name('component_store');
        Route::get('delete/{id}',[ComponentController::class,'destroy'])->name('component_delete');
        Route::get('edit/{id}',[ComponentController::class, 'edit'])->name('component_edit');
        Route::get('properties/inline/update',[ComponentController::class, 'componentPropertiesInlineUpdate'])->name('component_properties_inline_update');
        Route::PATCH('update/{id}',[ComponentController::class, 'update'])->name('component_update');
        Route::POST('plugin-slug/update',[ComponentController::class, 'updatePluginSlug'])->name('plugin_slug_update_component');
    });
    /* component route end */


    /* global config route start */
    Route::prefix('global-config')->group(function () {
        Route::get('list',[GlobalConfigController::class,'index'])->name('global_config_list');
        Route::get('create/{mode}',[GlobalConfigController::class,'create'])->name('global_config_add');
        Route::get('edit/{id}',[GlobalConfigController::class, 'edit'])->name('global_config_edit');
        Route::PATCH('update/{id}',[GlobalConfigController::class, 'update'])->name('global_config_update');
        Route::get('assign-component',[GlobalConfigController::class, 'globalConfigAssignComponent'])->name('global_config_assign_component');
        Route::get('assign-component-position',[GlobalConfigController::class, 'globalConfigAssignComponentPosition'])->name('global_config_assign_component_position');
        Route::POST('plugin-slug/update',[GlobalConfigController::class, 'updatePluginSlug'])->name('plugin_slug_update_config');
    });
    /* global config route end */

    /* theme route start */
    Route::prefix('theme')->group(function () {
        Route::get('list',[ThemeController::class,'index'])->name('theme_list');
        Route::get('create',[ThemeController::class,'create'])->name('theme_add');
        Route::POST('store',[ThemeController::class,'store'])->name('theme_store');

        Route::get('assign/component/{id}',[ThemeController::class, 'themeAssignComponent'])->name('theme_assign_component');
        Route::get('assign/component-update',[ThemeController::class, 'themeAssignComponentUpdate'])->name('theme_assign_component_update');
        Route::get('page/inline/update',[ThemeController::class, 'themePageInlineUpdate'])->name('theme_page_inline_update');


        Route::get('edit/{id}',[ThemeController::class,'edit'])->name('theme_edit');
        Route::PATCH('update/{id}',[ThemeController::class, 'update'])->name('theme_update');
        Route::get('delete/{id}',[ThemeController::class, 'destroy'])->name('theme_delete');

        Route::get('sort', [ThemeController::class, 'sortTheme'])->name('theme_sort');
        Route::get('sort/data',[ThemeController::class, 'themeSortData'])->name('theme_sort_data');
        Route::put('sort/update',[ThemeController::class, 'themeSortUpdate'])->name('theme_sort_update');

        Route::post('/store-theme-photo-gallery',[ThemeController::class, 'storePhotoGallery'])->name('store_photo_gallery_for_theme');
        Route::get('/gallery/image/{id}',[ThemeController::class, 'photoGalleryImageDelete'])->name('theme_gallery_image_delete');
    });
    /*theme route end*/

    /* page route start */
    Route::prefix('page')->group(function () {
        Route::get('list',[PageController::class,'index'])->name('page_list');
        Route::get('scope/list',[PageController::class,'scopeIndex'])->name('scope_list');
        Route::get('create',[PageController::class,'create'])->name('page_add');
        Route::POST('store',[PageController::class,'store'])->name('page_store');
        Route::get('edit/{id}',[PageController::class, 'edit'])->name('page_edit');
        Route::PATCH('update/{page}',[PageController::class, 'update'])->name('page_update');
        Route::get('delete/{id}',[PageController::class,'destroy'])->name('page_delete');
        Route::get('force-delete/{id}',[PageController::class,'forceDestroy'])->name('page_force_delete');
    });
    /* page route end */

    /* plugin route start */
    Route::prefix('plugin')->group(function () {
        Route::get('list',[PluginController::class,'index'])->name('plugin_list');
        Route::get('create',[PluginController::class,'create'])->name('plugin_add');
        Route::POST('store',[PluginController::class,'store'])->name('plugin_store');
        Route::get('edit/{id}',[PluginController::class, 'edit'])->name('plugin_edit');
        Route::PATCH('update/{plugin}',[PluginController::class, 'update'])->name('plugin_update');

        Route::get('sort', [PluginController::class, 'sortPlugin'])->name('plugin_sort');
        Route::get('sort/data',[PluginController::class, 'pluginSortData'])->name('plugin_sort_data');
        Route::put('sort/update',[PluginController::class, 'pluginSortUpdate'])->name('plugin_sort_update');
    });
    /* plugin route end */

    /* build-order start */
    Route::prefix('setup')->group(function () {
        Route::get('list',[SetupController::class,'index'])->name('setup_list');
        Route::get('create',[SetupController::class,'create'])->name('setup_add');
        Route::post('store',[SetupController::class,'store'])->name('setup_store');
        Route::get('edit/{id}',[SetupController::class, 'edit'])->name('setup_edit');
        Route::PATCH('update/{setup}',[SetupController::class, 'update'])->name('setup_update');
        Route::get('delete/{id}',[SetupController::class,'destroy'])->name('setup_delete');
    });
    /* build-order route end */

    /* build-order start */
    Route::prefix('build-order')->group(function () {
        Route::get('list',[BuildOrderController::class,'index'])->name('build_order_list');
    });
    /* build-order route end */

    /* request log start */
    Route::prefix('request-log')->group(function () {
        Route::get('list',[RequestLogController::class,'index'])->name('request_log_list');
    });
    /* request log route end */
});


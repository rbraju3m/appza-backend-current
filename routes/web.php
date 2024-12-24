<?php

use App\Http\Controllers\ComponentController;
use App\Http\Controllers\ComponentGroupController;
use App\Http\Controllers\GlobalConfigController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LayoutTypeController;
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


Route::prefix('/appza')->middleware(['auth'])->group(function() {

    /* layout type route start */
    Route::prefix('layout-type')->group(function () {
        Route::get('list', [LayoutTypeController::class, 'index'])->name('layout_type_list');
    });
    /* layout type route start */

    /* style group route start */
    Route::prefix('style-group')->group(function () {
        Route::get('list', [StyleGroupController::class, 'index'])->name('style_group_list');
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
});


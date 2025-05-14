<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Models\ComponentStyleGroup;
use App\Models\GlobalConfig;
use App\Models\Page;
use App\Models\SupportsPlugin;
use App\Models\Theme;
use App\Models\ThemeComponent;
use App\Models\ThemeComponentStyle;
use App\Models\ThemeConfig;
use App\Models\ThemePage;
use App\Models\ThemePhotoGallery;
use App\Traits\HandlesFileUploads;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ThemeController extends Controller
{
    use ValidatesRequests;
    use HandlesFileUploads;

    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function index()
    {
        $themes = Theme::active()
            ->join('appza_supports_plugin', 'appza_supports_plugin.slug', '=', 'appfiy_theme.plugin_slug')
            ->select(['appfiy_theme.id', 'appfiy_theme.name as theme_name', 'appfiy_theme.appbar_id', 'appfiy_theme.navbar_id', 'appfiy_theme.drawer_id','appza_supports_plugin.name as plugin_name'])
            ->with(['appbar:id,name', 'navbar:id,name', 'drawer:id,name'])
            ->latest('appfiy_theme.id')
            ->paginate(20);

        return view('theme.index', compact('themes'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */

    public function create()
    {
        $dropdowns = [
            'pluginDropdown' => SupportsPlugin::getPluginDropdown(),
        ];

        // Fetch all plugins with a "home-page" in one query
        $homePagePlugins = Page::where('slug', 'home-page')
            ->pluck('plugin_slug')
            ->toArray();

        $categorizedDropdowns = [
            'withHomePage' => [],
            'withoutHomePage' => [],
        ];

        // Categorize the plugin dropdown
        foreach ($dropdowns['pluginDropdown'] as $key => $value) {
            if (in_array($key, $homePagePlugins)) {
                $categorizedDropdowns['withHomePage'][$key] = $value;
            } else {
                $categorizedDropdowns['withoutHomePage'][$key] = $value;
            }
        }

        return view('theme.add', $dropdowns,$categorizedDropdowns);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     */

    public function store(Request $request)
    {
        // Validate the incoming request
        $this->validate($request, [
//            'name' => 'required|unique:appfiy_theme,name',
            'name' => 'required',
            'plugin_slug' => 'required',
        ], [
            'name.required' => __('messages.enterThemeName'),
            'name.unique' => __('messages.themeNameMustbeUnique'),
            'plugin_slug.required' => __('messages.choosePlugin'),
        ]);

        try {
            DB::beginTransaction();

            // Prepare data
            $input = $request->all();
            $input['slug'] = Str::slug($input['name']);

            // Handle Image Upload
            $input['image'] = config('app.is_image_update')
                ? $this->handleFileUpload($request, '', 'image', 'theme')
                : null;

            // Store combined appbar, navbar, drawer IDs as JSON
//            $appbarNavbarDrawer = [$input['appbar_id'], $input['navbar_id'], $input['drawer_id']];
//            $input['appbar_navbar_drawer'] = json_encode($appbarNavbarDrawer);

            // Create Theme
            $theme = Theme::create($input);

            // Create ThemeConfig for each global configuration
            /*foreach ($appbarNavbarDrawer as $configId) {
                $this->createThemeConfig($theme->id, $configId);
            }*/

            // Create ThemePage and components
            $this->createThemePagesAndComponents($theme, $input['background_color']);

            DB::commit();

            // Redirect on success
            Session::flash('message', __('messages.CreateMessage'));
            return redirect()->route('theme_edit', $theme->id);
//            return redirect()->route('theme_assign_component', $theme->id);
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('danger', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    protected function createThemeConfig($themeId, $configId)
    {
        $globalConfig = GlobalConfig::findOrFail($configId);

        ThemeConfig::create([
            'theme_id' => $themeId,
            'global_config_id' => $configId,
            'mode' => $globalConfig->mode,
            'name' => $globalConfig->name,
            'slug' => $globalConfig->slug,
            'background_color' => $globalConfig->background_color,
            'layout' => $globalConfig->layout,
            'icon_theme_size' => $globalConfig->icon_theme_size,
            'icon_theme_color' => $globalConfig->icon_theme_color,
            'shadow' => $globalConfig->shadow,
            'icon' => $globalConfig->icon,
            'automatically_imply_leading' => $globalConfig->automatically_imply_leading,
            'center_title' => $globalConfig->center_title,
            'flexible_space' => $globalConfig->flexible_space,
            'bottom' => $globalConfig->bottom,
            'shape_type' => $globalConfig->shape_type,
            'shape_border_radius' => $globalConfig->shape_border_radius,
            'toolbar_opacity' => $globalConfig->toolbar_opacity,
            'actions_icon_theme_color' => $globalConfig->actions_icon_theme_color,
            'actions_icon_theme_size' => $globalConfig->actions_icon_theme_size,
            'title_spacing' => $globalConfig->title_spacing,
        ]);
    }

    protected function createThemePagesAndComponents($theme, $backgroundColor)
    {
        $pages = Page::where('is_active', 1)->whereIn('plugin_slug',[$theme->plugin_slug,'wordpress'])->get();

        foreach ($pages as $page) {
            if ((($theme->plugin_slug != $page->plugin_slug) && ($page->plugin_slug == 'wordpress' && $page->slug != 'home-page')) || $theme->plugin_slug == $page->plugin_slug) {
                $themePage = ThemePage::create([
                    'theme_id' => $theme->id,
                    'page_id' => $page->id,
                    'persistent_footer_buttons' => null,
                    'background_color' => $backgroundColor,
                    'border_color' => $page->border_color,
                    'border_radius' => $page->border_radius,
                ]);

                $components = Component::where('scope', 'LIKE', '%' . $page->slug . '%')
                    ->whereIn('plugin_slug', [$theme->plugin_slug, 'wordpress'])
                    ->where('is_active', 1)
                    ->where('is_upcoming', 0)
                    ->get();

                foreach ($components as $component) {
                    if ($component->plugin_slug == $page->plugin_slug) {
                        $themeComponent = ThemeComponent::create([
                            'theme_id' => $theme->id,
                            'component_id' => $component->id,
                            'theme_page_id' => $themePage->id,
                            'display_name' => $component->name,
                            'clone_component' => 3,
                        ]);

                        $this->createThemeComponentStyles($theme->id, $themeComponent->id, $component->id);
                    }
                }
            }
        }
    }

    protected function createThemeComponentStyles($themeId, $themeComponentId, $componentId)
    {
        $activeStyles = ComponentStyleGroup::where('component_id', $componentId)
            ->where('is_checked', true)
            ->pluck('style_group_id');

        $componentStyles = DB::table('appfiy_component_style_group_properties')
            ->join('appfiy_style_group', 'appfiy_style_group.id', '=', 'appfiy_component_style_group_properties.style_group_id')
            ->select([
                'appfiy_component_style_group_properties.name',
                'appfiy_component_style_group_properties.input_type',
                'appfiy_component_style_group_properties.value',
                'appfiy_component_style_group_properties.style_group_id',
            ])
            ->where('appfiy_component_style_group_properties.component_id', $componentId)
            ->whereIn('appfiy_component_style_group_properties.style_group_id', $activeStyles)
            ->where('appfiy_component_style_group_properties.is_active', 1)
            ->whereNull('appfiy_component_style_group_properties.deleted_at')
            ->get();

        foreach ($componentStyles as $style) {
            ThemeComponentStyle::create([
                'theme_id' => $themeId,
                'theme_component_id' => $themeComponentId,
                'name' => $style->name,
                'input_type' => $style->input_type,
                'value' => $style->value,
                'style_group_id' => $style->style_group_id,
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function themeAssignComponent($id)
    {
        // Fetch theme details
        $themeDetails = Theme::findOrFail($id);

        // Fetch theme configurations
        $themeConfig = ThemeConfig::where('theme_id', $id)->get();

        // Fetch pages and their components
        $themePages = ThemePage::where('theme_id', $id)
            ->with(['page:id,name,slug', 'components:id,theme_page_id,component_id,display_name,clone_component,selected_id,sort_ordering'])
            ->get()
            ->map(function ($themePage) {
                return [
                    'theme_page_id' => $themePage->id,
                    'page_id' => $themePage->page_id,
                    'name' => $themePage->page->name,
                    'slug' => $themePage->page->slug,
                    'sort_order' => $themePage->sort_order,
                    'persistent_footer_buttons' => $themePage->persistent_footer_buttons,
                    'background_color' => $themePage->background_color,
                    'border_color' => $themePage->border_color,
                    'border_radius' => $themePage->border_radius,
                    'components' => $themePage->components->map(function ($component) {
                        return [
                            'id' => $component->id,
                            'component_id' => $component->component_id,
                            'display_name' => $component->display_name,
                            'clone_component' => $component->clone_component,
                            'selected' => $component->selected_id,
                            'sort_ordering' => $component->sort_ordering,
                        ];
                    }),
                ];
            });

        return view('theme.assign-component', compact('themeDetails', 'themeConfig', 'themePages'));
    }


    public function themeAssignComponentUpdate(Request $request)
    {
        $id = $request->get('id');
        $fieldName = $request->get('fieldName');
        $value = $request->get('value');
        $response = [];

        // Find ThemeComponent by ID
        $themeComponent = ThemeComponent::findOrFail($id);

        // Define allowed fields and their corresponding update logic
        $allowedFields = [
            'display_name' => $value,
            'clone_component' => $value,
            'selected_id' => $request->get('isChecked'),
            'sort_ordering' => $value,
        ];

        if (array_key_exists($fieldName, $allowedFields)) {
            $themeComponent->update([$fieldName => $allowedFields[$fieldName]]);
            $response['status'] = 'ok';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Invalid field name.';
        }

        return response()->json($response);
    }

    public function themePageInlineUpdate(Request $request)
    {
        $id = $request->input('id');
        $fieldName = $request->input('fieldName');
        $value = $request->input('value');
        $response = [];

        $themePage = ThemePage::find($id);

        if (!$themePage) {
            return response()->json(['status' => 'error', 'message' => 'Theme Page not found'], 404);
        }

        if ($fieldName === 'persistent_footer_buttons') {
            $themePage->update([
                'persistent_footer_buttons' => $value ? $value : null
            ]);
            $response['status'] = 'ok';
        } elseif ($fieldName === 'sort_order'){
            $themePage->update([
                'sort_order' => $value ?? null
            ]);
            $response['status'] = 'ok';
        } else {
            return response()->json(['status' => 'error', 'message' => 'Invalid fieldName'], 400);
        }

        return response()->json($response);
    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $theme = Theme::findOrFail($id);

        // Retrieve dropdown options for appbars, navbars, and drawers
        $dropdowns = [
            'appbars' => GlobalConfig::getDropdown('appbar',$theme->plugin_slug),
            'navbars' => GlobalConfig::getDropdown('navbar',$theme->plugin_slug),
            'drawers' => GlobalConfig::getDropdown('drawer',$theme->plugin_slug),
            'pluginDropdown' => SupportsPlugin::getPluginDropdown(),
            'pages' => Page::where('is_active', 1)
                ->whereIn('plugin_slug', [$theme->plugin_slug])
                ->pluck('name', 'slug'),
        ];
        $pages = Page::where('is_active', 1)->whereIn('plugin_slug',[$theme->plugin_slug,'wordpress'])->get();
        $this->AddedNewPageNewThemeUpdate($pages,$theme);

        return view('theme.edit', array_merge($dropdowns, compact('theme')));
    }

    private function AddedNewPageNewThemeUpdate($pages,$theme,$backgroundColor='#000000')
    {
        foreach ($pages as $page) {
            if ((($theme->plugin_slug != $page->plugin_slug) && ($page->plugin_slug == 'wordpress' && $page->slug != 'home-page')) || $theme->plugin_slug == $page->plugin_slug) {
                $themePageExists = ThemePage::where('theme_id', $theme->id)->where('page_id', $page->id)->first();
                if (!$themePageExists) {
                    $themePage = ThemePage::create([
                        'theme_id' => $theme->id,
                        'page_id' => $page->id,
                        'persistent_footer_buttons' => null,
                        'background_color' => $backgroundColor,
                        'border_color' => $page->border_color,
                        'border_radius' => $page->border_radius,
                    ]);

                    $components = Component::where('scope', 'LIKE', '%' . $page->slug . '%')
                        ->whereIn('plugin_slug', [$theme->plugin_slug, 'wordpress'])
                        ->where('is_active', 1)
                        ->where('is_upcoming', 0)
                        ->get();

                    foreach ($components as $component) {
                        if ($component->plugin_slug == $page->plugin_slug) {
                            $themeComponent = ThemeComponent::create([
                                'theme_id' => $theme->id,
                                'component_id' => $component->id,
                                'theme_page_id' => $themePage->id,
                                'display_name' => $component->name,
                                'clone_component' => 3,
                            ]);

                            $this->createThemeComponentStyles($theme->id, $themeComponent->id, $component->id);
                        }
                    }
                }
            }
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $this->validate($request, [
//            'name' => 'required|unique:appfiy_theme,name,' . $id,
            'name' => 'required',
            'appbar_id' => 'required',
            'navbar_id' => 'required',
            'drawer_id' => 'required',
            'plugin_slug' => 'required',
            'default_page' => 'required',
        ], [
            'name.required' => __('messages.enterThemeName'),
            'name.unique' => __('messages.themeNameMustbeUnique'),
            'appbar_id.required' => __('messages.chooseAppbar'),
            'navbar_id.required' => __('messages.chooseNavbar'),
            'drawer_id.required' => __('messages.chooseDrawer'),
            'plugin_slug.required' => __('messages.choosePlugin'),
            'default_page.required' => __('messages.chooseDefaultPage'),
        ]);

        try {
            DB::beginTransaction();

            $input = $this->prepareInput($request, $id);
            $theme = Theme::findOrFail($id);
//            $input['slug'] = Str::slug($input['name']);
            $theme->update($input);

            // Update ThemeConfig, ThemePage, and Components
            $this->updateThemeConfigs($theme, $input['appbar_navbar_drawer']);
            $this->updateThemePages($theme, $input['background_color']);

            DB::commit();

            Session::flash('message', __('messages.CreateMessage'));
            return redirect()->route('theme_assign_component', $theme->id);

        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('message', $e->getMessage());
            return redirect()->back();
        }
    }

    private function prepareInput(Request $request, $id)
    {
        $input = $request->only([
            'name', 'appbar_id', 'navbar_id', 'drawer_id', 'background_color',
            'font_family', 'text_color', 'font_size', 'transparent',
            'dashboard_page', 'login_page', 'login_modal','plugin_slug','default_page'
        ]);

        // Append additional fields
        $input['appbar_navbar_drawer'] = json_encode([
            $input['appbar_id'], $input['navbar_id'], $input['drawer_id'],
        ]);

        $theme = Theme::find($id);

        // Handle Image Upload
        $input['image'] = config('app.is_image_update')
            ? $this->handleFileUpload($request, $theme, 'image', 'theme')
            : $theme->image;

        // Generate slug only if it doesn't exist
        if (!$theme->slug) {
            $input['slug'] = Str::slug($input['name']);
        }

        return $input;
    }
    private function updateThemeConfigs($theme, $appbarNavbarDrawer)
    {
        $theme->globalConfig()->delete(); // Delete old configurations

        foreach (json_decode($appbarNavbarDrawer) as $configId) {
            $globalConfig = GlobalConfig::findOrFail($configId);
            ThemeConfig::create([
                'theme_id' => $theme->id,
                'global_config_id' => $configId,
                'mode' => $globalConfig->mode,
                'name' => $globalConfig->name,
                'slug' => $globalConfig->slug,
                'background_color' => $globalConfig->background_color,
                'layout' => $globalConfig->layout,
                'icon_theme_size' => $globalConfig->icon_theme_size,
                'icon_theme_color' => $globalConfig->icon_theme_color,
                'shadow' => $globalConfig->shadow,
                'icon' => $globalConfig->icon,
                'automatically_imply_leading' => $globalConfig->automatically_imply_leading,
                'center_title' => $globalConfig->center_title,
                'flexible_space' => $globalConfig->flexible_space,
                'bottom' => $globalConfig->bottom,
                'shape_type' => $globalConfig->shape_type,
                'shape_border_radius' => $globalConfig->shape_border_radius,
                'toolbar_opacity' => $globalConfig->toolbar_opacity,
                'actions_icon_theme_color' => $globalConfig->actions_icon_theme_color,
                'actions_icon_theme_size' => $globalConfig->actions_icon_theme_size,
                'title_spacing' => $globalConfig->title_spacing,
            ]);
        }
    }

    private function updateThemePages($theme, $backgroundColor)
    {
        $themePages = ThemePage::where('theme_id', $theme->id)->get();

        // Reset ThemeComponentStyles
        ThemeComponentStyle::where('theme_id', $theme->id)->delete();

        foreach ($themePages as $themePage) {
            // Update ThemePage background color
            $themePage->update(['background_color' => $backgroundColor]);

            $page = $themePage->page;
            $components = Component::where('scope', 'LIKE', '%' . $page->slug . '%')
                ->where('plugin_slug', $theme->plugin_slug)
                ->where('is_active', 1)
                ->get();

            foreach ($components as $component) {
                $themeComponent = ThemeComponent::firstOrCreate(
                    [
                        'theme_id' => $theme->id,
                        'theme_page_id' => $themePage->id,
                        'component_id' => $component->id,
                    ],
                    [
                        'display_name' => $component->name,
                        'clone_component' => 3,
                    ]
                );

                $this->createComponentStyles($theme->id, $themeComponent->id, $component->id);
            }
        }
    }

    private function createComponentStyles($themeId, $themeComponentId, $componentId)
    {
        $activeStyleGroups = ComponentStyleGroup::where('component_id', $componentId)
            ->where('is_checked', true)
            ->pluck('style_group_id');

        $styles = DB::table('appfiy_component_style_group_properties')
            ->join('appfiy_style_group', 'appfiy_style_group.id', '=', 'appfiy_component_style_group_properties.style_group_id')
            ->select([
                'appfiy_component_style_group_properties.name',
                'appfiy_component_style_group_properties.input_type',
                'appfiy_component_style_group_properties.value',
                'appfiy_component_style_group_properties.style_group_id',
            ])
            ->where('appfiy_component_style_group_properties.component_id', $componentId)
            ->whereIn('appfiy_component_style_group_properties.style_group_id', $activeStyleGroups)
            ->where('appfiy_component_style_group_properties.is_active', 1)
            ->whereNull('appfiy_component_style_group_properties.deleted_at')
            ->get();

        foreach ($styles as $style) {
            ThemeComponentStyle::create([
                'theme_id' => $themeId,
                'theme_component_id' => $themeComponentId,
                'name' => $style->name,
                'input_type' => $style->input_type,
                'value' => $style->value,
                'style_group_id' => $style->style_group_id,
            ]);
        }
    }
    public function storePhotoGallery(Request $request)
    {
        $id = $request->input('id');
        $theme = Theme::findOrFail($id);

        // Loop through and process uploaded files
        for ($x = 0; $x < $request->TotalFiles; $x++) {
            $fileKey = 'files' . $x;

            if ($request->hasFile($fileKey)) {
                // Store the uploaded file
                $filePath = $request->file($fileKey)->store('theme-gallery', 'r2');

                // Save photo gallery image
                $theme->photoGallery()->create([
                    'caption' => $request->input('caption'),
                    'image' => $filePath,
                ]);
            }
        }

        // Render updated gallery view and return response
        $returnHTML = view('theme.image-gallery', ['theme' => $theme->fresh()])->render();

        return response()->json(['html' => $returnHTML]);
    }
    public function photoGalleryImageDelete($id)
    {
        try {
            $photoGalleryImage = ThemePhotoGallery::findOrFail($id);

            // Delete the image from storage
            if (!empty($photoGalleryImage->image)) {
                Storage::disk('r2')->delete($photoGalleryImage->image);
            }

            // Delete the photo gallery record
            $photoGalleryImage->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Record deleted successfully',
            ]);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while deleting the record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sortTheme()
    {
        return view('theme/sort');
    }
    public function themeSortData(Request $request)
    {
        $themes = Theme::orderBy('sort_order')->get();
        $str = '<ul id="sortable">';
        if ($themes != null) {
            foreach ($themes as $theme) {
                $str .= '<li id="' . $theme->id . '"><i class="fa fa-sort"></i>' . $theme->name . '</li>';
            }
        }
        echo $str . '</ul>';
    }

    public function themeSortUpdate(Request $request)
    {
        $themeOrder = $request->input('themeOrder');
        $themeOrderArray = explode(',', $themeOrder);
        $count = 1;
        foreach ($themeOrderArray as $themeId) {
            $event = Theme::find($themeId);
            $event->sort_order = $count;
            $event->update();
            $count++;
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy($id)
    {
        Theme::find($id)->delete();
        Session::flash('delete',__('messages.deleteMessage'));
        return redirect()->route('theme_list');
    }
}

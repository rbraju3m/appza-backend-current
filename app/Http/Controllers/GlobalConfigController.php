<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\GlobalConfig;
use App\Models\GlobalConfigComponent;
use App\Models\SupportsPlugin;
use App\Models\Theme;
use App\Traits\HandlesFileUploads;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GlobalConfigController extends Controller
{
    use ValidatesRequests;
    use HandlesFileUploads;


    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        // Clean up any GlobalConfig entries with a null slug
        GlobalConfig::whereNull('slug')->first()?->delete();

        // Retrieve active GlobalConfig entries
        $globalConfig = GlobalConfig::where('is_active', 1)
            ->join('appza_supports_plugin', 'appza_supports_plugin.slug', '=', 'appfiy_global_config.plugin_slug')
            ->select('appza_supports_plugin.name as plugin_name','appfiy_global_config.*')
            ->latest('appfiy_global_config.id')
            ->paginate(20);

        return view('global-config.index',compact('globalConfig'));
    }


    /**
     * Show the form for creating a new resource.
     * @return RedirectResponse
     */
    public function create($mode)
    {
        $input = [
            'is_active' => 1,
            'automatically_imply_leading' => false,
            'center_title' => false,
            'mode' => $mode,
        ];

        $globalConfig = GlobalConfig::create($input);

        return $globalConfig->id
            ? redirect()->route('global_config_edit', $globalConfig->id)
            : redirect()->back()->with('error', __('messages.creationFailed'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */

    public function edit($id)
    {
        $data = GlobalConfig::find($id);

        $getComponents = DB::table('appfiy_component')
            ->select([
                'appfiy_component.id',
                'appfiy_component.name',
                'appfiy_component.slug',
                'appfiy_component.label',
                'appfiy_component.layout_type_id',
                'appfiy_component.icon_code',
                'appfiy_component.event',
                'appfiy_component.scope',
                'appfiy_component.class_type',
                'appfiy_component.product_type',
                'appfiy_component.is_active',
                DB::raw('CONCAT("/upload/component-image/", appfiy_component.image) AS image'),
                'appfiy_component.is_multiple',
                'appza_supports_plugin.slug as plugin_slug',
                'appza_supports_plugin.name as plugin_name'
            ])
            ->join('appza_supports_plugin', 'appza_supports_plugin.slug', '=', 'appfiy_component.plugin_slug')
            ->where('appfiy_component.scope', 'like', '%' . $data->mode . '%')
            ->where('appfiy_component.plugin_slug', $data->plugin_slug)
            ->where('appfiy_component.is_active', 1)
            ->where('appfiy_component.is_upcoming', 0)
            ->get()
            ->toArray();


        $assignComponents = GlobalConfigComponent::where('global_config_id', $id)
            ->get()
            ->keyBy('component_id')
            ->toArray();

        $assignComId = array_keys($assignComponents);
        $positions = array_column($assignComponents, 'component_position', 'component_id');

        $alreadyUse = Theme::where('appbar_id', $id)
            ->orWhere('navbar_id', $id)
            ->orWhere('drawer_id', $id)
            ->exists();

        $currencyDropdown = Currency::getDropdown();
        $pluginDropdown = SupportsPlugin::getPluginDropdown();

        return view('global-config/edit', compact(
            'data', 'getComponents', 'assignComId', 'positions', 'alreadyUse', 'currencyDropdown','pluginDropdown'
        ));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|unique:appfiy_global_config,name,' . $id,
            'plugin_slug' => 'required',
        ], [
            'name.required' => __('messages.enterName'),
            'name.unique' => __('messages.nameAlreadyExists'),
            'plugin_slug.required' => __('messages.choosePlugin'),
        ]);

        $input = $request->all();

        $globalConfig = GlobalConfig::findOrFail($id);

        if (!$globalConfig->slug) {
            $input['slug'] = Str::slug($request->name);
        }

        // Handle Image Upload
        $input['image'] = config('app.is_image_update')
            ? $this->handleFileUpload($request, $globalConfig, 'image', 'global-config')
            : $globalConfig->image;

        DB::beginTransaction();

        try {
            $globalConfig->update($input);
            DB::commit();

            return redirect()->route('global_config_list');
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('danger', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function globalConfigAssignComponent(Request $request)
    {
        $isChecked = $request->get('isChecked');
        $componentId = $request->get('componentId');
        $globalConfigId = $request->get('globalConfigId');

        // Initialize response data
        $response = [];

        if ($isChecked) {
            // Create the component record if not already existing
            GlobalConfigComponent::firstOrCreate([
                'global_config_id' => $globalConfigId,
                'component_id' => $componentId,
            ]);

            $response['status'] = 'created';
        } else {
            // Remove the component record if it exists
            $deleted = GlobalConfigComponent::where([
                ['global_config_id', $globalConfigId],
                ['component_id', $componentId],
            ])->delete();

            $response['status'] = $deleted ? 'deleted' : 'not-found';
        }

        return response()->json($response);
    }


    public function globalConfigAssignComponentPosition(Request $request)
    {
        // Attempt to update the component position directly
        $updatedRows = GlobalConfigComponent::where([
            'global_config_id' => $request->get('globalConfigId'),
            'component_id' => $request->get('componentId'),
        ])->update(['component_position' => $request->get('value')]);

        // Prepare the response
        $response = [
            'status' => $updatedRows ? 'update' : 'not-found',
        ];

        return response()->json($response);
    }

    public function updatePluginSlug(Request $request)
    {
        $component = GlobalConfig::findOrFail($request->input('id'));
        $component->update(['plugin_slug' => $request->input('value')]);
        return response()->json(['status' => 'ok'], 200);
    }
}

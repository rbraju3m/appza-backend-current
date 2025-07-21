<?php

namespace App\Http\Controllers;

use App\Models\ClassType;
use App\Models\Component;
use App\Models\ComponentStyleGroup;
use App\Models\ComponentStyleGroupProperties;
use App\Models\ComponentType;
use App\Models\GlobalConfigComponent;
use App\Models\LayoutType;
use App\Models\Scope;
use App\Models\StyleGroup;
use App\Models\StyleGroupProperties;
use App\Models\SupportsPlugin;
use App\Models\ThemeComponent;
use App\Traits\HandlesFileUploads;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class ComponentController extends Controller
{
    use ValidatesRequests;
    use HandlesFileUploads;

    public function index(Request $request)
    {
        // Clean up components with null slug
        $this->deleteComponentsWithNullSlug();

        // Get the search term from the request
        $search = $request->input('search');

        // Fetch and paginate components, applying the search condition if provided
        $components = Component::query()
            ->join('appza_supports_plugin', 'appza_supports_plugin.slug', '=', 'appfiy_component.plugin_slug')
            ->select('appfiy_component.*','appza_supports_plugin.name as plugin_name')
            ->when($search, function ($query, $search) {
                $query->where('appfiy_component.name', 'like', '%' . $search . '%')
                    ->orWhere('appfiy_component.slug', 'like', '%' . $search . '%')
                    ->orWhere('appfiy_component.label', 'like', '%' . $search . '%')
                    ->orWhere('appza_supports_plugin.slug', 'like', '%' . $search . '%')
                    ->orWhere('appza_supports_plugin.name', 'like', '%' . $search . '%')
                    ->orWhere('appfiy_component.scope', 'like', '%' . $search . '%');
            })
            ->orderByDesc('appfiy_component.id')
            ->paginate(20);

        // Include the search query in the pagination links
        $components->appends(['search' => $search]);

        // Return components to view
        return view('component.index', [
            'components' => $components,
            'search' => $search, // Pass search term back to the view for repopulating the search input
        ]);
    }


    /**
     * Deletes the first component with a null 'slug' if it exists.
     */
    protected function deleteComponentsWithNullSlug()
    {
        $deleteComponent = Component::whereNull('slug')->first();
        if ($deleteComponent) {
            $deleteComponent->delete();
        }
    }


    /**
     * Show the form for creating a new resource.
     * @return RedirectResponse
     */

    public function create()
    {
        $pluginDropdown = SupportsPlugin::getPluginDropdown();
        return view('component.add',compact('pluginDropdown'));
    }

    public function store(Request $request){
        $this->validate($request, [
            'plugin_slug' => 'required|string',
        ], [
            'plugin_slug.required' => __('messages.choosePlugin'),
        ]);

        $plugin = $request->get("plugin_slug");
        $input = [
            'parent_id' => null,
            'layout_type_id' => null,
            'name' => null,
            'slug' => null,
            'plugin_slug' => $plugin,
        ];

        $component = Component::create($input);

        if (!$component) {
            return redirect()->back()->with('error', __('messages.componentCreationFailed'));
        }

        $styleGroups = StyleGroup::where('is_active', 1)->whereJsonContains('plugin_slug',$plugin)->get();

        $this->AddedComponentStylesWithProperties($styleGroups, $component);

        return redirect()->route('component_edit', $component->id);
    }

    private function AddedComponentStylesWithProperties($styleGroups,$component)
    {
        $styleGroups->each(function ($group) use ($component) {
            $exists = ComponentStyleGroup::where('component_id', $component->id)->where('style_group_id', $group->id)->first();
            if (!$exists) {
                // Create ComponentStyleGroup
                ComponentStyleGroup::create([
                    'component_id' => $component->id,
                    'style_group_id' => $group->id,
                ]);

                // Fetch properties linked to the style group
                $properties = StyleGroupProperties::where('appfiy_style_group_properties.style_group_id', $group->id)
                    ->where('appfiy_style_properties.is_active', 1)
                    ->join('appfiy_style_properties', 'appfiy_style_properties.id', '=', 'appfiy_style_group_properties.style_property_id')
                    ->select([
                        'appfiy_style_properties.name',
                        'appfiy_style_properties.input_type',
                        'appfiy_style_properties.value',
                        'appfiy_style_properties.default_value',
                    ])
                    ->get();

                // Create ComponentStyleGroupProperties for each property
                $properties->each(function ($property) use ($component, $group) {
                    ComponentStyleGroupProperties::create([
                        'component_id' => $component->id,
                        'style_group_id' => $group->id,
                        'name' => $property->name,
                        'input_type' => $property->input_type,
                        'value' => $property->value,
                        'default_value' => $property->default_value,
                    ]);
                });
            }
        });
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */

    public function edit($id)
    {
        // Fetch required data
        $data = Component::findOrFail($id);
        $layoutTypes = LayoutType::where('is_active', 1)->pluck('name', 'id');

        $styleGroups = StyleGroup::where('is_active', 1)->whereJsonContains('plugin_slug',$data->plugin_slug)->get();
        $this->AddedComponentStylesWithProperties($styleGroups, $data);

        $properties = $this->getComponentStyleGroupProperties($id, $styleGroups);
        $componentStyleIdArray = $this->getCheckedStyleGroupIds($properties);

        $scopes = $this->formatScopes(Scope::select(['id', 'name', 'slug', 'is_global'])->whereIn('plugin_slug',[$data->plugin_slug,'global'])->get());
        $componentType = ComponentType::where('is_active', 1)->get();
        $pluginDropdown = SupportsPlugin::getPluginDropdown();

        // Check if the component is in use
        $alreadyUse = $this->checkIfComponentIsAlreadyInUse($id);

        $classTypesDropdown = ClassType::getDropdown($data->plugin_slug);

        // Pass data to view
        return view('component.edit', [
            'data' => $data,
            'layoutTypes' => $layoutTypes,
            'scopeArrayData' => $scopes,
            'styleGroups' => $styleGroups,
            'componentStyleIdArray' => $componentStyleIdArray,
            'componentStyleGroup' => $properties,
            'componentType' => $componentType,
            'pluginDropdown' => $pluginDropdown,
            'classTypesDropdown' => $classTypesDropdown,
            'alreadyUse' => $alreadyUse
        ]);
    }

    /**
     * Fetch and process component style group properties.
     *
     * @param int $componentId
     * @param Collection $styleGroups
     * @return array
     */
    /*protected function getComponentStyleGroupProperties($componentId, $styleGroups)
    {
        $componentStyleGroup = ComponentStyleGroup::where('component_id', $componentId)
            ->with('styleGroup:id,name,slug') // Eager load related style group
            ->get();

        $properties = [];

        foreach ($componentStyleGroup as $group) {
            $groupProperties = $this->syncAndFetchComponentStyleGroupProperties($componentId, $group->style_group_id);
            $groupArray = $group->toArray();
            $groupArray['properties'] = $groupProperties;
            $properties[] = $groupArray;
        }

        return $properties;
    }*/
    protected function getComponentStyleGroupProperties($componentId, $styleGroups)
    {
        // Get all style group IDs from the included styleGroups
        $styleGroupIds = $styleGroups->pluck('id')->toArray();

        // Step 1: Load all related ComponentStyleGroups with related styleGroup (eager load)
        $componentStyleGroups = ComponentStyleGroup::with('styleGroup:id,name,slug')
            ->where('component_id', $componentId)
            ->whereIn('style_group_id', $styleGroupIds)
            ->get();

        // Step 2: Batch fetch all valid StyleGroupProperties and join with style_properties
        $validProperties = StyleGroupProperties::whereIn('style_group_id', $styleGroupIds)
            ->join('appfiy_style_properties', 'appfiy_style_properties.id', '=', 'appfiy_style_group_properties.style_property_id')
            ->where('appfiy_style_properties.is_active', 1)
            ->select(
                'appfiy_style_group_properties.style_group_id',
                'appfiy_style_properties.name',
                'appfiy_style_properties.input_type',
                'appfiy_style_properties.value',
                'appfiy_style_properties.default_value'
            )
            ->get()
            ->groupBy('style_group_id');

        // Step 3: Batch fetch all existing ComponentStyleGroupProperties
        $existingProperties = ComponentStyleGroupProperties::where('component_id', $componentId)
            ->whereIn('style_group_id', $styleGroupIds)
            ->get()
            ->groupBy('style_group_id');

        // Final result to return
        $properties = [];

        // Step 4: Loop through each ComponentStyleGroup and sync properties
        foreach ($componentStyleGroups as $group) {
            $groupId = $group->style_group_id;

            // Valid properties (from style group definition)
            $valid = $validProperties[$groupId] ?? collect();

            // Existing properties saved already for this component
            $existingGroupItems = $existingProperties[$groupId] ?? collect();

            // Index existing by 'name'
            $existing = $existingGroupItems->keyBy('name');

            // Extract name lists
            $validNames = $valid->pluck('name')->unique()->toArray();
            $existingNames = $existingGroupItems->pluck('name')->unique()->toArray();

            // Compute differences
            $namesToAdd = array_diff($validNames, $existingNames);
            $namesToDelete = array_diff($existingNames, $validNames);

            // Step 5: Delete obsolete component properties
            if (!empty($namesToDelete)) {
                ComponentStyleGroupProperties::where('component_id', $componentId)
                    ->where('style_group_id', $groupId)
                    ->whereIn('name', $namesToDelete)
                    ->delete();
            }

            // Step 6: Add any missing properties
            foreach ($namesToAdd as $name) {
                $prop = $valid->firstWhere('name', $name);
                if ($prop) {
                    ComponentStyleGroupProperties::create([
                        'component_id' => $componentId,
                        'style_group_id' => $groupId,
                        'name' => $prop->name,
                        'input_type' => $prop->input_type,
                        'value' => $prop->value,
                        'default_value' => $prop->default_value,
                    ]);

                    // Add to in-memory collection too for return
                    $existing->put($name, (object)[
                        'component_id' => $componentId,
                        'style_group_id' => $groupId,
                        'name' => $prop->name,
                        'input_type' => $prop->input_type,
                        'value' => $prop->value,
                        'default_value' => $prop->default_value,
                    ]);
                }
            }

            // Step 7: Finalize return data structure
            $groupArray = $group->toArray();
//            $groupArray['properties'] = $existing->values()->toArray(); // Final properties list
            $groupArray['properties'] = array_map(function ($item) {
                return (array) $item;
            }, $existing->values()->toArray());

            $groupArray['style_group_id'] = $groupId;

            $properties[] = $groupArray;
        }

        return $properties;
    }


    /**
     * Sync and fetch the component's style group properties.
     *
     * @param int $componentId
     * @param int $styleGroupId
     * @return array
     */
    /*protected function syncAndFetchComponentStyleGroupProperties($componentId, $styleGroupId)
    {
        // Fetch valid and existing properties
        $validProperties = StyleGroupProperties::where('style_group_id', $styleGroupId)
            ->where('appfiy_style_properties.is_active', 1)
            ->leftJoin('appfiy_style_properties', 'appfiy_style_properties.id', '=', 'appfiy_style_group_properties.style_property_id')
            ->pluck('appfiy_style_properties.name')
            ->toArray();

        $existingProperties = ComponentStyleGroupProperties::where('component_id', $componentId)
            ->where('style_group_id', $styleGroupId)
            ->pluck('name')
            ->toArray();

        // Calculate missing and obsolete properties
        $deleteProperties = array_diff($existingProperties, $validProperties);
        $newProperties = array_diff($validProperties, $existingProperties);

        // Delete invalid properties
        ComponentStyleGroupProperties::where('component_id', $componentId)
            ->where('style_group_id', $styleGroupId)
            ->whereIn('name', $deleteProperties)
            ->delete();

        // Add new properties
        foreach ($newProperties as $propertyName) {
            $this->createStyleGroupProperty($componentId, $styleGroupId, $propertyName);
        }

        // Fetch all synced properties
        return ComponentStyleGroupProperties::where('component_id', $componentId)
            ->where('style_group_id', $styleGroupId)
            ->get()
            ->toArray();
    }*/

    /**
     * Create a new style group property for the component's style group.
     *
     * @param int $componentId
     * @param int $styleGroupId
     * @param string $propertyName
     * @return bool
     */
    protected function createStyleGroupProperty($componentId, $styleGroupId, $propertyName)
    {
        $property = StyleGroupProperties::where('style_group_id', $styleGroupId)
            ->where('appfiy_style_properties.is_active', 1)
            ->leftJoin('appfiy_style_properties', 'appfiy_style_properties.id', '=', 'appfiy_style_group_properties.style_property_id')
            ->where('appfiy_style_properties.name', $propertyName)
            ->first();

        if ($property) {
            ComponentStyleGroupProperties::create([
                'component_id' => $componentId,
                'style_group_id' => $styleGroupId,
                'name' => $property->name,
                'input_type' => $property->input_type,
                'value' => $property->value,
                'default_value' => $property->default_value,
            ]);
        }
    }

    /**
     * Retrieve checked style group IDs from component properties.
     *
     * @param array $properties
     * @return array
     */
    protected function getCheckedStyleGroupIds($properties)
    {
        return collect($properties)
            ->filter(fn($group) => !empty($group['is_checked']))
            ->pluck('style_group_id')
            ->toArray();
    }

    /**
     * Format scopes into grouped associative array.
     *
     * @param Collection $scopes
     * @return array
     */
    protected function formatScopes($scopes)
    {
        $scopeArray = [];
        foreach ($scopes as $val) {
            $key = $val['is_global'] == 0 ? 'page-scope' : 'global-scope';
            $scopeArray[$key][] = $val->toArray();
        }
        return $scopeArray;
    }

    /**
     * Check if the component is already in use globally or on a theme page.
     *
     * @param int $componentId
     * @return bool
     */
    protected function checkIfComponentIsAlreadyInUse($componentId)
    {
        /*firstly code is*/
        return GlobalConfigComponent::where('component_id', $componentId)->exists() || ThemeComponent::where('component_id', $componentId)->exists();
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
            'name' => 'required|unique:appfiy_component,name,' . $id,
            'component_type_id' => 'required',
            'scope' => 'required',
            'layout_type_id' => 'required',
            'style_group' => 'required',
            'plugin_slug' => 'required',
            'items' => 'nullable|json',
            'dev_data' => 'nullable|json',
            'pagination' => 'nullable|json',
            'filters' => 'nullable|json',
        ], [
            'name.required' => __('messages.enterComponentName'),
            'name.unique' => __('messages.componentNameMustbeUnique'),
            'component_type_id.required' => __('messages.chooseComponentType'),
            'scope.required' => __('messages.ChooseScope'),
            'layout_type_id.required' => __('messages.chooseLayoutType'),
            'style_group.required' => __('messages.chooseStyleGroup'),
            'plugin_slug.required' => __('messages.choosePlugin'),
            'items.json' => __('messages.ItemJsonNotValid'),
            'dev_data.json' => __('messages.DevDataJsonNotValid'),
            'pagination.json' => __('messages.FiltersJsonNotValid'),
            'filters.json' => __('messages.PaginationJsonNotValid'),
        ]);

        $input = $request->all();
        $input['scope'] = json_encode($input['scope']);
        $component = Component::findOrFail($id);

        // Handle Image Upload
        $input['image'] = config('app.is_image_update')
            ? $this->handleFileUpload($request, $component, 'image', 'component-image')
            : $component->image;

        $input['image_url'] = config('app.is_image_update')
            ? $this->handleFileUpload($request, $component, 'image_url', 'component-image')
            : $component->image_url;


        DB::beginTransaction();
        try {
            $component->update($input);

            if (isset($input['style_group'])) {
                // Reset all style groups for the component
                ComponentStyleGroup::where('component_id', $id)
                    ->update(['is_checked' => false]);

                // Update selected style groups
                foreach ($input['style_group'] as $styleGroupId) {
                    ComponentStyleGroup::where('component_id', $id)
                        ->where('style_group_id', $styleGroupId)
                        ->update(['is_checked' => true]);
                }
            }

            DB::commit();
            Session::flash('message', __('messages.updateMessage'));
            return redirect()->route('component_list');
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('danger', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function componentPropertiesInlineUpdate(Request $request)
    {
        $componentPropertiesId = $request->query('component_properties_id');
        $value = $request->query('value');

        $property = ComponentStyleGroupProperties::findOrFail($componentPropertiesId);

        $property->update(['value' => $value]);

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return RedirectResponse
     */

    public function destroy($id)
    {
        // Check if the component is used in Global Configs
        if (GlobalConfigComponent::where('component_id', $id)->exists()) {
            $usedConfigs = GlobalConfigComponent::where('component_id', $id)
                ->join('appfiy_global_config', 'appfiy_global_config.id', '=', 'appfiy_global_config_component.global_config_id')
                ->pluck('appfiy_global_config.name')->unique()->implode(', ');

            Session::flash('validate', __('messages.alreadyUseGlobalConfig') . ' Please check config [ ' . $usedConfigs . ' ]');
            return redirect()->route('component_list');
        }

        // Check if the component is used in Themes
        if (ThemeComponent::where('component_id', $id)->exists()) {
            $usedThemes = ThemeComponent::where('component_id', $id)
                ->join('appfiy_theme', 'appfiy_theme.id', '=', 'appfiy_theme_component.theme_id')
                ->pluck('appfiy_theme.name')->unique()->implode(', ');

            Session::flash('validate', __('messages.alreadyUseTheme') . ' Please check theme [ ' . $usedThemes . ' ]');
            return redirect()->route('component_list');
        }

        // Delete the component
        Component::findOrFail($id)->delete();

        Session::flash('delete', __('messages.deleteMessage'));
        return redirect()->route('component_list');
    }

    public function updatePluginSlug(Request $request)
    {
        $component = Component::findOrFail($request->input('id'));
        $component->update(['plugin_slug' => $request->input('value')]);
        return response()->json(['status' => 'ok'], 200);
    }

}

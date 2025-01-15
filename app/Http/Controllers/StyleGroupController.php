<?php

namespace App\Http\Controllers;

use App\Http\Requests\StyleGroupRequest;
use App\Models\StyleGroup;
use App\Models\StyleGroupProperties;
use App\Models\StyleProperties;
use App\Models\SupportsPlugin;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class StyleGroupController extends Controller
{
    use ValidatesRequests;

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(){
        $styleGroups = StyleGroup::where('is_active',1)->orderByDesc('id')->paginate(20);
        return view('style-group/index',compact('styleGroups'));
    }

    public function create(){
        $pluginDropdown = SupportsPlugin::getPluginDropdown();
        return view('style-group/add',compact('pluginDropdown'));
    }

    public function store(StyleGroupRequest $request)
    {
        $inputs = $request->validated();
        StyleGroup::create($inputs);
        return redirect()->route('style_group_list')->with('message', 'Style group created successfully.');
    }

    public function edit(StyleGroup $styleGroup,$id){
        $pluginDropdown = SupportsPlugin::getPluginDropdown();
        $styleGroup = StyleGroup::find($id);
        return view('style-group/edit',compact('styleGroup','pluginDropdown'));
    }

    public function update(StyleGroupRequest $request,$id){
        $inputs = $request->validated();
        $styleGroup = StyleGroup::find($id);
        $styleGroup->update($inputs);
        return redirect()->route('style_group_list')->with('message', 'Style group updated successfully.');
    }

    public function assignProperties(int $id)
    {
        $styleGroup = StyleGroup::with('groupProperties')->findOrFail($id);
        $styleProperties = StyleProperties::where('is_active', 1)->get();
        $existsPropertiesArray = $styleGroup->groupProperties->pluck('style_property_id')->toArray();

        return view('style-group.assign-properties', compact('styleGroup', 'styleProperties', 'existsPropertiesArray'));
    }


    public function assignPropertiesUpdate(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        // Validate request
        $request->validate([
            'properties_id' => 'required|array',
        ], [
            'properties_id.required' => __('messages.chooseStyleProperties'),
        ]);

        // Find the StyleGroup instance or fail gracefully
        $styleGroup = StyleGroup::findOrFail($id);

        DB::beginTransaction();
        try {
            // Get the new set of property IDs from the request
            $newProperties = $request->input('properties_id');

            // Get existing property IDs related to the StyleGroup
            $existingProperties = $styleGroup->groupProperties->pluck('style_property_id')->toArray();

            // Determine IDs to add and remove
            $propertiesToAdd = array_diff($newProperties, $existingProperties);
            $propertiesToRemove = array_diff($existingProperties, $newProperties);

            // Delete the removed properties
            if (!empty($propertiesToRemove)) {
                StyleGroupProperties::where('style_group_id', $styleGroup->id)
                    ->whereIn('style_property_id', $propertiesToRemove)
                    ->delete();
            }

            // Insert the new properties
            if (!empty($propertiesToAdd)) {
                $insertData = array_map(function ($propertyId) use ($styleGroup) {
                    return [
                        'style_group_id' => $styleGroup->id,
                        'style_property_id' => $propertyId,
                    ];
                }, $propertiesToAdd);

                StyleGroupProperties::insert($insertData);
            }

            DB::commit();

            // Flash a success message and redirect
            Session::flash('message', __('messages.updateMessage'));
            return redirect()->route('style_group_list');
        } catch (\Throwable $e) {
            // Rollback the transaction in case of error
            DB::rollback();

            // Flash an error message and redirect back
            Session::flash('danger', __('An error occurred: ') . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

}

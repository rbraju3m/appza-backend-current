<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Models\ComponentType;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ComponentGroupController extends Controller
{
    use ValidatesRequests;

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $componentTypes = ComponentType::orderBy('id','DESC')->whereNull('deleted_at')->paginate(20);
        return view(' component-group/index',compact('componentTypes'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(){
        return view(' component-group/add');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     */

    public function store(Request $request)
    {
        // Validate incoming request data
        $validatedData = $this->validate($request, $this->validationRules(), $this->validationMessages());

        // Map request data to component model attributes
        $componentData = [
            'group_name' => $validatedData['name'],
            'name' => $validatedData['name'],
            'icon' => $validatedData['icon'],
        ];

        // Create a new ComponentType
        ComponentType::create($componentData);

        // Redirect with success message
        return redirect()
            ->route('component_group_list')
            ->with('success', __('messages.componentGroupCreateSuccess'));
    }

    /**
     * Validation rules for store method.
     */
    protected function validationRules(): array
    {
        return [
            'name' => 'required',
            'icon' => 'required',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Validation messages for store method.
     */
    protected function validationMessages(): array
    {
        return [
            'name.required' => __('messages.componentGroupNameRequired'),
            'icon.required' => __('messages.componentGroupIconRequired'),
        ];
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id){
        $data = ComponentType::find($id);

        return view(' component-group/edit',compact('data'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */

    public function update(Request $request, $id)
    {
        // Validate incoming request data
        $validatedData = $this->validate($request, $this->validationRules(), $this->validationMessages());

        // Find the ComponentType by ID; fail if not found
        $componentType = ComponentType::findOrFail($id);

        // Update the ComponentType with validated data
        $componentType->update([
            'group_name' => $validatedData['name'],
            'name' => $validatedData['name'],
            'icon' => $validatedData['icon'],
            'is_active' => $validatedData['is_active'],
        ]);

        // Redirect back with success message
        return redirect()
            ->route('component_group_list')
            ->with('success', __('messages.componentGroupUpdateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return RedirectResponse
     */


    public function destroy($id)
    {
        // Find the ComponentType by ID or fail gracefully
        $componentType = ComponentType::find($id);

        if (!$componentType) {
            return redirect()
                ->route('component_group_list')
                ->with('error', __('messages.notFoundMessage'));
        }

        $componentExists = Component::where('component_type_id', $id)->exists();
        if ($componentExists){
            return redirect()
                ->route('component_group_list')
                ->with('validate', 'Already exists in component');
        }

        // Soft delete (or delete) the record
        $componentType->delete();

        // Redirect with success message
        return redirect()
            ->route('component_group_list')
            ->with('delete', __('messages.deleteMessage'));
    }

}

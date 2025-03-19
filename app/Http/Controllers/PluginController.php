<?php

namespace App\Http\Controllers;

use App\Http\Requests\PageRequest;
use App\Http\Requests\PluginRequest;
use App\Models\Component;
use App\Models\Page;
use App\Models\Scope;
use App\Models\SupportsPlugin;
use App\Traits\HandlesFileUploads;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class PluginController extends Controller
{
    use ValidatesRequests;
    use HandlesFileUploads;


    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        // Retrieve active plugin entries
        $plugins = SupportsPlugin::where('status', 1)
            ->select('appza_supports_plugin.*')
            ->orderByDesc('id')
            ->paginate(20);

        return view('plugin.index',compact('plugins'));
    }


    /**
     * Show the form for creating a new resource.
     * @return RedirectResponse
     */
    public function create()
    {
        return view('plugin.add');
    }

    public function store(PluginRequest $request)
    {
        $inputs = $request->validated();

        try {
            // Start database transaction
            DB::beginTransaction();

            // Handle Image Upload
            $inputs['image'] = config('app.is_image_update')
                ? $this->handleFileUpload($request, '', 'image', 'plugins')
                : null;

            // Create the plugin
            SupportsPlugin::create($inputs);

            // Commit the transaction if everything is successful
            DB::commit();

            return redirect()->route('plugin_list')->with('success', 'Plugin created successfully.');
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            // Log the error for debugging
            \Log::error('Error creating page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('page_list')->with('error', 'Failed to create the page. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */

    public function edit($id)
    {
        $data = SupportsPlugin::find($id);
        return view('plugin.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */

    public function update(PluginRequest $request, $id)
    {
        // Get validated input
        $inputs = $request->validated();
        $plugin = SupportsPlugin::findOrFail($id);

        $exists = array_key_exists('is_disable', $inputs);
        if (!$exists) {
            $inputs['is_disable'] = false;
        }

        try {
            DB::beginTransaction();

            // Handle Image Upload
            $inputs['image'] = config('app.is_image_update')
                ? $this->handleFileUpload($request, $plugin, 'image', 'plugins')
                : $plugin->image;

            $plugin->update($inputs);

            DB::commit(); // Commit the transaction
            return redirect()->route('plugin_list')->with('success', 'Plugin updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            \Log::error('Error updating page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'inputs' => $inputs,
            ]);
            return redirect()->route('page_list')->with('error', 'Failed to update the page. Please try again.');
        }
    }



    public function destroy($id)
    {
        $page = Page::findOrFail($id);

        $existsComponent = Component::where('plugin_slug', $page->plugin_slug)
            ->where('scope', 'like', '%' . $page->slug . '%')
            ->exists();

        if ($existsComponent){
            return redirect()->route('page_list')->with('validate', 'Page already exists in component.');
        }

        try {
            // Begin a transaction
            DB::beginTransaction();

            // Handle associated scope deletion (soft-delete)
            $scope = Scope::where('plugin_slug', $page->plugin_slug)
                ->where('slug', $page->slug) // Assuming 'slug' is used to link Page and Scope
                ->first();

            if ($scope) {
                $scope->delete(); // Soft delete the scope
            }

            // Soft delete the page itself
            $page->delete();

            // Commit the transaction
            DB::commit();

            return redirect()->route('page_list')->with('success', 'Page and associated scope soft-deleted successfully.');
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Log the error for debugging
            \Log::error('Error during soft delete: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('page_list')->with('error', 'Failed to delete the page. Please try again.');
        }
    }

    public function sortPlugin()
    {
        return view('plugin.sort');
    }
    public function pluginSortData(Request $request)
    {
        $plugins = SupportsPlugin::orderBy('sort_order')->get();
        $str = '<ul id="sortable">';
        if ($plugins != null) {
            foreach ($plugins as $plugin) {
                $str .= '<li id="' . $plugin->id . '"><i class="fa fa-sort"></i>' . $plugin->name . '</li>';
            }
        }
        echo $str . '</ul>';
    }

    public function pluginSortUpdate(Request $request)
    {
        $pluginOrder = $request->input('pluginOrder');
        $pluginOrderArray = explode(',', $pluginOrder);
        $count = 1;
        foreach ($pluginOrderArray as $id) {
            $event = SupportsPlugin::find($id);
            $event->sort_order = $count;
            $event->update();
            $count++;
        }
    }
}

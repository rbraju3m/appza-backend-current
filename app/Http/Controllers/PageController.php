<?php

namespace App\Http\Controllers;

use App\Http\Requests\PageRequest;
use App\Models\Component;
use App\Models\Currency;
use App\Models\GlobalConfig;
use App\Models\GlobalConfigComponent;
use App\Models\Page;
use App\Models\Scope;
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
use Monolog\Handler\IFTTTHandler;

class PageController extends Controller
{
    use ValidatesRequests;
    use HandlesFileUploads;


    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        // Clean up any page entries with a null slug
        Page::whereNull('slug')->first()?->delete();

        // Retrieve active page entries
        $pages = Page::where('is_active', 1)
            ->join('appza_supports_plugin', 'appza_supports_plugin.slug', '=', 'plugin_slug')
            ->select('appfiy_page.*', 'appza_supports_plugin.name as plugin_name')
            ->orderByDesc('id')
            ->paginate(20);

        return view('page.index',compact('pages'));
    }


    /**
     * Show the form for creating a new resource.
     * @return RedirectResponse
     */
    public function create()
    {
        $pluginDropdown = SupportsPlugin::getPluginDropdown();
        return view('page.add', compact('pluginDropdown'));
    }

    public function store(PageRequest $request)
    {
        $inputs = $request->validated();

        // Handle 'persistent_footer_buttons' field
        if ($request->has('persistent_footer_buttons')) {
            $inputs['persistent_footer_buttons'] = '{}';
        }

        // Set default value for 'component_limit' if null
        if (!$inputs['component_limit']) {
            $inputs['component_limit'] = 0;
        }

        try {
            // Start database transaction
            DB::beginTransaction();

            // Create the Page
            $page = Page::create($inputs);

            // Handle scope data creation only if 'page_scope' exists
            if ($request->has('page_scope') && $page) {
                $scopeData = [
                    'name' => $inputs['name'],
                    'slug' => $inputs['slug'],
                    'plugin_slug' => $inputs['plugin_slug'],
                    'is_global' => 0,
                ];

                Scope::create($scopeData);
            }

            // Commit the transaction if everything is successful
            DB::commit();

            return redirect()->route('page_list')->with('success', 'Page created successfully.');
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
        $data = Page::find($id);
        $pluginDropdown = SupportsPlugin::getPluginDropdown();
        $data['page_scope'] = Scope::where('plugin_slug', $data->plugin_slug)
            ->where('slug', $data['slug'])
            ->first()?->exists ?? false;
        return view('page.edit', compact('data', 'pluginDropdown'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */

    public function update(PageRequest $request, $id)
    {
        // Get validated input
        $inputs = $request->validated();
        $page = Page::findOrFail($id); // Use findOrFail for better error handling

        // Handle 'persistent_footer_buttons'
        if ($request->has('persistent_footer_buttons')) {
            $inputs['persistent_footer_buttons'] = '{}';
        }else{
            $inputs['persistent_footer_buttons'] = null;
        }

        // Set default value for 'component_limit' if null
        if (!$inputs['component_limit']) {
            $inputs['component_limit'] = 0;
        }

        try {
            DB::beginTransaction(); // Start transaction

            // Update the Page
            $page->update($inputs);

            // Handle additional scope logic
            if ($request->filled('page_scope')) {
                // Look for an existing scope
                $scope = Scope::withTrashed() // Include soft-deleted rows in search
                ->where('plugin_slug', $page->plugin_slug)
                    ->where('slug', $inputs['slug'])
                    ->first();

                $scopeData = [
                    'name' => $inputs['name'],
                    'slug' => $inputs['slug'],
                    'plugin_slug' => $inputs['plugin_slug'],
                    'is_global' => 0,
                ];

                if (!$scope) {
                    // Create a new scope if it doesn't exist
                    Scope::create($scopeData);
                } elseif ($scope->trashed()) {
                    // Restore if the scope was soft-deleted
                    $scope->restore();
                    $scope->update($scopeData); // Update additional fields if needed
                } else {
                    // Update an existing scope
                    $scope->update($scopeData);
                }
            } else {
                // Handle soft delete only if the scope exists
                $scope = Scope::where('plugin_slug', $page->plugin_slug)
                    ->where('slug', $inputs['slug'])
                    ->first();

                if ($scope) {
                    $scope->delete(); // Soft delete the scope
                }
            }

            DB::commit(); // Commit the transaction
            return redirect()->route('page_list')->with('success', 'Page updated successfully.');
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
}

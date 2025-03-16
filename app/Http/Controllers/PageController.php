<?php

namespace App\Http\Controllers;

use App\Http\Requests\PageRequest;
use App\Models\Component;
use App\Models\Page;
use App\Models\Scope;
use App\Models\SupportsPlugin;
use App\Models\ThemeComponent;
use App\Models\ThemeComponentStyle;
use App\Models\ThemePage;
use App\Traits\HandlesFileUploads;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

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
    public function scopeIndex()
    {
        // Retrieve active page entries
        $scopes = Scope::orderByDesc('id')
            ->join('appza_supports_plugin', 'appza_supports_plugin.slug', '=', 'appfiy_scope.plugin_slug')
            ->select('appfiy_scope.*', 'appza_supports_plugin.name as plugin_name')
            ->paginate(20);

        return view('page.scope-index',compact('scopes'));
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

        try {
            // Start database transaction
            DB::beginTransaction();

            // Create the Page
            $page = Page::create($inputs);

            // Handle scope data creation only if 'page_scope' exists
            if ($page) {
                $scopeData = [
                    'name' => $inputs['name'],
                    'slug' => $inputs['slug'],
                    'page_id' => $page->id,
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
        $inputs['persistent_footer_buttons'] = $request->has('persistent_footer_buttons') ? '{}' : null;

        // Set default value for 'component_limit' if null
        $inputs['component_limit'] = $inputs['component_limit'] ?? null;

        $scope = Scope::where('page_id', $id)->firstOrFail();

        try {
            DB::beginTransaction(); // Start transaction

            // Check if slug needs to be updated
            if (isset($inputs['slug']) && $page->slug === $inputs['slug']) {
                // Update the Page and Scope (no slug changes)
                $page->update($inputs);
                $scope->update($inputs);
            } else {
                // Update components with old slug in the scope field if slug changes
                $scopeWiseComponent = Component::where('scope', 'LIKE', '%' . $scope->slug . '%')
                    ->where('plugin_slug', $scope->plugin_slug) // Direct comparison
                    ->get(); // Retrieve matching components

                if ($scopeWiseComponent->isNotEmpty()) {
                    foreach ($scopeWiseComponent as $component) {
                        if (isset($component['scope'])) {
                            $scopeArray = json_decode($component['scope'], true); // Decode 'scope' JSON

                            if (is_array($scopeArray)) {
                                // Replace the slug with the new slug
                                $updatedScope = array_map(function ($item) use ($scope, $inputs) {
                                    return $item === $scope->slug ? $inputs['slug'] : $item;
                                }, $scopeArray);

                                // Encode back to JSON and update
                                $component->update([
                                    'scope' => json_encode($updatedScope),
                                ]);
                            }
                        }
                    }
                }

                // Update Scope and Page with new slug
                $scope->update($inputs);
                $page->update($inputs);
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


    public function forceDestroy($id)
    {
        try {
            // Begin a transaction
            DB::beginTransaction();

            $page = Page::findOrFail($id);

            // Update the `scope` field in Component where necessary
            Component::where('plugin_slug', $page->plugin_slug)
                ->where('scope', 'like', '%' . $page->slug . '%')
                ->get()
                ->each(function ($component) use ($page) {
                    $scope = json_decode($component->scope, true);

                    if (is_array($scope)) {
                        $scope = array_values(array_diff($scope, [$page->slug])); // Remove $page->slug from scope
                    }

                    $component->scope = empty($scope) ? null : json_encode($scope);
                    $component->save();
                });

            // Retrieve related ThemePage and ThemeComponent IDs
            $getThemePages = ThemePage::where('page_id', $id)->get();
            $themePageIds = $getThemePages->pluck('id');

            $getThemeComponents = ThemeComponent::whereIn('theme_page_id', $themePageIds)->get();
            $themeComponentIds = $getThemeComponents->pluck('id');

            // Delete ThemeComponentStyles
            ThemeComponentStyle::whereIn('theme_component_id', $themeComponentIds)->delete();

            // Delete ThemeComponents
            ThemeComponent::whereIn('id', $themeComponentIds)->delete();

            // Delete ThemePages
            ThemePage::whereIn('id', $themePageIds)->delete();

            // Handle associated scope soft deletion
            $scope = Scope::where('plugin_slug', $page->plugin_slug)
                ->where('slug', $page->slug)
                ->first();

            if ($scope) {
                $scope->delete(); // Soft delete the scope
            }

            // Finally, delete the Page
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

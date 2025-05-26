<?php

namespace App\Http\Controllers;

use App\Http\Requests\PluginRequest;
use App\Jobs\DeleteBuildDir;
use App\Jobs\ProcessBuild;
use App\Models\BuildOrder;
use App\Models\Component;
use App\Models\Page;
use App\Models\Scope;
use App\Models\SupportsPlugin;
use App\Traits\HandlesFileUploads;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class BuildOrderController extends Controller
{
    use ValidatesRequests;
    use HandlesFileUploads;


    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request): Renderable
    {
        // Retrieve active plugin entries
        $buildOrders = BuildOrder::orderByDesc('id')->paginate(20);

        return view('build-order.index',compact('buildOrders'));
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


    /**
     * Delete build directory for a specific build order.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function deleteBuildDir(Request $request, int $id): JsonResponse
    {
        $buildOrder = BuildOrder::findOrFail($id);

        // Check if the build dir is already deleted
        if ($buildOrder->is_build_dir_delete) {
            return response()->json([
                'message' => 'Build directory is already deleted.',
                'status' => 'success'
            ]);
        }

        // Dispatch the job to delete the build directory
        dispatch(new DeleteBuildDir($buildOrder->id));

        return response()->json([
            'message' => 'Build directory delete processing .....',
            'status' => 'success'
        ]);
    }

}

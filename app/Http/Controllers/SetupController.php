<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetupRequest;
use App\Models\Setup;
use App\Traits\HandlesFileUploads;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Log;

class SetupController extends Controller
{
    use ValidatesRequests;


    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request): Renderable
    {
        // Retrieve active plugin entries
        $setups = Setup::orderByDesc('id')->where('is_active', 1)->paginate(20);

        return view('setup.index', compact('setups'));
    }

    /**
     * Show the form for creating a new resource.
     * @return RedirectResponse
     */
    public function create()
    {
        return view('setup.add');
    }

    public function store(SetupRequest $request)
    {
        $inputs = $request->validated();

        try {
            // Start database transaction
            DB::beginTransaction();

            // Create the setup
            Setup::create($inputs);

            // Commit the transaction if everything is successful
            DB::commit();

            return redirect()->route('setup_list')->with('success', 'Setup created successfully.');
        } catch (Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            // Log the error for debugging
            Log::error('Error creating setup: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('setup_list')->with('error', 'Failed to create the setup. Please try again.');
        }
    }

    public function edit($id)
    {
        $data = Setup::find($id);
        return view('setup.edit', compact('data'));
    }

    public function update(SetupRequest $request, $id)
    {
        // Get validated input
        $inputs = $request->validated();
        $setup = Setup::findOrFail($id);

        $exists = array_key_exists('is_active', $inputs);
        if (!$exists) {
            $inputs['is_active'] = false;
        }

        try {
            DB::beginTransaction();

            $setup->update($inputs);

            DB::commit(); // Commit the transaction
            return redirect()->route('setup_list')->with('success', 'Setup updated successfully.');
        } catch (Exception $e) {
            DB::rollBack(); // Rollback on error
            Log::error('Error updating page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'inputs' => $inputs,
            ]);
            return redirect()->route('setup_list')->with('error', 'Failed to update the setup. Please try again.');
        }
    }


    public function destroy($id)
    {
        $setup = Setup::findOrFail($id);

        try {
            // Begin a transaction
            DB::beginTransaction();
            $setup->delete();
            // Commit the transaction
            DB::commit();

            return redirect()->route('setup_list')->with('success', 'Setup deleted successfully.');
        } catch (Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Log the error for debugging
            Log::error('Error during soft delete: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('setup_list')->with('error', 'Failed to delete the setup. Please try again.');
        }
    }

}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\LicenseLogicRequest;
use App\Http\Requests\PageRequest;
use App\Models\Component;
use App\Models\LicenseLogic;
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

class LicenseLogicController extends Controller
{
    use ValidatesRequests;
    use HandlesFileUploads;


    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $events = LicenseLogic::distinct('event')->pluck('event');
//        $events = ['expiration', 'grace', 'invalid'];

        $licenseLogicsByEvent = [];
        foreach ($events as $event) {
            $licenseLogicsByEvent[$event] = LicenseLogic::where('is_active', 1)
                ->where('event', $event)
                ->select(['id','name','slug','event','direction','from_days','to_days'])
                ->orderByDesc('id')
                ->paginate(20, ['*'], $event . '_page'); // page name for pagination
        }

        return view('license-logic.index', compact('licenseLogicsByEvent', 'events'));
    }

    /**
     * Show the form for creating a new resource.
     * @return RedirectResponse
     */
    public function create()
    {
        $eventDropdown = [
            'expiration' => 'Expiration',
            'grace' => 'Grace',
            'invalid' => 'Invalid'
        ];
        $directionDropdown = [
            'equal' => 'Equal',
            'before' => 'Before',
            'after' => 'After'
        ];

        $fromDaysDropdown = [];
        $toDaysDropdown = [];
        for ($day = 0; $day <= 31; $day++) {
            $fromDaysDropdown[$day] = $day;
            $toDaysDropdown[$day] = $day;
        }
        return view('license-logic.add', compact('eventDropdown','directionDropdown','fromDaysDropdown','toDaysDropdown'));
    }

    /*public function store(LicenseLogicRequest $request)
    {
        $inputs = $request->validated();

        try {
            // Start database transaction
            DB::beginTransaction();

            if (in_array($inputs['event'], ['expiration','grace'])) {
                $inputs['event_combination'] = $inputs['event'].'_'.$inputs['direction'].'_'.$inputs['from_days'].'_'.$inputs['to_days'];
            } else {
                $inputs['event_combination'] = null;
                $inputs['direction'] = null;
                $inputs['from_days'] = null;
                $inputs['to_days'] = null;
            }


            LicenseLogic::create($inputs);

            // Commit the transaction if everything is successful
            DB::commit();

            return redirect()->route('license_logic_list')->with('success', 'Matrix created successfully.');
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            // Log the error for debugging
            \Log::error('Error creating page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('license_logic_list')->with('success', 'Failed to create the page. Please try again.');
        }
    }*/

    public function store(LicenseLogicRequest $request)
    {
        $inputs = $request->validated();

        try {
            DB::beginTransaction();

            LicenseLogic::create($inputs);

            DB::commit();

            return redirect()->route('license_logic_list')
                ->with('success', 'Matrix created successfully.')
                ->with('active_tab', $inputs['event']);
            ;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating logic: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors('Failed to create the matrix. Please try again.');
        }
    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */

    public function edit($id)
    {
        $licenseLogic = LicenseLogic::findOrFail($id);
        $eventDropdown = [
            'expiration' => 'Expiration',
            'grace' => 'Grace',
            'invalid' => 'Invalid'
        ];
        $directionDropdown = [
            'equal' => 'Equal',
            'before' => 'Before',
            'after' => 'After'
        ];

        $fromDaysDropdown = [];
        $toDaysDropdown = [];
        for ($day = 0; $day <= 31; $day++) {
            $fromDaysDropdown[$day] = $day;
            $toDaysDropdown[$day] = $day;
        }
        return view('license-logic.edit', compact('eventDropdown','directionDropdown','fromDaysDropdown','toDaysDropdown','licenseLogic'));
    }

    public function update(LicenseLogicRequest $request, $id)
    {
        $licenseLogic = LicenseLogic::findOrFail($id);

        $inputs = $request->validated();

        try {
            // Start database transaction
            DB::beginTransaction();

            if (in_array($inputs['event'], ['expiration','grace'])) {
                $inputs['event_combination'] = $inputs['event'].'_'.$inputs['direction'].'_'.$inputs['from_days'].'_'.$inputs['to_days'];
            } else {
                $inputs['event_combination'] = null;
                $inputs['direction'] = null;
                $inputs['from_days'] = null;
                $inputs['to_days'] = null;
            }

            $licenseLogic->update($inputs);

            // Commit the transaction if everything is successful
            DB::commit();

            return redirect()->route('license_logic_list')
                ->with('success', 'Matrix update successfully.')
                ->with('active_tab', $inputs['event']);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            // Log the error for debugging
            \Log::error('Error creating page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('license_logic_list')->with('success', 'Failed to create the page. Please try again.');
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

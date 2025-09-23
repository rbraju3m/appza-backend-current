<?php

namespace App\Http\Controllers;

use App\Http\Requests\LicenseLogicRequest;
use App\Http\Requests\LicenseMessageRequest;
use App\Http\Requests\PageRequest;
use App\Models\Component;
use App\Models\FluentInfo;
use App\Models\LicenseLogic;
use App\Models\LicenseMessage;
use App\Models\LicenseMessageDetails;
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

class LicenseMessageController extends Controller
{
    use ValidatesRequests;
    use HandlesFileUploads;


    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function index(Request $request)
    {
        $products = FluentInfo::getProductTab(); // your tabs
        $activeTab = $request->query('tab') ?: ($products->first()->product_slug ?? null);
        $search = $request->query('search');
        $licenseType = $request->query('license_type');

        $licenseMessages = [];

        foreach ($products as $product) {
            $query = LicenseMessage::with([
                'product:id,product_name,product_slug',
                'logic:id,name,slug',
                'message_details:id,message_id,type,message'
            ])->where('is_active', 1)
                ->where('product_id', $product->id);

            // Apply search only if tab matches
            if ($activeTab === $product->product_slug) {
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('license_type', 'like', "%$search%")
                            ->orWhereHas('logic', function($l) use ($search) {
                                $l->where('name', 'like', "%$search%");
                            });
                    });
                }

                if ($licenseType) {
                    $query->where('license_type', $licenseType);
                }
            }

            $licenseMessages[$product->product_slug] = $query->orderByDesc('id')
                ->paginate(10, ['*'], $product->product_slug . '_page')
                ->appends($request->query()); // preserve search, license_type, tab
        }

        return view('license-message.index', compact('licenseMessages', 'products', 'activeTab', 'search', 'licenseType'));
    }


    /**
     * Show the form for creating a new resource.
     * @return RedirectResponse
     */
    public function create()
    {
        $products = FluentInfo::getProductDropdown();
        $matrixs = LicenseLogic::pluck('name', 'id')->all();

        $licenseType = [
            'free_trial' => 'Free Trail',
            'premium' => 'Premium',
        ];

        return view('license-message.add', compact('products','matrixs','licenseType'));
    }

    public function store(LicenseMessageRequest $request)
    {
        $inputs = $request->validated();

        try {
            // Start database transaction
            DB::beginTransaction();

            $licenseMessage = LicenseMessage::create($inputs);

            $licenseMessage->message_details()->createMany([
                ['type' => 'user',    'message' => $inputs['message_user']],
                ['type' => 'admin',   'message' => $inputs['message_admin']],
                ['type' => 'special', 'message' => $inputs['message_special']],
            ]);


            // Commit the transaction if everything is successful
            DB::commit();

            return redirect()->route('license_message_list')->with('success', 'Message created successfully.');
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            // Log the error for debugging
            \Log::error('Error creating page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('license_message_list')->with('error', 'Failed to create the page. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */

    public function edit($id)
    {
        // Fetch the LicenseMessage
        $licenseMessage = LicenseMessage::with('message_details')->findOrFail($id);

        // Prepare dropdowns
        $products = FluentInfo::getProductDropdown();
        $matrixs = LicenseLogic::pluck('name', 'id')->all();

        $licenseType = [
            'free_trial' => 'Free Trial',
            'premium' => 'Premium',
        ];

        // Map messages by type for easy access in Blade
        $messagesByType = collect($licenseMessage->message_details->toArray())
            ->keyBy('type')
            ->map(fn($m) => $m['message'])
            ->all();

        // Add properties to the model for easier form binding
        $licenseMessage->user_message = $messagesByType['user'] ?? null;
        $licenseMessage->admin_message = $messagesByType['admin'] ?? null;
        $licenseMessage->special_message = $messagesByType['special'] ?? null;

        return view('license-message.edit', compact('licenseMessage', 'products', 'matrixs', 'licenseType'));
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */

    public function update(LicenseMessageRequest $request, $id)
    {
        $inputs = $request->validated();

        try {
            DB::beginTransaction();

            $licenseMessage = LicenseMessage::findOrFail($id);

            // Update LicenseMessage main fields
            $licenseMessage->update($inputs);

            // Update or create message_details
            $details = [
                'user' => $inputs['message_user'] ?? null,
                'admin' => $inputs['message_admin'] ?? null,
                'special' => $inputs['message_special'] ?? null,
            ];

            foreach ($details as $type => $message) {
                if ($message !== null) {
                    $licenseMessage->message_details()->updateOrCreate(
                        ['type' => $type],
                        ['message' => $message]
                    );
                }
            }

            DB::commit();

            return redirect()->route('license_message_list',['tab' => $licenseMessage->product->product_slug])
                ->with('success', 'Message updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error updating message: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to update the message. Please try again.');
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

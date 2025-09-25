<?php

namespace App\Http\Controllers;

use App\Http\Requests\PageRequest;
use App\Http\Requests\ProductRequest;
use App\Models\Component;
use App\Models\FluentInfo;
use App\Models\FreeTrial;
use App\Models\Lead;
use App\Models\Page;
use App\Models\Scope;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Log;

class ProductController extends Controller
{
    use ValidatesRequests;


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Key = slug, Value = name
        $products = FluentInfo::where('is_active',1)->orderBy('id','DESC')->paginate(20);


        return view('product.index', compact( 'products'));
    }

    public function edit($id){
        $product = FluentInfo::find($id);
        return view('product.edit', compact( 'product'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */

    public function update(ProductRequest $request, $id)
    {
        // Get validated input
        $inputs = $request->validated();

        // Normalize api_url to ensure only one trailing slash
        $inputs['api_url'] = rtrim($inputs['api_url'], '/') . '/';

        DB::beginTransaction();
        try {
            $product = FluentInfo::findOrFail($id);

            $product->update($inputs);

            DB::commit();

            return redirect()
                ->route('product_list')
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('product_list')
                ->with('error', 'Failed to update product: ' . $e->getMessage());
        }
    }
}

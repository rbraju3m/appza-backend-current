<?php

namespace App\Http\Controllers;

use App\Models\LayoutType;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;

class LayoutTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $layoutTypes = LayoutType::where('is_active',1)->orderByDesc('id')->paginate(20);
        return view('layout-type/index',compact('layoutTypes'));
    }
}

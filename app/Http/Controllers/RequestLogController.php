<?php

namespace App\Http\Controllers;

use App\Jobs\DeleteBuildDir;
use App\Models\BuildOrder;
use App\Models\Component;
use App\Models\Page;
use App\Models\RequestLog;
use App\Models\Scope;
use App\Traits\HandlesFileUploads;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class RequestLogController extends Controller
{
    use ValidatesRequests;
    use HandlesFileUploads;


    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        /*$filters = $request->query('search');

        $requestLogs = RequestLog::orderByDesc('id');

        if ($filters) {
            $requestLogs = $requestLogs->where(function($query) use ($filters) {
//                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(request_data, '$')) LIKE ?", ["%{$filters}%"])
//                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(response_data, '$')) LIKE ?", ["%{$filters}%"])
                $query->whereRaw("MATCH(request_text, response_text) AGAINST(? IN NATURAL LANGUAGE MODE)", [$filters])

//                $query->whereRaw("MATCH(request_data, response_data) AGAINST(? IN NATURAL LANGUAGE MODE)", [$filters])
                    ->orWhere('ip_address', 'like', "%{$filters}%");
            });
        }

        $requestLogs = $requestLogs->paginate(20)->withQueryString();*/


        $filters = trim($request->query('search', ''));

        $requestLogs = RequestLog::orderByDesc('id');

        if ($filters !== '') {
            $requestLogs = $requestLogs->where(function($query) use ($filters) {
                // Full string exact match (case-insensitive substring match)
                $query->where('request_text', 'like', "%{$filters}%")
                    ->orWhere('response_text', 'like', "%{$filters}%")
                    ->orWhere('ip_address', 'like', "%{$filters}%");
            });
        }

        $requestLogs = $requestLogs->paginate(20)->withQueryString();

        return view('request-log.index', compact('requestLogs'));
    }

}

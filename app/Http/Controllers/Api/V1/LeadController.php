<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\LeadResource;
use App\Models\Lead;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class LeadController extends Controller
{
    use ValidatesRequests;
    protected $authorization;
    protected $domain;
    protected $pluginName;

    public function __construct(Request $request){
        $data = Lead::checkAuthorization($request);
        $this->authorization = $data['auth_type'] ?? false;
        $this->domain = $data['domain'] ?? '';
        $this->pluginName = $data['plugin_name'] ?? '';
    }

    public function store(Request $request, $plugin)
    {
        // Perform validation
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'domain' => 'required|url',
        ], [
            'first_name.required' => __('customers::messages.firstNameRequired'),
            'last_name.required' => __('customers::messages.lastNameRequired'),
            'email.required' => __('customers::messages.emailRequired'),
            'email.email' => __('customers::messages.emailInvalid'),
            'domain.required' => __('customers::messages.domainRequired'),
            'domain.url' => __('customers::messages.domainInvalid'),
        ]);

        // Prepare input data
        $input = $request->only('first_name', 'last_name', 'email', 'domain', 'note');
        $input['plugin_name'] = $plugin;
        $input['appza_hash'] = Hash::make($input['email'] . $input['domain']);

        // Create Lead record
        $data = Lead::create($input);

        // Return the resource-based response with JSON_UNESCAPED_SLASHES
        return response()->json(
            (new LeadResource($data))->resolve(),
            Response::HTTP_OK,
            [],
            JSON_UNESCAPED_SLASHES
        );
    }

}

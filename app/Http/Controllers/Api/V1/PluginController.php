<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\PluginRequest;
use App\Http\Resources\PluginResource;
use App\Http\Resources\ThemeResource;
use App\Models\Lead;
use App\Models\ProductAddon;
use App\Models\SupportsPlugin;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class PluginController extends Controller
{
    protected $authorization;
    protected $domain;
    protected $pluginName;
    protected $email;

    public function __construct(Request $request)
    {
        $data = Lead::checkAuthorization($request);
        $this->authorization = ($data && $data['auth_type']) ? $data['auth_type'] : false;
        $this->domain = $data['domain'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->pluginName = $data['plugin_name'] ?? '';
    }

    public function allPlugin(Request $request)
    {
        $isHashAuthorization = config('app.is_hash_authorization');
        if ($isHashAuthorization && !$this->authorization) {
            return $this->buildJsonResponse(
                Response::HTTP_UNAUTHORIZED,
                $request,
                'Unauthorized'
            );
        }

        try {
            // Fetch active themes with their photo gallery
            $plugins = SupportsPlugin::active()
                ->orderby('sort_order', 'asc')
                ->get();

            // Check if themes exist
            if ($plugins->isEmpty()) {
                return $this->buildJsonResponse(
                    Response::HTTP_NOT_FOUND,
                    $request,
                    'Data Not Found'
                );
            }

            // Use the ThemeResource to transform the data
            return PluginResource::collection($plugins)
                ->additional([
                    'status' => Response::HTTP_OK,
                    'url' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'message' => 'Data Found',
                ])
                ->response()
                ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
        } catch (Exception $ex) {
            return response(['message' => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkDisablePlugin(Request $request)
    {
        $isHashAuthorization = config('app.is_hash_authorization');
        if ($isHashAuthorization && !$this->authorization) {
            return $this->buildJsonResponse(
                Response::HTTP_UNAUTHORIZED,
                $request,
                'Unauthorized'
            );
        }

        try {
            // Fetch active plugin with their photo gallery
            $plugin = SupportsPlugin::where('slug',$request->get('plugin_slug'))->first();

            // Check if plugin exist
            if (!$plugin) {
                return $this->buildJsonResponse(
                    Response::HTTP_NOT_FOUND,
                    $request,
                    'Data Not Found'
                );
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'is_disable' => $plugin->is_disable?true:false,
            ], Response::HTTP_OK);

        } catch (Exception $ex) {
            return response(['message' => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function buildJsonResponse($status, $request, $message, $data = null)
    {
        $response = [
            'status' => $status,
            'url' => $request->getUri(),
            'method' => $request->getMethod(),
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $status, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    public function pluginVersionCheck(Request $request)
    {
        // Create validator
        $validator = Validator::make($request->all(), [
            'installed_version' => 'required',
            'plugin_slug'       => 'required',
        ]);

        // Check for validation errors
        if ($validator->fails()) {
            return $this->jsonResponse(
                Response::HTTP_BAD_REQUEST,
                'Validation Error',
                ['errors' => $validator->errors()]
            );
        }

        // Get validated data as array
        $validated = $validator->validated();

        // Find the plugin
        $findPluginJson = ProductAddon::where('addon_slug', $validated['plugin_slug'])->first();

        if (!$findPluginJson) {
            return $this->jsonResponse(Response::HTTP_NOT_FOUND, 'Plugin not found');
        }

        $pluginData = json_decode($findPluginJson->addon_json_info, true);

        if (!$this->authorization) {
            $pluginData['version'] = $validated['installed_version'];
            $pluginData['download_url'] = null;
        }
        return response()->json($pluginData);
    }

    protected function jsonResponse(int $statusCode, string $message, array $additionalData = []): JsonResponse
    {
        return response()->json(array_merge([
            'status' => $statusCode,
            'message' => $message,
        ], $additionalData), $statusCode);
    }


}

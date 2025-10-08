<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\FreeTrialRequest;
use App\Http\Resources\FreeTrialResource;
use App\Models\FluentInfo;
use App\Models\FluentLicenseInfo;
use App\Models\FreeTrial;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FirebaseController extends Controller
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

    protected function jsonResponse(int $statusCode, string $message, array $additionalData = []): JsonResponse
    {
        return response()->json(array_merge([
            'status' => $statusCode,
            'message' => $message,
        ], $additionalData), $statusCode);
    }
    public function credential(Request $request, $product)
    {
        if (!$this->authorization) {
            return $this->jsonResponse(Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        if ($product === 'appza' || $product === 'lazy_task' || $product === 'fcom_mobile') {
            $findProduct = FluentInfo::where('product_slug', $product)->first();

            $findProductFirebaseJson = $findProduct?->temp_firebase_json;
            return $this->jsonResponse(Response::HTTP_OK, 'success', ['data' => json_decode($findProductFirebaseJson)]);
        }

        return $this->jsonResponse(Response::HTTP_BAD_REQUEST, 'Bad Request');

    }


}

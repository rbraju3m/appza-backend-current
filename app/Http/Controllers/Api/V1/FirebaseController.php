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
            $additionalData = [
                "client_email" => "firebase-adminsdk-iu4tv@lazytasks-ee0da.iam.gserviceaccount.com",
                "private_key" => "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCyaI91fwBsrUHx\nfHkIKAxT6Ul+5uzXYQQvUo4x6RRA5sTsrSKnK8NM4wiy6pvYR3/U4DqfsnveQrd1\najGKHPKMieZqMLE32UYQ972FFR5spZaoAARhabxkzy2X9QO7ThcZRKWv5g1h6lbq\n1XdECR+UrA4vcHWBN4goGdZu4v9X4JqmBuOOs6ygIHdVsBjYr/1b1RpN/eLLLS89\nYnMWAX8UdVm2gmE5dxr3GLXvws4L2epidhzceLtY6h4McfxfRBUt9+35IZNbhz/Z\nAuhdJ95242afOHA9sbEpQ1JrdwIgnir4v/KQmbW1x819Dxs+rWrdjhfD7wXKE+7h\nhA9i84MzAgMBAAECggEAGIEfDzLZ8pNbhtkjYJjrlIzq0NjT+AM1yXAr4rQSWZW4\nv49KPq7WnNVkKPrrW+n6J1mDA9NGizEbtK9YzZELSX1ZXgWBGdwocQUuDTzo2GBo\nEHwsHdshirEgIIqBMDVaG9jfbwkIloXS2V9nibs9ELUPH7nxEcX4WBWwa7uW+dE/\nXqC8RdvY+Bfy+/11cDbQ3+gJmH0sbDEf26Kujq96/daId6TZumJQxJt55FNQNJ+A\nz/6G6Xlp9HfkRDo0h3tDmipqmJygngJ9af/NQIY4BmPp4wxI0spuPh8YymybMoz3\nPC2nR8rTa7OOWTyf15lhTJc6CPse9RGO2w01/8F0pQKBgQDmDQq7nfy7BntKsRd6\njTECwAu78j0XgYFut9N4Th25rS/vOntzigs/n7dTb+Jxp6U0V+0f5GlAeN74SsrN\nl+FcXxEAQduMom2C778B/SQnA1mQ/Ns+ujIf4RTeDa6l6K+bHHcILXAKV5zOa+hP\nX1ojpYxW6fYQatY/qUfp6NbqdwKBgQDGiEmzHxxOyNfdro068znleLVuqvbYpOAt\n4ROLukB3HTMBvGtzxZMZacYh54TiOwFLQAjl/VruukesulhIoA800lB1++RmhK7g\nvtsB0waH3lwU1A67X3yGBLLYCF3vdeldE8m/pN6jsDeX/kEO5fzzzlL83heBJdXA\n8t5dcoFgJQKBgG7tYPyAvKmuAWtNoyWbyUMrOT1CHAUmlDO//f8no5uxj8iJ6ZcX\nvD7Mk8huzcDB9p4bu6JCMCI/ZjxRTCMAllFFIdx+5Q+WDroxQmgCGRmauuh3lHxV\nqe/HR5me/VTQs0RW4GqYBktmXZ0HWThUoRFJNTd/jv/xlCeUR7HZbTAXAoGAIUSZ\nRQDDl5gkRCuJ0wULAJ73mDHh52/JeSasRc/SGaO09meCggXrnmiRIQFQzTAiCWAT\nnaaU5Egm1pTrUlAv/CP6A6tQHLXDMDoLLQUVpRLnzz2xskhP740+AuF0DDpv/n7g\nYdLY1AklZ7zdXgfAGYLLeUAmJCgY55pLFNGpSFkCgYEAq0C+dhQWtKb2+VmrdAC9\nSbNpTjSsSmiKTwLqJKwX9w54zqFJGukrilqhzWpZS4egWt01WaTmURt9k1lwGBf8\n5Xq2qWoojHUSyfy58NTjdnHx+cw/oci36mESKGMC72sgUbMcgPCtAKc8mL8zcaEb\nAqNZGBZNvZlq8TEtjQrGzvE=\n-----END PRIVATE KEY-----\n",
            ];

            return $this->jsonResponse(Response::HTTP_OK, 'success', ['data' => $additionalData]);
        }

        return $this->jsonResponse(Response::HTTP_BAD_REQUEST, 'Bad Request');

    }


}

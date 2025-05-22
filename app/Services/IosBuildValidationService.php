<?php
namespace App\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;

class IosBuildValidationService
{
    public function iosBuildProcessValidation($findSiteUrl)
    {
        $token = $this->generateJwt($findSiteUrl);

        if ($token['success'] === false) {
            return $token;
        }

        $bundle = $this->bundleExists($findSiteUrl, $token['token']);

        if ($bundle === 'Unauthorized') {
            return [
                'success' => false,
                'status' => Response::HTTP_UNAUTHORIZED,
                'message' => 'Unauthorized: Invalid token or credentials.',
            ];
        }

        if (!$bundle) {
            $bundleCreation = $this->createBundle($token['token'], $findSiteUrl->package_name, $findSiteUrl->app_name);

            if ($bundleCreation['success'] === false) {
                return $bundleCreation;
            }

            return [
                'success' => true,
                'status' => Response::HTTP_OK,
                'message' => 'IOS Resource information is valid.',
                'data' => $bundleCreation['data']
            ];
        }

        return [
            'success' => true,
            'status' => Response::HTTP_OK,
            'message' => 'IOS Resource information is valid.',
            'data' => $bundle,
        ];
    }

    public function iosBuildProcessValidation2($findSiteUrl)
    {
        $token = $this->generateJwt($findSiteUrl);

        if ($token['success'] === false) {
            return $token;
        }

        $appName = $this->checkAppExists($token['token'], $findSiteUrl->package_name);

        if ($appName) {
            if ($cert = $this->getDistributionCertificate($token['token'])) {
                $this->apiRequest('DELETE', "certificates/$cert", $token['token']);
            }

            $profileName = "match AppStore " . $findSiteUrl->package_name;
            if ($profile = $this->checkProfileExists($token['token'], $profileName)) {
                $this->apiRequest('DELETE', "profiles/$profile", $token['token']);
            }

            return [
                'success' => true,
                'status' => Response::HTTP_OK,
                'message' => 'Your ios app name has been taken from your app store.',
                'app_name' => $appName
            ];
        }

        return [
            'success' => false,
            'status' => Response::HTTP_NOT_FOUND,
            'message' => "We didn't found any app for {$findSiteUrl->package_name} Bundle ID. Please create an app & try again.",
            'app_name' => null
        ];
    }

    private function generateJwt($findSiteUrl)
    {
        $filePath = public_path('/upload/build-apk/p8file/' . $findSiteUrl->ios_p8_file_content);

        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'The .p8 file was not found',
            ];
        }

        $privateKey = file_get_contents($filePath);
        if (!$privateKey) {
            return [
                'success' => false,
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Failed to read the private key from the .p8 file.',
            ];
        }

        $payload = [
            'iss' => $findSiteUrl->ios_issuer_id,
            'iat' => time(),
            'exp' => time() + (20 * 60),
            'aud' => 'appstoreconnect-v1',
        ];

        try {
            $jwt = JWT::encode($payload, $privateKey, 'ES256', $findSiteUrl->ios_key_id);
            return [
                'success' => true,
                'status' => Response::HTTP_OK,
                'message' => 'Token generated successfully',
                'token' => $jwt
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Error generating JWT: ' . $e->getMessage(),
            ];
        }
    }

    private function bundleExists($findSiteUrl, $token)
    {
        $response = $this->apiRequest('GET', 'bundleIds?limit=200', $token);

        if (isset($response['status']) && $response['status'] === 401) {
            return 'Unauthorized';
        }

        if (isset($response['data'])) {
            foreach ($response['data'] as $bundle) {
                if ($bundle['attributes']['identifier'] === $findSiteUrl->package_name) {
                    return $bundle['attributes']['identifier'];
                }
            }
        }

        return false;
    }

    private function createBundle($token, $identifier, $name)
    {
        $payload = [
            'data' => [
                'type' => 'bundleIds',
                'attributes' => [
                    'identifier' => $identifier,
                    'name' => $name ?? 'appza-app',
                    'platform' => 'IOS',
                ]
            ]
        ];

        $response = $this->apiRequest('POST', 'bundleIds', $token, $payload);

        if (isset($response['data']['attributes']['identifier'])) {
            return [
                'success' => true,
                'status' => Response::HTTP_OK,
                'message' => 'Bundle created',
                'data' => $response['data']['attributes']['identifier'],
            ];
        }

        return [
            'success' => false,
            'status' => $response['status'] ?? 400,
            'message' => $response['message'] ?? 'Failed to create bundle',
            'errors' => $response['errors'] ?? [],
        ];
    }

    private function checkAppExists($token, $bundleId)
    {
        $apps = $this->apiRequest('GET', 'apps?limit=200', $token);

        if (isset($apps['data'])) {
            foreach ($apps['data'] as $app) {
                if ($app['attributes']['bundleId'] === $bundleId) {
                    return $app['attributes']['name'];
                }
            }
        }

        return false;
    }

    private function getDistributionCertificate($token)
    {
        $certificates = $this->apiRequest('GET', 'certificates?limit=200', $token);

        if (isset($certificates['data'])) {
            $valid = array_filter($certificates['data'], function ($cert) {
                return $cert['attributes']['certificateType'] === 'DISTRIBUTION' &&
                    $cert['attributes']['expirationDate'] > Carbon::now()->toISOString();
            });

            if (count($valid) >= 2) {
                $random = array_rand($valid);
                return $valid[$random]['id'];
            }
        }

        return null;
    }

    private function checkProfileExists($token, $profileName)
    {
        $profiles = $this->apiRequest('GET', 'profiles?limit=200', $token);

        if (isset($profiles['data'])) {
            foreach ($profiles['data'] as $profile) {
                if ($profile['attributes']['name'] === $profileName) {
                    return $profile['id'];
                }
            }
        }

        return null;
    }

    private function apiRequest($method, $endpoint, $token, $data = null)
    {
        try {
            $client = new Client();
            $url = "https://api.appstoreconnect.apple.com/v1/$endpoint";

            $response = $client->request($method, $url, [
                'headers' => [
                    'Authorization' => "Bearer $token",
                    'Content-Type' => 'application/json'
                ],
                'json' => $data,
                'http_errors' => true,
            ]);

            return json_decode($response->getBody(), true);

        } catch (ClientException $e) {
            $body = json_decode($e->getResponse()->getBody(), true);
            return [
                'status' => $e->getResponse()->getStatusCode(),
                'message' => $body['errors'][0]['detail'] ?? $e->getMessage(),
                'errors' => $body['errors'] ?? []
            ];
        } catch (\Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Internal API error: ' . $e->getMessage()
            ];
        }
    }
}


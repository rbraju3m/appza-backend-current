<?php
namespace App\Services;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use http\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class IosBuildValidationService_bk {
    function iosBuildProcessValidation($findSiteUrl)
    {
        $token = $this->generateJwt($findSiteUrl);
        $bundle = $this->bundleExists($findSiteUrl,$token);
        if ($bundle=='Unauthorized'){
            return $bundle;
        }

        if ($bundle!='Unauthorized' && $bundle == false){
            $this->createBundle($token,$findSiteUrl->package_name,$findSiteUrl->app_name);
            return true;
        }
        return true;
    }

    function iosBuildProcessValidation2($findSiteUrl)
    {
        $token = $this->generateJwt($findSiteUrl);

        $appName = $this->checkAppExists($token,$findSiteUrl->package_name);

        if ($appName){
            $cert = $this->getDistributionCertificate($token);
            if ($cert != null){
                $this->apiRequest('DELETE', "certificates/$cert", $token);
            }
            $profileName = "match AppStore ".$findSiteUrl->package_name;
            $profile = $this->checkProfileExists($token,$profileName);
            if ($profile != null){
                $this->apiRequest('DELETE', "profiles/$profile", $token);
                return ['status' => true, 'app_name' => $appName];
            }
            return ['status' => true, 'app_name' => $appName];
        }else{
            return ['status' => false, 'app_name' => null];
        }
    }

    function generateJwt($findSiteUrl) {
        $p8file = public_path() . '/upload/build-apk/p8file/' . $findSiteUrl->ios_p8_file_content;
        if (!file_exists($p8file)) {
            $response = new JsonResponse([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'The .p8 file was not found',
            ], Response::HTTP_OK);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        // Read the private key with file_get_contents
        $privateKey = file_get_contents($p8file);
        // Ensure the private key was actually loaded
        if (!$privateKey) {
            $response = new JsonResponse([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Failed to read the private key from the .p8 file.',
            ], Response::HTTP_OK);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $key_id = $findSiteUrl->ios_key_id;
        $payload = [
            'iss' => $findSiteUrl->ios_issuer_id,
            'iat' => time(),
            'exp' => time() + (20 * 60), // Token valid for 20 minutes
            'aud' => 'appstoreconnect-v1'
        ];

        try {
            // This ensures ES256 algorithm is used while encoding the JWT
            return JWT::encode($payload, $privateKey, 'ES256', $key_id);
        } catch (Exception $e) {
            $response = new JsonResponse([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Error generating JWT: '.$e->getMessage(),
            ], Response::HTTP_OK);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    function bundleExists($findSiteUrl,$token)
    {
        $bundleIds = $this->apiRequest('get','bundleIds?limit=200',$token,null);
        if ($bundleIds instanceof \Symfony\Component\HttpFoundation\JsonResponse) {
            $responseData = json_decode($bundleIds->getContent(), true);
            if (isset($responseData['status']) && $responseData['status'] == 401) {
                return "Unauthorized";
            }
        }
        if ($bundleIds) {
            foreach ($bundleIds['data'] as $bundle_id) {
                if ($bundle_id['attributes']['identifier'] === $findSiteUrl->package_name) {
                    return $bundle_id['attributes']['identifier'];
                }
            }
            return false;
        }
    }

    function createBundle($token, $identifier, $name) {
        $data = [
            'data' => [
                'type' => 'bundleIds',
                'attributes' => [
                    'identifier' => $identifier,
                    'name' => $name??'appza-app',
                    'platform' => 'IOS'
                ]
            ]
        ];

        $response = $this->apiRequest('POST', 'bundleIds', $token, $data);

        if (isset($response['data']['attributes']['identifier'])) {
            return $response['data']['attributes']['identifier'];
        }
        return null;
    }

    function checkAppExists($token, $bundleId) {
        $apps = $this->apiRequest('get','apps?limit=200',$token,null);

        if ($apps && isset($apps['data'])) {
            foreach ($apps['data'] as $app) {
                if ($app['attributes']['bundleId'] === $bundleId) {
                    return $app['attributes']['name'];
                }
            }
        }
//        dump($apps);

        /*if ($appName){
            if ($apps && isset($apps['data'])) {
                foreach ($apps['data'] as $app) {
                    if ($app['attributes']['bundleId'] === $bundleId) {
                        return $app['attributes']['name'];
                    }
                }
            }
        }else{
            if ($apps && isset($apps['data'])) {
                foreach ($apps['data'] as $app) {
                    if ($app['attributes']['bundleId'] === $bundleId) {
                        return $app['attributes']['name'];
                    }
                }
            }
        }*/
//        return false;
    }

    function getDistributionCertificate($token) {
        $certificates = $this->apiRequest('GET', 'certificates?limit=200', $token);

        if ($certificates && isset($certificates['data'])) {
            $distributionCerts = array_filter(
                array_map(function($cert) {
                    return [
                        'ID' => $cert['id'],
                        'Name' => $cert['attributes']['name'],
                        'Type' => $cert['attributes']['certificateType'],
                        'expirationDate' => $cert['attributes']['expirationDate'],
                        'Platforms' => isset($cert['attributes']['platforms']) ? $cert['attributes']['platforms'] : 'N/A'
                    ];
                }, $certificates['data']),
                function($cert) {
                    // previous check that work okay
//                    return $cert['Type'] === 'DISTRIBUTION';

                    // next implement for saiful req for more validation
                    $current_time = Carbon::now()->format('Y-m-d\TH:i:s.000+00:00');
                    return $cert['Type'] === 'DISTRIBUTION' && ($cert['expirationDate'] ?? '') > $current_time;
                }
            );

            // Count the number of distribution certificates
            $distributionCount = count($distributionCerts);
            // Check if there are more than 2 distribution certificates
            /*if ($distributionCount >= 2) {
                return $distributionCerts[0]['ID'];
            } else {
                return null;
            }*/

            if ($distributionCount >= 2) {
                $randomKey = array_rand($distributionCerts); // Get a random key from the array
                return $distributionCerts[$randomKey]['ID']; // Return the ID of the randomly selected item
            } else {
                return null;
            }
        }
        return null;
    }

    function checkProfileExists($token, $profileId) {
        $profiles = $this->apiRequest('GET', 'profiles?limit=200', $token);

        if ($profiles && isset($profiles['data'])) {
            foreach ($profiles['data'] as $profile) {
                if ($profile['attributes']['name'] === $profileId) {
                    return $profile['id'];
                }
            }
        }
        return null;
    }

    function apiRequest($method, $endpoint, $token, $data = null) {
        $client = new Client();
        $url = "https://api.appstoreconnect.apple.com/v1/$endpoint";
        $headers = [
            'Authorization' => "Bearer $token",
            'Content-Type' => 'application/json'
        ];

        try {
            $response = $client->request($method, $url, [
                'headers' => $headers,
                'json' => $data
            ]);

            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $response = new JsonResponse([
                'status' => Response::HTTP_UNAUTHORIZED,
                'message' => 'API ERROR 1: ' . $e->getMessage(),
                'details' => json_decode($responseBody, true)
            ], Response::HTTP_UNAUTHORIZED);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } catch (Exception $e) {
            $response = new JsonResponse([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'API ERROR: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }
}

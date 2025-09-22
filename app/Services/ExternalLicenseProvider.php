<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalLicenseProvider
{
    /**
     * Call external fluent API by license_key + activation_hash.
     *
     * Returns normalized license DTO:
     * [
     *   'product_slug' => 'app_name',
     *   'expiration_date' => 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS',
     *   'grace_period_date' => 'YYYY-MM-DD HH:MM:SS'  // expiration + 15 for premium
     * ]
     *
     * Returns null if no license / invalid.
     * Throws on network errors.
     * @throws ConnectionException
     */
    public function fetchLicenseByKey(string $licenseKey, ?string $activationHash, int $itemId, string $apiUrl, string $siteUrl, string $productSlug): ?array
    {
        // minimal validation
        if (! $licenseKey || ! $itemId || ! $apiUrl) {
            return null;
        }

        $params = [
            'fluent-cart' => 'check_license',
            'license_key' => $licenseKey,
            'item_id' => $itemId,
            'site_url' => $siteUrl,
        ];
        if ($activationHash) $params['activation_hash'] = $activationHash;

        try {
            $resp = Http::timeout(12)->retry(2, 100)->get($apiUrl, $params);

            if (! $resp->ok()) {
                Log::warning("ExternalLicenseProvider: API responded with non-OK status {$resp->status()} for site {$siteUrl}");
                return null;
            }

            $data = $resp->json();

            // expected: ['status'=>'valid'|'invalid', 'expiration_date'=>'2025-10-01', ...]
            if (empty($data) || ($data['status'] ?? null) !== 'valid') {
                return null;
            }

            if (empty($data['expiration_date'])) {
                return null;
            }

            $exp = Carbon::parse($data['expiration_date'])->startOfDay();
            // premium uses fixed 15-day grace
            $grace = $exp->copy()->addDays(15);

            return [
                'product_slug' => $productSlug,
                'expiration_date' => $exp->toDateString(),
                'grace_period_date' => $grace->toDateString(),
            ];
        } catch (\Exception $e) {
            Log::error('ExternalLicenseProvider exception: ' . $e->getMessage(), ['site' => $siteUrl, 'product' => $productSlug]);
            throw $e;
        }
    }
}

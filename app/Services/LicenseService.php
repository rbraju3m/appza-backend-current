<?php

namespace App\Services;

use App\Models\FluentInfo;
use App\Models\LicenseLogic;
use App\Models\LicenseMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LicenseService
{
    protected const CACHE_LICENSE_LOGICS = 'license_logics_all';
    protected const CACHE_LICENSE_MESSAGES = 'license_messages_by_logic_and_product_id';

    /**
     * Evaluate the license status and return a structured response.
     */
    public function evaluate(object $license, string $licenseType, array $context = []): array
    {
        $today = Carbon::now()->startOfDay();

        $productSlug = data_get($license, 'product_slug');
        $expirationString = data_get($license, 'expiration_date');
        $graceString = data_get($license, 'grace_period_date');

        if (!$expirationString) {
            return $this->formatInvalidResponse('invalid_license_data');
        }

        $expDate = Carbon::parse($expirationString)->startOfDay();
        $graceDate = $graceString ? Carbon::parse($graceString)->startOfDay() : $expDate->copy();

        $product = FluentInfo::where('product_slug', $productSlug)->where('is_active', 1)->first();
        $productId = $product?->id ?? null;

        $expDiff = $today->diffInDays($expDate, false);
        $graceDiff = $today->diffInDays($graceDate, false);

        $status = $today->gt($graceDate) ? 'expire' : 'active';
        $subStatus = $today->lt($expDate) ? 'before_exp' : ($today->eq($expDate) ? 'expires_today' : ($today->lte($graceDate) ? 'in_grace' : 'grace_expired'));

        if ($today->lte($expDate)) {
            $primaryMsg = $this->findMatchedMessage('expiration', $expDiff, $productId,$licenseType);
        } else {
            $primaryMsg = $this->findMatchedMessage('grace', $graceDiff, $productId,$licenseType);
        }

        return [
            'status' => $status,
            'sub_status' => $subStatus,
            'message' => $primaryMsg ?? $this->emptyMessageObject(),
            'meta' => [
                'expiration_days_diff' => $expDiff,
                'grace_days_diff' => $graceDiff,
                'product_id' => $productId,
            ],
        ];
    }

    /**
     * Format a response for an invalid license scenario.
     */
    public function formatInvalidResponse(string $slug, ?string $extra = null, ?string $productSlug = null): array
    {
        $logic = LicenseLogic::where('slug', $slug)->first();
        $productId = null;

        if ($productSlug) {
            $product = FluentInfo::where('product_slug', $productSlug)->where('is_active', 1)->first();
            $productId = $product?->id ?? null;
        }

        $msg = null;
        if ($logic) {
            $msg = $this->getCachedMessageForLogic($logic->id, $productId) ?? $this->getCachedMessageForLogic($logic->id, null);
        }

        $msgFormat = $msg ? [
            'user' => ['message' => $msg['message_user'], 'message_id' => $msg['message_user_id']],
            'admin' => ['message' => $msg['message_admin'], 'message_id' => $msg['message_admin_id']],
            'special' => ['message' => $msg['message_special'], 'message_id' => $msg['message_special_id']],
        ] : $this->emptyMessageObject();

        // Handle specific case for validation errors that don't come from the DB
        if (($slug === 'validation_error' || $slug === 'unauthorized') && $extra) {
            $msgFormat = [
                'user' => ['message' => $extra, 'message_id' => null],
                'admin' => ['message' => $extra, 'message_id' => null],
                'special' => ['message' => $extra, 'message_id' => null],
            ];
        }

        return [
            'sub_status' => $slug,
            'message' => $msgFormat,
        ];
    }

    protected function emptyMessageObject(): array
    {
        return [
            'user' => ['message' => null, 'message_id' => null],
            'admin' => ['message' => null, 'message_id' => null],
            'special' => ['message' => null, 'message_id' => null],
        ];
    }

    protected function findMatchedMessage(string $event, int $dayDiff, ?int $productId = null, string $licenseType): ?array
    {
        $direction = $dayDiff > 0 ? 'before' : ($dayDiff < 0 ? 'after' : 'equal');
        $absDays = abs($dayDiff);

        $logics = $this->getCachedLogicsForEvent($event);

        $logic = collect($logics)->first(function ($l) use ($direction, $absDays) {
            return $l['direction'] === $direction && $l['from_days'] <= $absDays && $l['to_days'] >= $absDays;
        });

        if (!$logic) return null;

        $msg = $this->getCachedMessageForLogic($logic['id'], $productId) ?? $this->getCachedMessageForLogic($logic['id'], null);

        if (!$msg) return null;

        return [
            'user' => ['message' => $msg['message_user'], 'message_id' => $msg['message_user_id']],
            'admin' => ['message' => $msg['message_admin'], 'message_id' => $msg['message_admin_id']],
            'special' => ['message' => $msg['message_special'], 'message_id' => $msg['message_special_id']],
        ];
    }

    /* ---------- Caching helpers ---------- */

    protected function getCachedLogicsForEvent(string $event): array
    {
//        $all = Cache::rememberForever(self::CACHE_LICENSE_LOGICS, function () {
            return LicenseLogic::orderBy('event')->where('event',$event)->orderBy('from_days')->get()->map(function ($l) {
                return [
                    'id' => $l->id,
                    'slug' => $l->slug ?? null,
                    'name' => $l->name,
                    'event' => $l->event,
                    'direction' => $l->direction,
                    'from_days' => (int)($l->from_days ?? 0),
                    'to_days' => (int)($l->to_days ?? 0),
                ];
            })->toArray();
//        });

//        return array_values(array_filter($all, fn ($r) => $r['event'] === $event));
    }

    protected function getCachedMessageForLogic(int $logicId, ?int $productId = null): ?array
    {
//        $cacheKey = self::CACHE_LICENSE_MESSAGES . "_{$logicId}_product_" . ($productId ?? 'any');

//        return Cache::remember($cacheKey, 3600, function () use ($logicId, $productId) {
            $q = LicenseMessage::where('license_logic_id', $logicId)->where('is_active', true);
            if ($productId) {
                $q->where('product_id', $productId);
            }
            $row = $q->first();

            if (!$row) return null;

            return [
                'id' => $row->id,
                'license_logic_id' => $row->license_logic_id,
                'product_id' => $row->product_id,
                'message_user' => optional($row->message_details->firstWhere('type', 'user'))->message,
                'message_user_id' => optional($row->message_details->firstWhere('type', 'user'))->id,
                'message_admin' => optional($row->message_details->firstWhere('type', 'admin'))->message,
                'message_admin_id' => optional($row->message_details->firstWhere('type', 'admin'))->id,
                'message_special' => optional($row->message_details->firstWhere('type', 'special'))->message,
                'message_special_id' => optional($row->message_details->firstWhere('type', 'special'))->id
            ];
//        });
    }

    public static function checkPremiumLicense( string $apiUrl , array $params): array
    {
        try {
            // Example API request (use Http facade, Guzzle, etc.)

            $response = Http::timeout(15)
                ->retry(2, 100)
                ->get($apiUrl, $params);
//            $data = $response->json();
            dump($response);

            if (!$response->ok()) {
                return self::formatInvalidResponse('server_error', 'Premium API error');
            }

            $data = $response->json();

            // Example: API returns { status: "active|expired", expiration_date: "2025-09-30" }
            if ($data['status'] === 'active') {
                $licenseObj = (object) [
                    'expire_date' => $data['expiration_date'],
                    // Premium has fixed 15-day grace period
                    'grace_period_date' => \Carbon\Carbon::parse($data['expiration_date'])->addDays(15),
                ];

                $resp = self::evaluate($licenseObj, 'premium', $product);

                return [
                    'status' => $resp['status'],
                    'sub_status' => $resp['sub_status'],
                    'message' => $resp['message'],
                ];
            } else {
                return self::formatInvalidResponse('license_not_found', 'Premium license expired or not found.');
            }
        } catch (\Exception $e) {
            return self::formatInvalidResponse('server_error', $e->getMessage());
        }
    }

}

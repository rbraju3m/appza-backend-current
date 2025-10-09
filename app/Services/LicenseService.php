<?php

namespace App\Services;

use App\Models\FluentInfo;
use App\Models\LicenseLogic;
use App\Models\LicenseMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LicenseService
{
    public const CACHE_LICENSE_LOGICS = 'license_logics_all';
    public const CACHE_LICENSE_MESSAGES = 'license_messages_by_logic_and_product';

    /**
     * Evaluate normalized license data.
     * Expects: ['product_slug', 'expiration_date', 'grace_period_date', 'license_type' => optional]
     */
    public function evaluate(array $licenseData): array
    {
        $today = Carbon::now()->startOfDay();

        $productSlug = $licenseData['product_slug'] ?? null;
        $expString = $licenseData['expiration_date'] ?? null;
        $graceString = $licenseData['grace_period_date'] ?? null;
        $licenseType = $licenseData['license_type'] ?? null;

        if (!$expString) {
            return $this->formatInvalidResponse('invalid_license_data', null, $productSlug);
        }

        $expDate = Carbon::parse($expString)->startOfDay();
        $graceDate = $graceString ? Carbon::parse($graceString)->startOfDay() : $expDate->copy();

        $product = FluentInfo::where('product_slug', $productSlug)->where('is_active', 1)->first();
        $productId = $product?->id ?? null;

        $expDiff = $today->diffInDays($expDate, false);
        $graceDiff = $today->diffInDays($graceDate, false);

        $subStatus = $today->lt($expDate) ? 'before_exp'
            : ($today->equalTo($expDate) ? 'expires_today'
                : ($today->lte($graceDate) ? 'in_grace' : 'grace_expired'));

        $status = in_array($subStatus, ['before_exp', 'expires_today', 'in_grace']) ? 'active' : 'expired';

        $primary = $today->lte($expDate)
            ? $this->findMatchedMessage('expiration', $expDiff, $productId, $licenseType)
            : $this->findMatchedMessage('grace', $graceDiff, $productId, $licenseType);

        if (!$primary) {
            $primary = $this->findMatchedMessage('expiration', $expDiff, $productId, $licenseType)
                ?? $this->findMatchedMessage('grace', $graceDiff, $productId, $licenseType);
        }

        return [
            'status' => $status,
            'sub_status' => $subStatus,
            'message' => $primary ?? $this->emptyMessageObject(),
            'meta' => [
                'expiration_days_diff' => $expDiff,
                'grace_days_diff' => $graceDiff,
                'product_id' => $productId,
            ],
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

    /*protected function findMatchedMessage(string $event, int $dayDiff, ?int $productId = null, ?string $licenseType = null): ?array
    {
        $direction = $dayDiff > 0 ? 'before' : ($dayDiff < 0 ? 'after' : 'equal');
        $absDays = abs($dayDiff);
//        dump($absDays);

        $logics = $this->getCachedLogicsForEvent($event);
        dump($logics);

        $logic = collect($logics)->first(fn($l) => $l['direction'] === $direction
            && $l['from_days'] <= $absDays
            && $l['to_days'] >= $absDays
        );

        dump($logic);

        if (!$logic) return null;

        $msg = $this->getCachedMessageForLogic($logic['id'], $productId, $licenseType)
            ?? $this->getCachedMessageForLogic($logic['id'], null, $licenseType);

        if (!$msg) return null;

        return [
            'user' => ['message' => $msg['message_user'], 'message_id' => $msg['message_user_id']],
            'admin' => ['message' => $msg['message_admin'], 'message_id' => $msg['message_admin_id']],
            'special' => ['message' => $msg['message_special'], 'message_id' => $msg['message_special_id']],
        ];
    }*/
    protected function findMatchedMessage(string $event, int $dayDiff, ?int $productId = null, ?string $licenseType = null): ?array
    {
        $direction = $dayDiff > 0 ? 'before' : ($dayDiff < 0 ? 'after' : 'equal');
        $absDays = abs($dayDiff);

        $logics = $this->getCachedLogicsForEvent($event);
        if (empty($logics)) return null;

        $matchingLogics = collect($logics)->filter(fn($l) =>
            $l['direction'] === $direction &&
            $l['from_days'] <= $absDays &&
            $l['to_days'] >= $absDays
        );

        if ($matchingLogics->isEmpty()) return null;

        // ✅ Step 1: Try to find an exact match
        $exactLogic = $matchingLogics->first(fn($l) =>
            $l['from_days'] === $absDays || $l['to_days'] === $absDays
        );

        $logic = $exactLogic
            // ✅ Step 2: If no exact match, find the closest range
            ?? $matchingLogics->sortBy(fn($l) =>
            abs($absDays - (($l['from_days'] + $l['to_days']) / 2))
            )->first();

        if (!$logic) return null;

        // Step 3: Get message
        $msg = $this->getCachedMessageForLogic($logic['id'], $productId, $licenseType)
            ?? $this->getCachedMessageForLogic($logic['id'], null, $licenseType);

        if (!$msg) return null;

        // Step 4: Build return data
        return [
            'user' => [
                'message' => $msg['message_user'],
                'message_id' => $msg['message_user_id']
            ],
            'admin' => [
                'message' => $msg['message_admin'],
                'message_id' => $msg['message_admin_id']
            ],
            'special' => [
                'message' => $msg['message_special'],
                'message_id' => $msg['message_special_id']
            ],
        ];
    }



    /* ---------- Caching Helpers ---------- */

    protected function getCachedLogicsForEvent(string $event): array
    {
        $all = Cache::rememberForever(self::CACHE_LICENSE_LOGICS, function () {
            return LicenseLogic::orderBy('event')->orderBy('from_days')->get()->map(fn($l) => [
                'id' => $l->id,
                'slug' => $l->slug,
                'name' => $l->name,
                'event' => $l->event,
                'direction' => $l->direction,
                'from_days' => (int)$l->from_days,
                'to_days' => (int)$l->to_days,
            ])->toArray();
        });

        return array_values(array_filter($all, fn($r) => $r['event'] === $event));
    }

    protected function getCachedMessageForLogic(int $logicId, ?int $productId = null, ?string $licenseType = null): ?array
    {
        $cacheKey = self::CACHE_LICENSE_MESSAGES . "_{$logicId}_product_" . ($productId ?? 'any');
        if ($licenseType) {
            $cacheKey .= "_type_{$licenseType}";
        }

        return Cache::remember($cacheKey, 3600, function () use ($logicId, $productId, $licenseType) {
            $q = LicenseMessage::where('license_logic_id', $logicId)
                ->where('is_active', true);

            if ($productId) $q->where('product_id', $productId);
            if ($licenseType) $q->where('license_type', $licenseType);

            $row = $q->with('message_details')->first();
            if (!$row) return null;

            return [
                'id' => $row->id,
                'message_user' => optional($row->message_details->firstWhere('type', 'user'))->message,
                'message_user_id' => optional($row->message_details->firstWhere('type', 'user'))->id,
                'message_admin' => optional($row->message_details->firstWhere('type', 'admin'))->message,
                'message_admin_id' => optional($row->message_details->firstWhere('type', 'admin'))->id,
                'message_special' => optional($row->message_details->firstWhere('type', 'special'))->message,
                'message_special_id' => optional($row->message_details->firstWhere('type', 'special'))->id,
            ];
        });
    }

    /**
     * Format invalid license response using logic slug (and optional product)
     */
    public function formatInvalidResponse(string $slug, ?string $extra = null, ?string $productSlug = null): array
    {
        $logic = LicenseLogic::where('slug', $slug)->first();
        $msgFormat = $this->emptyMessageObject();

        if ($logic) {
            $productId = null;
            if ($productSlug) {
                $product = FluentInfo::where('product_slug', $productSlug)
                    ->where('is_active', 1)->first();
                $productId = $product?->id ?? null;
            }

            $msg = $this->getCachedMessageForLogic($logic->id, $productId)
                ?? $this->getCachedMessageForLogic($logic->id, null);

            if ($msg) {
                $msgFormat = [
                    'user' => ['message' => $msg['message_user'], 'message_id' => $msg['message_user_id']],
                    'admin' => ['message' => $msg['message_admin'], 'message_id' => $msg['message_admin_id']],
                    'special' => ['message' => $msg['message_special'], 'message_id' => $msg['message_special_id']],
                ];
            }
        }

        if (in_array($slug, ['validation_error', 'unauthorized']) && $extra) {
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
}

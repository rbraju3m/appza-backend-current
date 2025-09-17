<?php
namespace App\Services;

use App\Models\FluentInfo;
use App\Models\LicenseLogic;
use App\Models\LicenseMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LicenseService_bk
{
    protected const CACHE_LICENSE_LOGICS = 'license_logics_all';

    public function evaluate($licenseOrNull,  array $context = []): array
    {
        $today = Carbon::now()->startOfDay();

        // Accept both model and array-like
        $productSlug = data_get($licenseOrNull, 'product_slug') ?? data_get($licenseOrNull, 'product_slug');
        $expirationString = data_get($licenseOrNull, 'expiration_date');
        $graceString      = data_get($licenseOrNull, 'grace_period_date');


        // if missing dates -> invalid
        if (! $expirationString) {
            return $this->formatInvalidResponse('invalid_license_data');
        }

        $expDate = Carbon::parse($expirationString)->startOfDay();
        $graceDate = $graceString ? Carbon::parse($graceString)->startOfDay() : $expDate->copy();

        // Resolve product id if possible (used to scope messages)
        $product = FluentInfo::where('product_slug', $productSlug)->where('is_active',1)->first();
        $productId = $product?->id ?? null;

        // compute day diffs
        $expDiff = $today->diffInDays($expDate, false);    // + => before exp | 0 => today | - => after (expired)
        $graceDiff = $today->diffInDays($graceDate, false);

        // compute high-level status
        $status = $today->gt($graceDate) ? 'expire' : 'active';

        // compute substatus (useful for client)
        $subStatus = $today->lt($expDate) ? 'before_exp'
            : ($today->eq($expDate) ? 'expires_today'
                : ($today->lte($graceDate) ? 'in_grace' : 'grace_expired'));

        // otherwise prefer grace logic (in_grace or expired).
        if ($today->lte($expDate)) {
            $primaryMsg = $this->findMatchedMessage('expiration', $expDiff, $productId);
        } else {
            $primaryMsg = $this->findMatchedMessage('grace', $graceDiff, $productId);
        }

        // build final payload
        $payload = [
            'status' => $status,
            'sub_status' => $subStatus,
            'message' => $primaryMsg ?? $this->emptyMessageObject(),
            /*'meta' => [
                'expiration_days_diff' => $expDiff,
                'grace_days_diff' => $graceDiff,
                'product_id' => $productId,
            ],*/
        ];

        return $payload;
    }

    protected function emptyMessageObject(): array
    {
        return [
            'user' => ['message' => null, 'message_id' => null],
            'admin' => ['message' => null, 'message_id' => null],
            'special' => ['message' => null, 'message_id' => null],
        ];
    }

    /**
     * Look up license logic row that matches event & dayDiff; return structured role messages.
     */
    protected function findMatchedMessage(string $event, int $dayDiff, ?int $productId = null): ?array
    {
        $direction = $dayDiff > 0 ? 'before' : ($dayDiff < 0 ? 'after' : 'equal');
        $absDays = abs($dayDiff);

        $logics = $this->getCachedLogicsForEvent($event);

        // find the first logic that matches range
        $logic = collect($logics)
            ->first(function($l) use ($direction, $absDays) {
                return $l['direction'] === $direction
                    && $l['from_days'] <= $absDays
                    && $l['to_days'] >= $absDays;
            });

        if (! $logic) return null;

        // try product-specific message row first, fall back to generic
        $msg = $this->getCachedMessageForLogic($logic['id'], $productId) ?? $this->getCachedMessageForLogic($logic['id'], null);
        if (! $msg) return null;

        return [
            'user' => ['message' => $msg['message_user'], 'message_id' => $msg['id']],
            'admin' => ['message' => $msg['message_admin'], 'message_id' => $msg['id']],
            'special' => ['message' => $msg['message_special'], 'message_id' => $msg['id']],
        ];
    }

    /* ---------- Caching helpers ---------- */

    protected function getCachedLogicsForEvent(string $event): array
    {
        // Cache entire license_logics table in memory (invalidate on admin changes)
        $all = Cache::rememberForever(self::CACHE_LICENSE_LOGICS, function() {
            return LicenseLogic::orderBy('event')->orderBy('from_days')->get()->map(function($l){
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
        });

        return array_values(array_filter($all, fn($r) => $r['event'] === $event));
    }

    protected function getCachedMessageForLogic(int $logicId, ?int $productId = null): ?array
    {
        $cacheKey = "license_message_{$logicId}_product_" . ($productId ?? 'any');

        return Cache::remember($cacheKey, 3600, function() use ($logicId, $productId) {
            $q = LicenseMessage::where('license_logic_id', $logicId)->where('is_active', true);
            if ($productId) $q->where('product_id', $productId);
            $row = $q->first();
            if (! $row) return null;
            return [
                'id' => $row->id,
                'license_logic_id' => $row->license_logic_id,
                'product_id' => $row->product_id,
                'message_user' => optional($row->message_details->firstWhere('type', 'user'))->message,
                'message_admin' => optional($row->message_details->firstWhere('type', 'admin'))->message,
                'message_special' => optional($row->message_details->firstWhere('type', 'special'))->message,
            ];
        });
    }

    /* ---------- Invalid handling (use logic slug lookup) ---------- */

    public function formatInvalidResponse(string $slug, string $extra = null , string $productSlug = null): ?array
    {
//        dump($slug, $extra, $productSlug);
        // Try find logic by slug (invalid event entries should have slug)
        $logic = LicenseLogic::where('slug', $slug)->first();

        $messageRow = null;
        if ($logic && $productSlug) {
            $product = FluentInfo::where('product_slug', $productSlug)->where('is_active', 1)->first();
            $productId = $product?->id ?? null;
            $msg = $this->getCachedMessageForLogic($logic->id, $productId) ?? $this->getCachedMessageForLogic($logic->id, null);

            if (! $msg) return null;

            $msgFormat =  [
                'user' => ['message' => $msg['message_user'], 'message_id' => $msg['id']],
                'admin' => ['message' => $msg['message_admin'], 'message_id' => $msg['id']],
                'special' => ['message' => $msg['message_special'], 'message_id' => $msg['id']],
            ];

            $payload = [
                'sub_status' => $slug,
                'message' => $msgFormat ?? $this->emptyMessageObject(),
            ];

            return $payload;
        }
        if ($slug === 'validation_error') {
            $msgFormat = [
                'user' => ['message' => $extra, 'message_id' => null],
                'admin' => ['message' => $extra, 'message_id' => null],
                'special' => ['message' => $extra, 'message_id' => null],
            ];
        }

        $payload = [
            'sub_status' => $slug,
            'message' => $msgFormat ?? $this->emptyMessageObject(),
        ];

        return $payload;
    }
}

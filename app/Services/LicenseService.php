<?php

namespace App\Services;

use App\Models\LicenseLogic;
use App\Models\LicenseMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LicenseService
{
    /**
     * Evaluate license status (free_trial | premium)
     */
    public static function evaluate($license, string $provider, string $productSlug): array
    {
        $today = Carbon::today();
        $expDate = Carbon::parse($license->expire_date)->startOfDay();
        $graceDate = $license->grace_period_date
            ? Carbon::parse($license->grace_period_date)->startOfDay()
            : $expDate;

        $daysUntilExp = $today->diffInDays($expDate, false);
        $daysAfterExp = $expDate->diffInDays($today, false);
        $daysAfterGrace = $graceDate->diffInDays($today, false);

        $subStatus = 'unknown';
        $logic = null;

        // 🔹 Expiration checks
        if ($today->lt($expDate)) {
            $subStatus = 'before_exp';
            $logic = self::findMatchedMessage($provider, 'expiration', 'before', abs($daysUntilExp), $productSlug);
        } elseif ($today->eq($expDate)) {
            $subStatus = 'expires_today';
            $logic = self::findMatchedMessage($provider, 'expiration', 'equal', 0, $productSlug);
        } elseif ($today->gt($expDate) && $today->lte($graceDate)) {
            $subStatus = 'in_grace';
            $logic = self::findMatchedMessage($provider, 'grace', 'before', abs($daysAfterExp), $productSlug);
        } elseif ($today->gt($graceDate)) {
            $subStatus = 'grace_expired';
            $logic = self::findMatchedMessage($provider, 'grace', 'after', abs($daysAfterGrace), $productSlug);
        }

        return [
            'status' => in_array($subStatus, ['before_exp', 'expires_today', 'in_grace']) ? 'active' : 'expired',
            'sub_status' => $subStatus,
            'message' => $logic ?? [],
        ];
    }

    /**
     * Match a message from license_logics/messages
     */
    public static function findMatchedMessage(string $provider, string $logicType, string $direction, int $absDays, string $productSlug): ?array
    {
        $logics = self::getCachedLogicsForEvent($provider, $logicType, $direction);

        foreach ($logics as $logic) {
            if ($absDays >= $logic->from_days && $absDays <= $logic->to_days) {
                return self::getCachedMessageForLogic($logic->id, $productSlug);
            }
        }
        return null;
    }

    /**
     * Cache: license_logics
     */
    public static function getCachedLogicsForEvent(string $provider, string $logicType, string $direction)
    {
        return Cache::rememberForever("license_logics_{$provider}_{$logicType}_{$direction}", function () use ($provider, $logicType, $direction) {
            return LicenseLogic::where('provider', $provider)
                ->where('logic_type', $logicType)
                ->where('direction', $direction)
                ->get();
        });
    }

    /**
     * Cache: license_messages
     */
    public static function getCachedMessageForLogic(int $logicId, string $productSlug): ?array
    {
        return Cache::rememberForever("license_message_{$logicId}_{$productSlug}", function () use ($logicId, $productSlug) {
            $logic = LicenseLogic::find($logicId);
            if (!$logic) return null;

            $productId = optional($logic->product)->id;

            $message = LicenseMessage::where('license_logic_id', $logicId)
                ->where('product_id', $productId)
                ->where('is_active', 1)
                ->first();

            if (!$message) {
                $message = LicenseMessage::where('license_logic_id', $logicId)
                    ->whereNull('product_id')
                    ->where('is_active', 1)
                    ->first();
            }

            return $message ? [
                'user' => [
                    'message' => $message->user_message,
                    'message_id' => $message->id,
                ],
                'admin' => [
                    'message' => $message->admin_message,
                    'message_id' => $message->id,
                ],
                'special' => [
                    'message' => $message->special_message,
                    'message_id' => $message->id,
                ],
            ] : null;
        });
    }

    /**
     * Format invalid response
     */
    public static function formatInvalidResponse(string $logicSlug, ?string $customMessage = null)
    {
        $logic = LicenseLogic::where('slug', $logicSlug)->first();

        $message = null;
        if ($logic) {
            $message = self::getCachedMessageForLogic($logic->id, request()->input('product', ''));
        }

        // Override user message if validation error
        if ($customMessage) {
            $message['user']['message'] = $customMessage;
        }

        return response()->json([
            'status' => 'invalid',
            'sub_status' => $logicSlug,
            'message' => $message,
            'popup_message' => [],
        ]);
    }
}

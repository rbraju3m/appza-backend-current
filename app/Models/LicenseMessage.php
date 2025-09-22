<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LicenseMessage extends Model
{
    protected $table = 'license_messages';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at','updated_at'];
    protected $fillable = ['product_id', 'addon_id', 'license_logic_id', 'license_type'];

    /**
     * Automatically clear message cache on save, delete, or restore.
     */
    protected static function booted()
    {
        static::saved(fn($message) => static::clearMessageCache($message));
        static::deleted(fn($message) => static::clearMessageCache($message));
//        static::restored(fn($message) => static::clearMessageCache($message));
    }

    /**
     * Clear cache for a message by logic and product
     */
    protected static function clearMessageCache($message)
    {
        $messages = is_iterable($message) ? $message : [$message];

        foreach ($messages as $msg) {
            // Clear cache for specific product
            $key = \App\Services\LicenseService::CACHE_LICENSE_MESSAGES . "_{$msg->license_logic_id}_product_" . ($msg->product_id ?? 'any');
            Cache::forget($key);

            // Clear fallback cache for any product
            $anyKey = \App\Services\LicenseService::CACHE_LICENSE_MESSAGES . "_{$msg->license_logic_id}_product_any";
            Cache::forget($anyKey);
        }
    }

    /**
     * Relationships
     */
    public function product()
    {
        return $this->belongsTo(FluentInfo::class, 'product_id');
    }

    public function addon()
    {
        return $this->belongsTo(Addon::class, 'addon_id');
    }

    public function logic()
    {
        return $this->belongsTo(LicenseLogic::class, 'license_logic_id');
    }

    public function message_details()
    {
        return $this->hasMany(LicenseMessageDetails::class, 'message_id');
    }
}

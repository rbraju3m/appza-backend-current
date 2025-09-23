<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LicenseLogic extends Model
{
    protected $table = 'license_logics';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at','updated_at'];
    protected $fillable = ['name', 'slug','event','direction','from_days','to_days','event_combination'];

    /**
     * Automatically clear cache when logics are saved, deleted, or restored.
     */
    protected static function booted()
    {
        static::saved(fn() => Cache::forget(\App\Services\LicenseService::CACHE_LICENSE_LOGICS));
        static::deleted(fn() => Cache::forget(\App\Services\LicenseService::CACHE_LICENSE_LOGICS));
//        static::restored(fn() => Cache::forget(\App\Services\LicenseService::CACHE_LICENSE_LOGICS));
    }

    /**
     * Relationship: Logic has many messages
     */
    public function messages()
    {
        return $this->hasMany(LicenseMessage::class, 'license_logic_id');
    }
}

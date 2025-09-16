<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseMessage extends Model
{

    protected $table = 'license_messages';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at', 'updated_at'];
    protected $fillable = ['product_id', 'addon_id','license_logic_id','license_type'];

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

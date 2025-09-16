<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseMessage extends Model
{

    protected $table = 'license_messages';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at', 'updated_at'];
    protected $fillable = ['product_id', 'addon_id','license_logic_id','license_type','message_user','message_admin','message_special'];

}

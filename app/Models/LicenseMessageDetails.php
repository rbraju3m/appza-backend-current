<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseMessageDetails extends Model
{

    protected $table = 'license_message_details';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at', 'updated_at'];
    protected $fillable = ['message_id','type','message'];

}

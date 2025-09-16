<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseLogic extends Model
{

    protected $table = 'license_logics';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at', 'updated_at'];
    protected $fillable = ['name', 'slug','event','direction','from_days','to_days'];

}

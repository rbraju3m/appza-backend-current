<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FluentLicenseInfo extends Model
{

    protected $table = 'appza_fluent_license_info';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['created_at','updated_at'];
    protected $fillable = [
        'build_domain_id','site_url','product_id','variation_id','license_key','activation_hash','product_title','variation_title','activation_limit','activations_count','expiration_date'
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->created_at = now();
            $model->updated_at = now();
        });

        self::updating(function ($model) {
            $model->updated_at = now();
        });
    }

}

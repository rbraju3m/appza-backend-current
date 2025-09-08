<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeTrial extends Model
{
    protected $table = 'appza_free_trial_request';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = [
        'product_slug',
        'site_url',
        'name',
        'email',
        'product_id',
        'variation_id',
        'license_key',
        'activation_hash',
        'product_title',
        'activation_limit',
        'activations_count',
        'expiration_date',
        'grace_period_date',
        'is_fluent_license_check'
    ];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BuildDomain extends Model
{
    use HasFactory;

    protected $table = 'appfiy_build_domain';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = [
        'site_url',
        'package_name',
        'email',
        'plugin_name',
        'license_key',
        'version_id',
        'fluent_id',
        'app_name',
        'app_logo',
        'app_splash_screen_image',
        'build_version',
        'ios_issuer_id',
        'ios_key_id',
        'ios_p8_file_content',
        'team_id',
        'is_android',
        'confirm_email',
        'is_ios',
        'fluent_item_id',
        'ios_app_name',
        'is_app_license_check',
        'build_plugin_slug',
        'is_deactivated'
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

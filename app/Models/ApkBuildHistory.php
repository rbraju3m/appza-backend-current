<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApkBuildHistory extends Model
{
    use HasFactory;

    protected $table = 'appfiy_apk_build_history';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $dates = ['created_at','updated_at'];

    protected $fillable = ['version_id', 'build_domain_id', 'fluent_id', 'app_name', 'app_logo', 'app_splash_screen_image', 'build_version','ios_issuer_id','ios_key_id','ios_team_id','ios_p8_file_content','ios_app_name'];

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

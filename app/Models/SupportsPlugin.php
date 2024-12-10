<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportsPlugin extends Model
{
    protected $table = 'appza_supports_plugin';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at','updated_at'];
    protected $fillable = [
        'name',
        'slug',
        'status'
    ];

    public static function getPluginDropdown(){
        return cache()->remember('plugin_dropdown', 60, function () {
            return self::where('status', true)->pluck('name', 'slug')->toArray();
        });
    }

    public static function getPluginPrefix($slug): ?string
    {
        $plugin = self::select('prefix')->where('slug', $slug)->first();

        return $plugin ? $plugin->prefix : null;
    }

}

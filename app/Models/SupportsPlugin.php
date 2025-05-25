<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportsPlugin extends Model
{
    protected $table = 'appza_supports_plugin';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['created_at','updated_at'];
    protected $fillable = [
        'name',
        'slug',
        'prefix',
        'image',
        'title',
        'description',
        'others',
        'is_disable',
        'sort_order',
        'status'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public static function getPluginDropdown(){
        return cache()->remember('plugin_dropdown', 60, function () {
            return self::where('status', true)->pluck('name', 'slug')->toArray();
        });
    }

    /*public static function getPluginPrefix($slug): ?string
    {
        $plugin = self::select('prefix')->where('slug', $slug)->first();

        return $plugin ? $plugin->prefix : null;
    }*/

    public static function getPluginPrefix($slug)
    {
        return self::where('slug', $slug)->value('prefix');
    }

}

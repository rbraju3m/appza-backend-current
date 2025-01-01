<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassType extends Model
{
    protected $table = 'appza_class_type';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at'];

    // Cast JSON field to array
    protected $casts = [
        'plugin' => 'array',
    ];

    // Specify mass assignable attributes
    protected $fillable = ['name', 'slug', 'plugin', 'is_active'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getDropdown($plugin = null)
    {
        $data = self::active()->whereJsonContains('plugin',$plugin)->pluck('name', 'slug')->toArray();
        return $data;
    }
}

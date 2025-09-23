<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FluentInfo extends Model
{

    protected $table = 'appza_fluent_informations';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $dates = ['created_at','updated_at'];

    public static function getProductDropdown(): array
    {
        return self::pluck('product_name', 'id')->all();
    }
    public static function getProductTab()
    {
        return self::where('is_active', 1)
            ->get(['id', 'product_name', 'product_slug'])
            ->keyBy('product_slug');
    }
}

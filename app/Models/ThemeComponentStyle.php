<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeComponentStyle extends Model
{
    use HasFactory;

    protected $table = 'appfiy_theme_component_style';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = ['theme_id','theme_component_id', 'name', 'input_type', 'value','style_group_id'];
}

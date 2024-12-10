<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThemeConfig extends Model
{
    use HasFactory;

    protected $table = 'appfiy_theme_config';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at', 'updated_at'];
    protected $fillable = ['theme_id', 'global_config_id', 'mode', 'name', 'slug', 'background_color', 'layout', 'icon_theme_size', 'icon_theme_color', 'shadow', 'icon', 'automatically_imply_leading', 'center_title', 'flexible_space', 'bottom', 'shape_type', 'shape_border_radius', 'toolbar_opacity', 'actions_icon_theme_color', 'actions_icon_theme_size', 'title_spacing'];
}

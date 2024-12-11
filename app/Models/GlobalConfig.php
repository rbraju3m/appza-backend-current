<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class GlobalConfig extends Model
{
    use HasFactory;

    protected $table = 'appfiy_global_config';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at','updated_at'];
    protected $fillable = ['mode', 'name', 'slug','selected_color','unselected_color', 'background_color', 'layout', 'icon_theme_size', 'icon_theme_color', 'shadow', 'icon', 'automatically_imply_leading', 'center_title', 'flexible_space', 'bottom', 'shape_type', 'shape_border_radius', 'toolbar_opacity', 'actions_icon_theme_color', 'actions_icon_theme_size', 'title_spacing', 'is_active','image','text_properties_color','icon_properties_size','icon_properties_color','icon_properties_shape_radius','icon_properties_background_color','padding_x','padding_y','margin_x','margin_y','image_properties_height','image_properties_width','image_properties_shape_radius','image_properties_padding_x','image_properties_padding_y','image_properties_margin_x','image_properties_margin_y','icon_properties_padding_x','icon_properties_padding_y','icon_properties_margin_x','icon_properties_margin_y','is_upcoming','float','currency_id','plugin_slug'];


    public static function getDropdown($mode,$pluginSlug){
        return self::where([['appfiy_global_config.is_active', 1], ['appfiy_global_config.is_upcoming', 0]])
            ->join('appza_supports_plugin', 'appza_supports_plugin.slug', '=', 'appfiy_global_config.plugin_slug')
            ->where('appfiy_global_config.mode', $mode)
            ->where('appfiy_global_config.plugin_slug',$pluginSlug)
            ->pluck(DB::raw("CONCAT(appfiy_global_config.name, ' (', appza_supports_plugin.name, ')') as name"), 'appfiy_global_config.id');
    }

    public function currency()
    {
        $this->hasOne(Currency::class, 'currency_id', 'id');
    }
}

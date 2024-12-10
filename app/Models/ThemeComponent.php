<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeComponent extends Model
{
    use HasFactory;

    protected $table = 'appfiy_theme_component';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = ['theme_id', 'parent_id', 'component_parent_id', 'component_id', 'theme_config_id', 'theme_page_id', 'display_name', 'clone_component', 'selected_id','sort_ordering'];

    public function component(){
        return $this->belongsTo(Component::class,'component_id','id');
    }
}

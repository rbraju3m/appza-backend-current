<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Component extends Model
{
    use SoftDeletes,Sluggable;

    protected $table = 'appfiy_component';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at','updated_at'];
    protected $fillable = ['parent_id', 'name', 'slug', 'label', 'layout_type_id', 'icon_code', 'event', 'scope', 'class_type','app_icon','web_icon','image','product_type','selected_design','details_page','transparent','image_url','is_active','component_type_id','is_upcoming','is_multiple','plugin_slug','items','dev_data','pagination','filters','show_no_data_view'];

    protected $casts = [
        'items' => 'array',
        'dev_data' => 'array',
        'filters' => 'array',
        'pagination' => 'array',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function componentStyleGroup(){
        return $this->hasMany(ComponentStyleGroup::class,'component_id','id');
    }
    public function componentLayout(){
        return $this->belongsTo(LayoutType::class,'layout_type_id','id');
    }
}

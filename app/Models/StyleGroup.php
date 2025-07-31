<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StyleGroup extends Model
{
    use SoftDeletes;

    protected $table = 'appfiy_style_group';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at','updated_at'];
    protected $fillable = ['name', 'slug','is_active','plugin_slug'];

    protected $casts = [
        'plugin_slug' => 'array', // Automatically cast JSON to array
    ];


    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->created_at = now();
            $model->is_active = 1;
        });

        self::updating(function ($model) {
            $model->updated_at = now();
        });
    }

    public function groupProperties(){
        return $this->hasMany(StyleGroupProperties::class,'style_group_id','id');
    }

    public static function getPropertiesNameArray($id){
        $getAllProperties = StyleGroupProperties::where('appfiy_style_group_properties.style_group_id',$id)->join('appfiy_style_properties','appfiy_style_properties.id','=','appfiy_style_group_properties.style_property_id')->select(['appfiy_style_properties.name'])->get()->toArray();
        $data = '';
        $array = [];
        if (count($getAllProperties)>0){
            foreach ($getAllProperties as $pro){
                array_push($array,$pro['name']);
            }
            $data = implode('<br>',$array);
        }
        return $data;
    }
}

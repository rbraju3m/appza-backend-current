<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StyleGroup extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'appfiy_style_group';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at','updated_at'];
    protected $fillable = ['name', 'slug',];

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

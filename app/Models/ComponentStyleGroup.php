<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComponentStyleGroup extends Model
{
    use HasFactory;

    protected $table = 'appfiy_component_style_group';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['created_at','updated_at'];
    protected $fillable = ['component_id', 'style_group_id'];

    public function componentStyleGroup(){
        return $this->belongsTo(ComponentStyleGroup::class,'component_id','id');
    }

    public function styleGroup()
    {
        return $this->belongsTo(StyleGroup::class, 'style_group_id', 'id'); // 'style_group_id' is the foreign key
    }
}

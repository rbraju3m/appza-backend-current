<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StyleGroupProperties extends Model
{
    use HasFactory;

    protected $table = 'appfiy_style_group_properties';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['created_at','updated_at'];
    protected $fillable = ['style_group_id', 'style_property_id',];

}

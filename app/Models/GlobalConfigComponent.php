<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalConfigComponent extends Model
{
    use HasFactory;

    protected $table = 'appfiy_global_config_component';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['created_at','updated_at'];
    protected $fillable = ['global_config_id', 'component_id', 'component_position'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FluentInfo extends Model
{

    protected $table = 'appza_fluent_informations';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $dates = ['created_at','updated_at'];
}

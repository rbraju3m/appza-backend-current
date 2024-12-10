<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currency';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = ['id','symbol'];


    public static function getDropdown(){
        return self::where([['is_active',1]])->select(DB::raw("CONCAT(country, ' -- ', currency) AS name"),'id')->pluck('name','id');

    }
}

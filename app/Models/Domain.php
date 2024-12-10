<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'appfiy_domain';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at','created_at', 'updated_at'];
    protected $fillable = ['name', 'licence_key', 'description', 'expire_date'];

    public static function checkAuthorization($licenceKey){
        $domain = self::where('is_active',1)->where('licence_key',$licenceKey)->first();
        if ($domain){
            if ($domain->expire_date>=date('Y-m-d')){
                $data = [
                    'domain' => $domain,
                    'auth_type' => true
                ];
                return $data;
            }else{
                $data = [
                    'domain' => '',
                    'auth_type' => false
                ];
                return $data;
            }
        }
        return [
            'domain' => '',
            'auth_type' => false
        ];
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;

class Lead extends Model
{
    use HasFactory;

    protected $table = 'appfiy_customer_leads';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = [
        'first_name', 'last_name', 'email', 'mobile', 'domain',
        'customer_id', 'license_id', 'note', 'appza_hash', 'plugin_name'
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->created_at = now();
        });

        self::updating(function ($model) {
            $model->updated_at = now();
        });
    }

    public function scopeActiveAndOpen($query)
    {
        return $query->where('is_active', 1)->where('is_close', 0);
    }

    public static function checkAuthorization(Request $request): array
    {
        // Determine hash from request headers
        /*$hashCode = $request->header('lazy-task-hash')
            ?? $request->header('appza-hash')
            ?? $request->header('Fcom-mobile-hash');*/

        $hashCode = collect([
            $request->header('lazy-task-hash'),
            $request->header('appza-hash'),
            $request->header('Fcom-mobile-hash'),
        ])->filter()->first();


        // Default unauthorized response
        $defaultResponse = [
            'domain' => '',
            'plugin_name' => '',
            'customer_name' => '',
            'email' => '',
            'auth_type' => false,
        ];

        // Return unauthorized response if no hash is provided
        if (!$hashCode) {
            return $defaultResponse;
        }

        // Retrieve the lead with active/open status
        $lead = self::activeAndOpen()->where('appza_hash', $hashCode)->first();

        // If no matching lead, return unauthorized response
        if (!$lead) {
            return $defaultResponse;
        }

        // Return authorized response with lead details
        return [
            'domain' => $lead->domain,
            'plugin_name' => $lead->plugin_name,
            'customer_name' => $lead->first_name . ' ' . $lead->last_name,
            'email' => $lead->email,
            'auth_type' => true,
        ];
    }
}

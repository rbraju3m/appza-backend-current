<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $table = 'appza_request_logs';
    protected $fillable = [
        'method',
        'url',
        'headers',
        'request_data',
        'response_status',
        'response_data',
        'ip_address',
        'user_agent',
        'user_id',
        'execution_time',
    ];

    protected $casts = [
        'headers' => 'array',
        'request_data' => 'array',
        'response_data' => 'array',
        'execution_time' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

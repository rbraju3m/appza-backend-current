<?php

namespace App\Models;

use App\Enums\BuildStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_name',
        'app_name',
        'domain',
        'base_suffix',
        'base_url',
        'build_number',
        'icon_url',
        'build_target',
        'jks_url',
        'key_properties_url',
        'key_properties',
        'issuer_id',
        'key_id',
        'api_key_url',
        'team_id',
        'app_identifier',
        'status',
        'apk_url',
        'build_message'
    ];

    protected $casts = [
        'key_properties' => 'json',
        'status' => BuildStatus::class,
    ];
}
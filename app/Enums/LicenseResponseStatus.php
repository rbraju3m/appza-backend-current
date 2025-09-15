<?php

namespace App\Enums;

enum LicenseResponseStatus: string
{
    case Invalid = 'invalid';
    case Active = 'active';
    case Expired = 'expired';
}

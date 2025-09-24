<?php

namespace App\Enums;

enum LicenseResponseStatus: string
{
    // for license check
    case Invalid = 'invalid';
    case Active = 'active';
    case Expired = 'expired';

    // for license activation
    case Activate = 'activate';
    case Suspended = 'suspended';
}

<?php

namespace App\Enums;

enum UserType: string
{
    case World = 'world';
    case Vendor = 'vendor';
    case NormalUser = 'normal_user';
}

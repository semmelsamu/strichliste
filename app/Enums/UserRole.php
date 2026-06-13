<?php

namespace App\Enums;

enum UserRole: string
{
    case TallyUser = 'tally_user';
    case Admin = 'admin';
    case Vendor = 'vendor';
    case World = 'world';
}

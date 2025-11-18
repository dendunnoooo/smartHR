<?php

namespace App\Enums;

enum UserType: string
{
    case SYSTEM_ADMIN = 'System Admin';
    case SUPERADMIN = 'Super Admin';
    case EMPLOYEE = 'Employee';
    case CLIENT = 'Client';
}

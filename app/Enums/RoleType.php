<?php

namespace App\Enums;

enum RoleType: string
{
    case MIS = 'mis';
    case USER = 'user';
    case ADMIN = 'admin';
} 
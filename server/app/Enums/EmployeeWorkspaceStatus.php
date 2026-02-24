<?php

namespace App\Enums;

enum EmployeeWorkspaceStatus: string
{
    case Pending  = 'pending';
    case Active   = 'active';
    case Expired  = 'expired';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

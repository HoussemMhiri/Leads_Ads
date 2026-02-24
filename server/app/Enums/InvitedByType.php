<?php

namespace App\Enums;

enum InvitedByType: string
{
    case Owner    = 'owner';
    case Employee = 'employee';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

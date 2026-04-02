<?php

namespace App\Enums;

enum MasterStatus: string
{
    case ACTIVE     = 'ACTIVE';
    case INACTIVE   = 'INACTIVE';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE    => 'Active',
            self::INACTIVE  => 'Inactive',
        };
    }
}
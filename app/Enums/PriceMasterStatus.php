<?php

namespace App\Enums;

enum PriceMasterStatus: string
{
    case ACTIVE     = 'ACTIVE';
    case INACTIVE   = 'INACTIVE';
    case SUPERSEDED = 'SUPERSEDED';
    case SCHEDULED  = 'SCHEDULED';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE     => 'Active',
            self::INACTIVE   => 'Inactive',
            self::SUPERSEDED => 'Superseded',
            self::SCHEDULED  => 'Scheduled',
        };
    }
}
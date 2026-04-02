<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case DRAFT     = 'DRAFT';
    case APPROVED  = 'APPROVED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT     => 'Draft',
            self::APPROVED  => 'Approved',
            self::CANCELLED => 'Cancelled',
        };
    }
}
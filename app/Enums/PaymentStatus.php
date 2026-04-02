<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case OUTSTANDING = 'OUTSTANDING';
    case PAID        = 'PAID';        

    public function label(): string
    {
        return match ($this) {
            self::OUTSTANDING => 'Outstanding',
            self::PAID        => 'Paid',
        };
    }
}

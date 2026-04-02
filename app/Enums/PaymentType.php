<?php

namespace App\Enums;

enum PaymentType: string
{
    case INCENTIVE   = 'INCENTIVE';
    case DIESEL_BILL = 'DIESEL_BILL';

    public function label(): string
    {
        return match ($this) {
            self::INCENTIVE   => 'Incentive',
            self::DIESEL_BILL => 'Diesel Bill',
        };
    }
}

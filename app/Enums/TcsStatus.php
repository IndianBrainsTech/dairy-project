<?php

namespace App\Enums;

enum TcsStatus: string
{
    case NOT_APPLICABLE = 'NOT_APPLICABLE';
    case APPLICABLE     = 'APPLICABLE';
    case APPLIED        = 'APPLIED';

    public function label(): string
    {
        return match ($this) {
            self::NOT_APPLICABLE => 'TCS Not Applicable',
            self::APPLICABLE     => 'TCS Applicable',
            self::APPLIED        => 'Already in TCS',
        };
    }
}

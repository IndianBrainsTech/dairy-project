<?php

namespace App\Enums;

enum TdsStatus: string
{
    case NOT_APPLICABLE = 'NOT_APPLICABLE';
    case APPLICABLE     = 'APPLICABLE';    
    case APPLIED        = 'APPLIED';

    public function label(): string
    {
        return match ($this) {
            self::NOT_APPLICABLE => 'TDS Not Applicable',
            self::APPLICABLE     => 'TDS Applicable',            
            self::APPLIED        => 'Already in TDS',
        };
    }
}

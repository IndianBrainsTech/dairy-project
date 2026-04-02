<?php

namespace App\Enums;

enum GstType: string
{
    case INTRASTATE_REGISTERED   = 'INTRASTATE_REGISTERED';
    case INTRASTATE_UNREGISTERED = 'INTRASTATE_UNREGISTERED';
    case INTERSTATE_REGISTERED   = 'INTERSTATE_REGISTERED';
    case INTERSTATE_UNREGISTERED = 'INTERSTATE_UNREGISTERED';

    public function label(): string
    {
        return match ($this) {
            self::INTRASTATE_REGISTERED   => 'Intrastate Registered (Tamilnadu)',
            self::INTRASTATE_UNREGISTERED => 'Intrastate Unregistered (Tamilnadu)',
            self::INTERSTATE_REGISTERED   => 'Interstate Registered (Other State)',
            self::INTERSTATE_UNREGISTERED => 'Interstate Unregistered (Other State)',
        };
    }
}

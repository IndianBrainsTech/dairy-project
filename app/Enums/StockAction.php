<?php

namespace App\Enums;

enum StockAction: string
{
    case OPENING      = 'OPENING';
    case PRODUCTION   = 'PRODUCTION';
    case SALES        = 'SALES';
    case RETURN       = 'RETURN';
    case RETURN_ORDER = 'RETURN_ORDER';
}

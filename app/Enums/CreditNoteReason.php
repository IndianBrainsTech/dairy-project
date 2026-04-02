<?php

namespace App\Enums;

enum CreditNoteReason: string
{
    case DISCOUNT  = 'DISCOUNT';
    case BAD_DEBT  = 'BAD_DEBT';
    case ROUND_OFF = 'ROUND_OFF';
    // case SALES_RETURN      = 'SALES_RETURN';
    // case DAMAGED_GOODS     = 'DAMAGED_GOODS';
    // case CANCELLED_INVOICE = 'CANCELLED_INVOICE';

    public function label(): string
    {
        return match ($this) {
            self::DISCOUNT  => 'Discount',
            self::BAD_DEBT  => 'Bad Debt',
            self::ROUND_OFF => 'Round Off',
            // self::SALES_RETURN      => 'Sales Return',
            // self::DAMAGED_GOODS     => 'Damaged Goods',
            // self::CANCELLED_INVOICE => 'Cancelled Invoice',
        };
    }
}
<?php

namespace App\Enums;

enum TaxType: string
{
    case TAXABLE  = 'TAXABLE';
    case EXEMPTED = 'EXEMPTED';        

    public function label(): string
    {
        return match ($this) {
            self::TAXABLE  => 'Taxable',
            self::EXEMPTED => 'Exempted',
        };
    }
}

<?php

namespace App\Models\Masters\Purchase;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Taxtype;

class HsnCode extends Model
{
    use HasFactory;

    protected $table = 'hsn_codes';

    protected $fillable = [
        'hsn_code',
        'tax_type',
        'gst',
        'sgst',
        'cgst',
        'igst',
    ];

    protected $casts = [
        'tax_type' => TaxType::class,
        'gst'  => 'decimal:2',
        'sgst' => 'decimal:2',
        'cgst' => 'decimal:2',
        'igst' => 'decimal:2',
    ];

    /**
     * Scope: Only taxable HSN codes
     */
    public function scopeTaxable(Builder $query): Builder
    {
        return $query->where('tax_type', self::TAXABLE);
    }

    /**
     * Scope: Only exempted HSN codes
     */
    public function scopeExempted(Builder $query): Builder
    {
        return $query->where('tax_type', self::EXEMPTED);
    }

    /**
     * Helper: Check if taxable
     */
    public function isTaxable(): bool
    {
        return $this->tax_type === self::TAXABLE;
    }
}

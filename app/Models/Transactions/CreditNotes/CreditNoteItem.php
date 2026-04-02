<?php

namespace App\Models\Transactions\CreditNotes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transactions\CreditNotes\CreditNote;
use App\Models\Transactions\CreditNotes\CreditNoteItemHistory;

class CreditNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_note_id',
        'invoice_number',
        'invoice_date',
        'invoice_amount',
        'paid_amount',
        'outstanding_amount',
        'adjusted_amount',
    ];

    protected $casts = [
        'document_date'      => 'date',
        'invoice_amount'     => 'decimal:2',
        'paid_amount'        => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'adjusted_amount'    => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(CreditNoteItemHistory::class, 'record_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */
    public function getInvoiceNumberAttribute($value)
    {
        if (str_ends_with($value, ' - OpeningAmt')) {
            return 'Opening Amount';
        }

        return $value;
    }
}

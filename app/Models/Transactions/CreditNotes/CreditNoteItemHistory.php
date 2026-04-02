<?php

namespace App\Models\Transactions\CreditNotes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transactions\CreditNotes\CreditNote;
use App\Models\User;
use App\Enums\SqlAction;

class CreditNoteItemHistory extends Model
{
    use HasFactory;

    protected $table = 'credit_note_items_history';

    protected $fillable = [
        'credit_note_id',
        'invoice_number',
        'invoice_date',
        'invoice_amount',
        'paid_amount',
        'outstanding_amount',
        'adjusted_amount',
        'version_code',
        'record_id',
        'user_id',        
        'sql_action',
    ];

    protected $casts = [
        'invoice_amount'     => 'decimal:2',
        'paid_amount'        => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'adjusted_amount'    => 'decimal:2',
        'version_code'       => 'integer',
        'sql_action'         => SqlAction::class,
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(CreditNoteItem::class, 'record_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeByVersion($query, $versionCode)
    {
        return $query->where('version_code', $versionCode);
    }
}

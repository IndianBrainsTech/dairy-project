<?php

namespace App\Models\Transactions\CreditNotes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transactions\CreditNotes\CreditNote;
use App\Models\Transactions\CreditNotes\CreditNoteItemHistory;
use App\Models\Profiles\Customer;
use App\Models\User;
use App\Enums\SqlAction;
use App\Enums\CreditNoteReason;

class CreditNoteHistory extends Model
{
    use HasFactory;

    protected $table = 'credit_notes_history';

    protected $fillable = [
        'credit_note_id',
        'document_number',
        'document_date',
        'customer_id',
        'reason',
        'narration',
        'amount',
        'version_code',
        'user_id',        
        'sql_action',
    ];

    protected $casts = [
        'document_date' => 'date',
        'reason'        => CreditNoteReason::class,
        'amount'        => 'decimal:2',
        'version_code'  => 'integer',
        'sql_action'    => SqlAction::class,
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItemHistory::class, 'credit_note_id', 'credit_note_id')
            ->whereColumn('version_code', 'credit_notes_history.version_code');
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

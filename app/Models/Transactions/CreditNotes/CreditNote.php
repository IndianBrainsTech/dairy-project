<?php

namespace App\Models\Transactions\CreditNotes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transactions\CreditNotes\CreditNoteItem;
use App\Models\Transactions\CreditNotes\CreditNoteHistory;
use App\Models\Transactions\CreditNotes\CreditNoteItemHistory;
use App\Models\Profiles\Customer;
use App\Models\User;
use App\Enums\DocumentStatus;
use App\Enums\CreditNoteReason;

class CreditNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_number',
        'document_date',
        'customer_id',
        'reason',
        'narration',
        'amount',
        'status',
        'cancel_remarks',
        'current_version',
        'created_by',
        'updated_by',
        'actioned_by',
        'actioned_at',
    ];

    protected $casts = [
        'document_date'   => 'date',
        'reason'          => CreditNoteReason::class,
        'amount'          => 'decimal:2',
        'status'          => DocumentStatus::class,
        'current_version' => 'integer',
        'actioned_at'     => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function actioner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(CreditNoteHistory::class);
    }

    public function itemHistories(): HasMany
    {
        return $this->hasMany(CreditNoteItemHistory::class);
    }
    
    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeDraft($query)
    {
        return $query->where('status', DocumentStatus::DRAFT);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', DocumentStatus::APPROVED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', DocumentStatus::CANCELLED);
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Attributes
    |--------------------------------------------------------------------------
    */

    protected function documentDateForForm(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->document_date?->toDateString()
        );
    }

    protected function documentDateForDisplay(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->document_date?->format('d-m-Y')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($record) {
            $record->created_by = auth()->id();
            $record->created_at = now();
        });

        static::updating(function ($record) {
            $record->updated_by = auth()->id();
            $record->updated_at = now();
        });
    }
}

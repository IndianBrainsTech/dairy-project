<?php

namespace App\Models\Masters\Purchase;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\Places\State;
use App\Models\Masters\Bank;
use App\Models\Masters\BankBranch;
use App\Models\User;
use App\Enums\GstType;
use App\Enums\TcsStatus;
use App\Enums\TdsStatus;
use App\Enums\MasterStatus;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        // Profile
        'name',
        'code',
        'address',
        'city',
        'state_id',
        'landmark',
        'pin_code',
        'contact_number',
        'email',

        // Finance
        'credit_limit',
        'credit_days',
        'gst_type',
        'gstin',
        'pan',
        'payment_terms',
        'tcs_status',
        'tds_status',

        // Banking
        'bank_id',
        'branch_id',
        'ifsc',
        'account_holder',
        'account_number',

        // Integration & Status
        'tally_sync_status',
        'status',

        // Audit
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_days'  => 'integer',
        'gst_type'     => GstType::class,
        'tcs_status'   => TcsStatus::class,
        'tds_status'   => TdsStatus::class,
        'status'       => MasterStatus::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BankBranch::class, 'branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeSynced($query)
    {
        return $query->where('tally_sync_status', 'SYNCED');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->created_by = auth()->id();            
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }
}

<?php

namespace App\Models\Masters\Purchase\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\MasterStatus;
use App\Enums\TallySyncStatus;
use App\Models\User;

class PurchaseItemUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviation',
        'hot_key',
        'tally_sync_status',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tally_sync_status' => TallySyncStatus::class,
        'status' => MasterStatus::class,
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope: Active records only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * Mark record as synced with Tally.
     */
    public function markSynced(): void
    {
        $this->tally_sync_status = 'SYNCED';
        $this->save();
    }
}

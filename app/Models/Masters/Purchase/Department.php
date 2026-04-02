<?php

namespace App\Models\Masters\Purchase;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\MasterStatus;
use App\Enums\TallySyncStatus;
use App\Models\User;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
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
}

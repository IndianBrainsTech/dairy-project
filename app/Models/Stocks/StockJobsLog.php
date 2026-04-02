<?php

namespace App\Models\Stocks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockJobsLog extends Model
{
    use HasFactory;

    protected $table = 'stock_jobs_log';

    protected $fillable = [
        'job_date',
        'status',
        'processed_count',
        'message',
    ];

    /**
     * Check if job succeeded.
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if job failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get a short status label (for UI badges).
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->isSuccess() ? '✅ Success' : '❌ Failed';
    }
}

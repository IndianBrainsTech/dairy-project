<?php

namespace App\Models\Stocks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Status;
use App\Models\User;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_number',
        'document_date',
        'status',
        'remarks',
        'created_by',
        'updated_by',
        'actioned_by',
        'actioned_at',
    ];

    protected $casts = [
        'status' => Status::class,
    ];

    /**
     * Get the user who created the stock.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the stock.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who actioned the stock.
     */
    public function actionedBy()
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    /**
     * Get the items associated with the stock.
     */
    public function items()
    {
        return $this->hasMany(StockItem::class);
    }

    /**
     * Get the history records of the stock.
     */
    public function history()
    {
        return $this->hasMany(StockHistory::class);
    }
}

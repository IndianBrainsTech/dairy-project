<?php

namespace App\Models\Masters\Pricing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Status;

class PriceAdjustment extends Model
{
    use HasFactory;

    protected $table = 'price_adjustments';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [        
        'document_date',        
        'user_id',
        'adjustment_data',
        'masters_list',
        'effect_date',
        'status',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'document_date'   => 'date',
        'adjustment_data' => 'array',
        'masters_list'    => 'array',
        'effect_date'     => 'date',
        'status'          => Status::class,
    ];
    
    /**
     * Default attribute values.
     */
    protected $attributes = [
        'status' => Status::PENDING,
    ];
}
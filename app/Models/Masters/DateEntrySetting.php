<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DateEntrySetting extends Model
{
    use HasFactory;
    protected $table = 'date_entry_settings';

    protected $fillable = [
        'module',
        'tag',
        'days_before',
        'days_after',        
    ];

    protected $casts = [
        'days_before' => 'integer',
        'days_after'  => 'integer',        
    ];
}

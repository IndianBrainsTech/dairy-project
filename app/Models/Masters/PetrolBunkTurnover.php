<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transport\PetrolBunk;

class PetrolBunkTurnover extends Model
{
    use HasFactory;
    protected $table = "petrol_bunk_turnover";
    protected $primaryKey = 'id';
    protected $fillable = ['bunk_id','amount','reference_date','status'];

    protected $casts = [
        'reference_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function bunk()
    {
        return $this->belongsTo(PetrolBunk::class, 'bunk_id');
    }
}

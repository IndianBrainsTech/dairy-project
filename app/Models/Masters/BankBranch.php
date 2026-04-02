<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\Bank;

class BankBranch extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['bank_id','name','ifsc'];

    /**
     * Get the bank that owns the branch.
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}

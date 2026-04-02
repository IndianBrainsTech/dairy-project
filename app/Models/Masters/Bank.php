<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\BankBranch;

class Bank extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['name','short_name'];

    /**
     * Get the branches for the bank.
     */
    public function branches()
    {
        return $this->hasMany(BankBranch::class);
    }
}

<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\Bank;
use App\Models\Masters\BankBranch;
use App\Enums\TdsStatus;

class PetrolBunk extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'petrol_bunks';    
    protected $fillable = ['name','code','address','pin_code',
        'contact_number','email','pan','gst_number','tds_status',
        'bank_id','branch_id','account_holder','account_number'];

    protected $casts = [
        'tds_status' => TdsStatus::class,
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function branch()
    {
        return $this->belongsTo(BankBranch::class, 'branch_id');
    }

    public function dieselBills()
    {
        return $this->hasMany(DieselBill::class, 'bunk_id');
    }
}

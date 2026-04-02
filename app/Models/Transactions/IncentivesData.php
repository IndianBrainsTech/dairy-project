<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Places\MRoute;

class IncentivesData extends Model
{
    use HasFactory;
    protected $table = "incentives_datas";
    protected $guarded = [];
    
    public function route()
    {
        return $this->belongsTo(MRoute::class, 'route_id', 'id');
    }

}

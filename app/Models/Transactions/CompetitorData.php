<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitorData extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['enquiry_id','competitor_id','product_data','offers','remarks'];
    // protected $casts = ['product_data' => 'array'];

    public function competitor()
    {
        return $this->belongsTo('App\Models\Profiles\Competitor','competitor_id','id');
    }
}

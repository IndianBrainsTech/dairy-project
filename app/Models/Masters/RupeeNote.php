<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RupeeNote extends Model
{
    use HasFactory;
    protected $table = "rupee_notes"; 
    protected $primaryKey = 'id';
    protected $fillable = ['note_value','display_index'];
}

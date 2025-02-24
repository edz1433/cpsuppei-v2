<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
    use HasFactory;
    protected $table = 'repairs';
    protected $fillable = ['inv_id','issue','remarks','status','urgency'];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}


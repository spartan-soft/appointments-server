<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'reservation_id'
    ];

     public function service()
    {
        return $this->belongsTo(Reservation::class);
    }
    
}

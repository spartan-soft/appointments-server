<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'service_id',
        'amount',
        'reservation_time',
        'reservation_end_time',
    ];
    public $timestamps = false; // Indicar que no se inserten valores en created_at y updated_at


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    
    public function payments(){
        return $this->hasMany(Payment::class);
    }
}

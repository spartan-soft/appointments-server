<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lastName',
        'phone',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
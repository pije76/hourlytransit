<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelCounter extends Model
{
    use HasFactory;
    protected $fillable = [
        'hotel_id',
        'key'
    ];
}

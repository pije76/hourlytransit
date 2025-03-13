<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HourlyRoomPrice extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_id',
        'hotel_id',
        'room_id',
        'hour_id',
        'price',
    ];
}

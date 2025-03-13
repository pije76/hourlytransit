<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'name',
        'code',
        'type',
        'value',
        'start_date',
        'end_date',
        'rooms'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomFeature extends Model
{
    use HasFactory;
    protected $fillable = [
        'room_id',
        'vendor_id',
        'order_number',
        'total',
        'payment_method',
        'gateway_type',
        'payment_status',
        'order_status',
        'attachment',
        'invoice',
        'days',
        'start_date',
        'end_date',
        'currency_symbol',
        'currency_symbol_position',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }
}

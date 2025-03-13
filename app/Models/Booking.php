<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'order_number',
        'user_id',
        'hotel_id',
        'room_id',
        'vendor_id',
        'membership_id',
        'check_in_date',
        'check_in_time',
        'check_out_date',
        'check_out_time',
        'preparation_time',
        'next_booking_time',
        'booking_name',
        'booking_email',
        'booking_phone',
        'booking_address',
        'total',
        'discount',
        'tax',
        'grand_total',
        'currency_text',
        'currency_text_position',
        'currency_symbol',
        'currency_symbol_position',
        'payment_method',
        'payment_status',
        'gateway_type',
        'order_status',
        'attachment',
        'invoice',
        'hour',
        'serviceCharge',
        'roomPrice',
        'adult',
        'children',
        'additional_service',
        'service_details',
        'check_in_date_time',
        'check_out_date_time',
        'conversation_id',
    ];
    public function hotelRoom()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelFeature extends Model
{
    use HasFactory;
    protected $fillable = [
        'hotel_id',
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

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id', 'id');
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }
}

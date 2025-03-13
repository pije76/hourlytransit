<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelCounterContent extends Model
{
    use HasFactory;
    protected $fillable = [
        'language_id',
        'hotel_counter_id',
        'label',
        'value'
    ];
}

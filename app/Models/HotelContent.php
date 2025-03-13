<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelContent extends Model
{
    use HasFactory;
    protected $fillable = [
        'language_id',
        'hotel_id',
        'category_id',
        'country_id',
        'state_id',
        'city_id',
        'title',
        'slug',
        'address',
        'amenities',
        'description',
        'meta_keyword',
        'meta_description',
    ];
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_id',
        'logo',
        'average_rating',
        'latitude',
        'longitude',
        'status',
        'max_price',
        'min_price',
        'stars'
    ];
    public function hotel_contents()
    {
        return $this->hasMany(HotelContent::class, 'hotel_id', 'id');
    }
    public function holidays()
    {
        return $this->hasMany(Holiday::class, 'hotel_id', 'id');
    }
    public function room()
    {
        return $this->hasMany(Room::class, 'hotel_id', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function hotel_galleries()
    {
        return $this->hasMany(HotelImage::class, 'hotel_id', 'id');
    }
    public function hotel_feature()
    {
        return $this->hasOne(HotelFeature::class, 'hotel_id', 'id');
    }
}

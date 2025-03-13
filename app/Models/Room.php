<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'hotel_id',
        'feature_image',
        'average_rating',
        'latitude',
        'longitude',
        'status',
        'bed',
        'bathroom',
        'number_of_rooms_of_this_same_type',
        'preparation_time',
        'prices',
        'area',
        'adult',
        'children',
        'max_price',
        'min_price',
    ];

    public function room_content()
    {
        return $this->hasMany(RoomContent::class, 'room_id', 'id');
    }
    public function room_feature()
    {
        return $this->hasOne(RoomFeature::class, 'room_id', 'id');
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function room_galleries()
    {
        return $this->hasMany(RoomImage::class, 'room_id', 'id');
    }

    public function room_prices()
    {
        return $this->hasMany(HourlyRoomPrice::class, 'room_id', 'id');
    }
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

}

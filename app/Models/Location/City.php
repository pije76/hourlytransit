<?php

namespace App\Models\Location;

use App\Models\HotelContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    protected $fillable = [
        'language_id',
        'country_id',
        'feature_image',
        'state_id',
        'name'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
    public function hotel_city()
    {
        return $this->hasMany(HotelContent::class, 'city_id');
    }
}

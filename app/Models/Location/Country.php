<?php

namespace App\Models\Location;

use App\Models\HotelContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'language_id',
        'name'
    ];
    
    public function cities()
    {
        return $this->hasMany(City::class, 'country_id');
    }
    public function states()
    {
        return $this->hasMany(State::class, 'country_id');
    }
    
    public function hotel_country()
    {
        return $this->hasMany(HotelContent::class, 'country_id');
    }
}

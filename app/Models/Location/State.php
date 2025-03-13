<?php

namespace App\Models\Location;

use App\Models\HotelContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;
    protected $fillable = [
        'language_id',
        'country_id',
        'name'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    public function cities()
    {
        return $this->hasMany(City::class, 'state_id');
    }
    public function hotel_state()
    {
        return $this->hasMany(HotelContent::class, 'state_id');
    }
}

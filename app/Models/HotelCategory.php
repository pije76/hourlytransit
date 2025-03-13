<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'language_id',
        'name',
        'slug',
        'serial_number',
        'status'
    ];

    public function hotel_category()
    {
        return $this->hasMany(HotelContent::class, 'category_id');
    }
}

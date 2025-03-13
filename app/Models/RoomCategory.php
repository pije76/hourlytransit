<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'language_id',
        'serial_number',
        'name',
        'status',
        'slug'
    ];

    public function room_category()
    {
        return $this->hasMany(RoomContent::class, 'room_category');
    }
}

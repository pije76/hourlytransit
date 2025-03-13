<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomReview extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'room_id',
        'hotel_id',
        'rating',
        'review',
    ];
    public function userInfo()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function listingInfo()
    {
        return $this->belongsTo(Room::class);
    }
}

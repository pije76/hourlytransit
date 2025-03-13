<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'price',
        'term',
        'status',
        'number_of_car_featured',
        'number_of_listing',
        'number_of_hotel',
        'number_of_amenities_per_room',
        'number_of_room',
        'number_of_amenities_per_hotel',
        'number_of_bookings',
        'number_of_images_per_hotel',
        'number_of_images_per_room',
        'number_of_products',
        'slug',
        'custom_features',
        'features',
        'number_of_faq',
        'number_of_social_links',
        'number_of_additional_specification',
        'icon',
        'recommended'
    ];

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }
}

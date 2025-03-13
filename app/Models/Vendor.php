<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Vendor extends Model implements AuthenticatableContract
{
    use HasFactory, Authenticatable;

    protected $fillable = [
        'photo',
        'email',
        'to_mail',
        'phone',
        'username',
        'password',
        'status',
        'amount',
        'facebook',
        'twitter',
        'linkedin',
        'avg_rating',
        'email_verified_at',
        'show_email_addresss',
        'show_phone_number',
        'show_contact_form',
        'lang_code',
        'code',
        
    ];

    public function vendor_infos()
    {
        return $this->hasMany(VendorInfo::class, 'vendor_id', 'id');
    }
    public function vendor_info()
    {
        return $this->hasOne(VendorInfo::class);
    }

    //support ticket
    public function support_ticket()
    {
        return $this->hasMany(SupportTicket::class, 'vendor_id', 'id');
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function hotels()
    {
        return $this->hasMany(Hotel::class, 'vendor_id', 'id');
    }
}

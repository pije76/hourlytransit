<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalServiceContent extends Model
{
    use HasFactory;
    protected $fillable = [
        'additional_service_id',
        'title',
        'language_id',

    ];
}

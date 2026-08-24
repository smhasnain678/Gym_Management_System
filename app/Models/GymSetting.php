<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GymSetting extends Model
{
    protected $fillable = [
        'gym_name',
        'gym_logo',
        'primary_color',
        'secondary_color',
        'brand_split_position',
        'owner_name',
        'contact_email',
        'contact_phone',
        'address',
        'country',
        'city',
        'currency',
        'currency_symbol',
        'timezone',
        'language',
        'theme',
        'date_format',
        'time_format',
    ];
}

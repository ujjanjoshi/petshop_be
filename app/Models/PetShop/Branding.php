<?php

namespace App\Models\PetShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branding extends Model
{
    use HasFactory;
    protected $connection = 'mysql_pet_shop';
    protected $table = 'brandings';
    protected $fillable = [
        'header_logo',
        'footer_logo',
        'header_color',
        'footer_color',
        'address',
        'phone_number',
        'trade_mark',
        'term_policy',
        'linkedin_url',
        'twitter_url',
        'facebook_url',
    ];
}

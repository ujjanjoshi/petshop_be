<?php

namespace App\Models\PetShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PetShopToken extends SanctumPersonalAccessToken
{
    use HasFactory;
    protected $connection = 'mysql_pet_shop';
}

<?php

namespace App\Models\PetShop;

use Illuminate\Database\Eloquent\Model;

class UserPetPoints extends Model
{
    protected $connection = 'mysql_pet_shop';
    protected $table='user_pet_points';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'pet_points'
    ];
}

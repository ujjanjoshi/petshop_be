<?php

namespace App\Models\PetShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotAllowedRental extends Model
{
    use HasFactory;
    protected $connection = 'mysql_pet_shop';
    protected $table = 'not_allowed_rentals';
    protected $fillable = ['rental_id'];
}

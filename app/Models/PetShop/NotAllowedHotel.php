<?php

namespace App\Models\PetShop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotAllowedHotel extends Model
{
    use HasFactory;
    protected $table = 'not_allowed_hotels';
    protected $connection = 'mysql_pet_shop';
    protected $fillable = [
       'hotel_id'
    ];
}

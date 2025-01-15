<?php

namespace App\Models\PetShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotAllowedTour extends Model
{
    use HasFactory;
    protected $connection = 'mysql_pet_shop';
    protected $table = 'not_allowed_tours';
    protected $fillable = ['tour_id'];
}

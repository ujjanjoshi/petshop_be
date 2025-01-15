<?php

namespace App\Models\PetShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureHotel extends Model
{
    use HasFactory;
    protected $connection = 'mysql_pet_shop';
    protected $table = 'feature_hotels';
    protected $fillable = ['hotel_id'];
}

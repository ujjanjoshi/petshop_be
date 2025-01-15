<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureRental extends Model
{
    use HasFactory;
    protected $connection = 'mysql_pet_shop';
    protected $table = 'feature_rentals';
    protected $fillable = ['rental_id'];
}

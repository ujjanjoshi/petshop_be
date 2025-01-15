<?php

namespace App\Models\PetShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetPoint extends Model
{
    use HasFactory;
    protected $connection = 'mysql_pet_shop';
    protected $table='pet_points';
    protected $fillable = [
        'dollar',
        'rate',
        'status',
        'purchase_limit'
    ];
}

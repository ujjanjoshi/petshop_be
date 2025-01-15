<?php

namespace App\Models\PetShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCharge extends Model
{
    use HasFactory;
    protected $connection = 'mysql_pet_shop';
    protected $table='payment_charges';
    protected $fillable = [
        'payment_method',
        'charges',
        'status'
    ];
}

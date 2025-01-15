<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCharge extends Model
{
    use HasFactory;
    protected $fillable = [
        'payment_method',
        'charges',
        'status'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    use HasFactory;
    use HasFactory;
    protected $table = 'shipping_addresses';
    protected $fillable = [
        'user_id',
        'name',
        'address1',
        'address2',
        'city',
        'region',
        'country',
        'zip',
        'phone',
        'email',
        'is_gifted',
        'fromName',
        'fromEmail',
        'message',
    ];
}

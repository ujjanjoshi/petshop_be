<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    use HasFactory;
    protected $table = 'order_histories';
    protected $fillable = [
        'transaction_id',
        'hotel_id',
        'product_title',
        'sku' ,
        'quantity',
        'product_id',
        'property_id',
        'retail_price' ,
        'user_id',
        'ticket_id' ,
        'session_id',
        'total_price',
        'type_of_payment',
        'last_four_digit',
        'invoice',
        'certificate_code',
        'shipping_id'
    ];
}

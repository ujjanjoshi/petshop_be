<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchandiseOption extends Model
{
    use HasFactory;
    protected $table = 'merchandise_options';
    protected $connection = 'mysql_resource_db';
    protected $fillable = [
        'product_id',
        'name',
        'model',
        'upc',
        'status',
        'size',
        'color',
        "label1",
        "value1",
        "label2",
        "value2",
        "label3",
        "value3",
        'image_lo',
        'image_hi',
        'upcharge_cost',
        'resources',
        'merchandise_id'  
    ];
}

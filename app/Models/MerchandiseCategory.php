<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchandiseCategory extends Model
{
    use HasFactory;
    protected $table = 'merchandise_categories';
    protected $connection = 'mysql_resource_db';
    protected $fillable = [
        'id',
        'name',
        'parent_id',
        'product_count'
    ];
}

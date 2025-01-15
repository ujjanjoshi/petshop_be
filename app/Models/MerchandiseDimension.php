<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchandiseDimension extends Model
{
    use HasFactory;
    protected $table = 'merchandise_dimensions';
    protected $connection = 'mysql_resource_db';
    protected $fillable = [
       "width",
       "height",
       "length"
    ];
}

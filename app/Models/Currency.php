<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;  
    protected $connection = 'mysql_resource_db';
    protected $table = 'currencies';
    protected $fillable = [
        'name',
        'country',
        'default',
        'status',
        'rate'
    ];
    public $timestamps = false;
    
}

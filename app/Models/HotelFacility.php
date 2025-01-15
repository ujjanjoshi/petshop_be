<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelFacility extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'hotel_facilities';
    protected $fillable = ['name', 'code'];
    public $timestamps = false; 
}

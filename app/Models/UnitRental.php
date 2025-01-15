<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitRental extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'rental_units';
    protected $fillable = [
        'unit',
        'rental_id'
    ];
    public function getUnitAttribute($value)
    {
        return json_decode($value, true);
    }
}

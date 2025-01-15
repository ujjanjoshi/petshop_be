<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateRental extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'rental_rates';
    protected $fillable = [
        "rate_id",
        "rental_id",
        "begin_date",
        "end_date",
        "name",
        "min_stay",
        "note",
        "amount",
        "type",
    ];
   

}

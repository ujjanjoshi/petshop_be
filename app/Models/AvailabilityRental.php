<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilityRental extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'rental_availability';
    protected $fillable = [
        "availability_id",
        "rental_id",
        "begin_date",
        "end_date",
        "availability",
        "change_over",
        "min_prior_notify",
        "max_stay",
        "min_stay",
        "stay_increment",
        "minStay",
        "changeOver",
        "availability_total",
        "minPriorNotify",
        'created_at',
        'updated_at',
    ];
}

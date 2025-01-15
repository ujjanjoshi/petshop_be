<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourAttractionDestination extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    // tour_attractions_destinations
    protected $table = 'tour_attractions_destinations';
    protected $fillable = [
        "attraction_id",
        "destination_id"
    ];
   public $timestamps = false; 
}

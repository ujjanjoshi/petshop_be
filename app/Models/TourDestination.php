<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourDestination extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'tour_destinations';
    protected $fillable = [
        "id",
        "parent_id",
        "lookup_id",
        "type",
        "name",
        "latitude",
        "longitude",
        "timezone",
        "iata_code",
        "currency_code",
        'created_at',
        'updated_at',
    ];
    public function attraction()
    {
        return $this->belongsToMany(TourAttraction::class, 'tour_attractions_destinations',
        'destination_id','attraction_id');
    }
}

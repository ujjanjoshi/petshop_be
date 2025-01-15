<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourAttraction extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'tour_attractions';
    protected $fillable = [
        "id",
        "destination_id",
        "destination_name",
        "title",
        "address",
        "city",
        "state",
        "latitude",
        "longitude",
        "thumbnail_url",
        "thumbnail_hi_url",
        "rating",
        "published_at",
        "created_at",
        "updated_at"
    ];
 
}

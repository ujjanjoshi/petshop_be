<?php

namespace App\Models\Tour;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attraction extends Model
{
    use HasFactory;

    protected $connection = 'mysql_resource_db';
    protected $table = 'viator_attractions';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'destination_id', 'destination_name', 'title', 'address', 'city', 'state', 
        'latitude', 'longitude', 'thumbnail_url', 'thumbnail_hi_url', 'rating', 'published_at'
    ];
    /**
     * The attributes that should be cast to native types.
     *   defined as object instead of json/array
     *
     * @var array
     */
    protected $casts = [
    ];
    /**
     * The attributes with default values
     *
     * @var array
     */
    protected $attributes = [
    ];

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
    ];

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    /**
     * Import JSON destination data into db
     *
     * @param   object    $json
     * @return  this
     */
    public static function import($json)
    {
        $id = $json->seoId;

        $attraction = Attraction::find($id) ?? new Attraction;
        $attraction->id    = $id;

        $attraction->destination_id   = $json->destinationId;
        $attraction->destination_name = $json->primaryDestinationName;
        $attraction->title      = $json->title;
        $attraction->address    = $json->attractionStreetAddress;
        $attraction->city       = $json->attractionCity;
        $attraction->state      = $json->attractionState;
        $attraction->latitude   = $json->attractionLatitude;
        $attraction->longitude  = $json->attractionLongitude;
        
        $attraction->thumbnail_url = $json->thumbnailURL;
        $attraction->thumbnail_hi_url   = $json->thumbnailHiResURL;
        $attraction->rating     = $json->rating;
        $attraction->published_at = $json->publishedDate;
        try {
            $attraction->save();
        } catch (\Exception $e) {
            \Log::error("Error importing destination: ". $e->getMessage());
        }
        return $attraction;
    }
}

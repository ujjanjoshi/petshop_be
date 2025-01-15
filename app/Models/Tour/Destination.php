<?php

namespace App\Models\Tour;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Destination extends Model
{
    use HasFactory;

    protected $connection = 'mysql_resource_db';
    protected $table = 'viator_destinations';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'lookup_id', 'parent_id', 'sort_order', 'timezone', 'currency_code', 'iata_code', 
        'type', 'name', 'latitude', 'longitude'
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
        'name', 'lookup_id', 'iata_code'
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
    ];
    /**
     * Allow route binding to column sku in addition to id
     *
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (is_numeric($value)) {
            return $this->where($this->getRouteKeyName(), $value)->first();
        } else {
            return $this->where('name', $value)->first();
        }
    }

    public function parent() 
    {
        return $this->belongsTo(Destination::class, 'parent_id');
    }
    public function children() 
    {
        return $this->hasMany(Destination::class, 'parent_id');
    }
    public function attractions()
    {
        return $this->hasMany(Attraction::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'viator_products_destinations')
                                ->withPivot('primary');
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'viator_destinations_tags');
    }

    /**
     * Import JSON destination data into db
     *
     * @param   object    $json
     * @return  this
     */
    public static function import($json)
    {
        $id = $json->destinationId;

        $destination = Destination::find($id) ?? new Destination;
        $destination->id    = $id;
        $destination->parent_id = $json->parentId;
        $destination->lookup_id = $json->lookupId;
        $destination->timezone = $json->timeZone;
        $destination->iata_code = $json->iataCode;
        $destination->currency_code = $json->defaultCurrencyCode;

        $destination->type  = $json->destinationType;
        $destination->name  = $json->destinationName;
        $destination->latitude = $json->latitude;
        $destination->longitude= $json->longitude;
        
        try {
            $destination->save();
        } catch (\Exception $e) {
            \Log::error("Error importing destination: ". $e->getMessage());
        }
        return $destination;
    }
}

<?php

namespace App\Models\Tour;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'mysql_resource_db';
    protected $table = 'viator_products';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status', 
        'code', 
        'language',
        'title', 
        'description',  
        'url', 
        'supplier',

        'options',
        'itinerary',
        'logistics',

        'images', 

        'language_guides', 
        'translation_info',
        'timezone',
        'inclusions', 
        'exclusions', 
        'additional_info',
        'cancellation_policy', 

        /* ticket_info */
        'ticket_types',      // MOBILE_ONLY, PAPER or both
        'ticket_type_description',
        'ticket_per_booking',    // ONE_PER_BOOKING, ONE_PER_TRAVELER
        'ticket_per_booking_description',

        /* price_info */
        'pricing_type',     // PER_PERSON, UNIT
        'pricing_age_bands',
        'pricing_unit_type', // type=UNIT, BIKE, BOAD, GROUP,PACKAGE, ROOM, AIRCRAFT, VEHICLE

        'booking_conf_setting',     // how it is confirmed
        'booking_requirements', 

        'reviews', 
        //'viator_unique_content',
    ];
    /**
     * The attributes that should be cast to native types.
     *   defined as object instead of json/array
     *
     * @var array
     */
    protected $casts = [

        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',

        'supplier'	=> 'object',
        'options'	=> 'object',
        'itinerary'	=> 'object',
        'logistics'	=> 'object',

        'images'	=> 'object', 

        'language_guides'	=> 'object', 
        'translation_info'	=> 'object',
        'timezone'	=> 'object',
        'inclusions'	=> 'object', 
        'exclusions'	=> 'object', 
        'additional_info'	=> 'object',
        'cancellation_policy'	=> 'object', 

        'pricing_age_bands'	=> 'object', 

        'booking_conf_setting'  => 'object',     // how it is confirmed
        'booking_requirements'           => 'object', 

        'reviews'  => 'object', 
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
        'code', 'name',
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
            return $this->firstWhere($this->getRouteKeyName(), $value);
        }
        return $this->firstWhere('code', $value);
    }

    /**
     * Product can have many tags
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'viator_products_tags');
    }
    /**
     * Product Booking questions 
     */
    public function questions()
    {
        return $this->belongsToMany(BookingQuestion::class, 'viator_products_booking_questions');
    }
    /**
     * Product Booking questions 
     */
    public function destinations()
    {
        return $this->belongsToMany(Destination::class, 'viator_products_destinations')
                                ->withPivot('primary');
    }
    public function attractions()
    {
        return $this->belongsToMany(Attraction::class, 'viator_products_attractions');
    }

    // AvailabitySchedule
    public function availabilites()
    {
        return $this->hasMany(Availability::class, 'product_code', 'code');
    }
    // alias for AvailabitySchedule
    public function schedules()
    {
        return $this->hasMany(Availability::class, 'product_code', 'code');
    }
    /**
     * Import JSON destination data into db
     *
     * @param   object    $json
     * @return  this
     */
    public static function import($json)
    {
        $code = $json->productCode;;

        $product = Product::firstWhere('code', $code) ?? new Product;

        $product->code   = $code;
        $product->status = $json->status;
        if ($product->status == 'INACTIVE') {
            // done, don' import the inactive product
            return $product->id ?  $product->save() : null;
        }
        
        $product->title     = $json->title;
        $product->description = $json->description;

        $product->timezone  = $json->timeZone;

        $product->language  = $json->language;
        $product->language_guides = $json->languageGuides ?? null;
        $product->translation_info= $json->translationInfo ?? null;

        $product->url       = $json->productUrl ?? '';
        $product->supplier  = $json->supplier;

        $product->images    = $json->images;

        $product->itinerary = $json->itinerary ?? null;
        $product->options   = $json->productOptions ?? null;

        $product->logistics	= $json->logistics;
        $product->inclusions	= $json->inclusions ?? null; 
        $product->exclusions	= $json->exclusions ?? null;
        $product->additional_info	= $json->additionalInfo;
        $product->cancellation_policy	= $json->cancellationPolicy; 

        /* ticket_info */
        if (count($json->ticketInfo->ticketTypes) == 1)
            $product->ticket_types	        = $json->ticketInfo->ticketTypes[0];      // MOBILE_ONLY, PAPER or both
        else
            $product->ticket_types	        = 'BOTH';
        $product->ticket_type_description	= $json->ticketInfo->ticketTypeDescription ?? '';
        $product->ticket_per_booking	        = $json->ticketInfo->ticketsPerBooking;    // ONE_PER_BOOKING, ONE_PER_TRAVELER
        $product->ticket_per_booking_description= $json->ticketInfo->ticketsPerBookingDescription ?? '';

        /* price_info */
        $product->pricing_type	    = $json->pricingInfo->type ?? 'PER_PERSON';     // PER_PERSON, UNIT
        $product->pricing_age_bands = $json->pricingInfo->ageBands ?? null;
        $product->pricing_unit_type = $json->pricingInfo->unitType ?? ''; // type=UNIT, BIKE, BOAD, GROUP,PACKAGE, ROOM, AIRCRAFT, VEHICLE

        $product->booking_conf_setting	= $json->bookingConfirmationSettings;     // how it is confirmed
        $product->booking_requirements	= $json->bookingRequirements; 

        $product->reviews	= $json->reviews; 

        $product->created_at = $json->createdAt;
        $product->updated_at = $json->lastUpdatedAt;

        try {
            $product->save();

            // destinations
            $product->destinations()->detach();
            foreach ($json->destinations as $item) {
                $product->destinations()->attach($item->ref, ['primary' => $item->primary]);
            }
            // tags
            $product->tags()->detach();
            foreach ($json->tags as $item) {
                $product->tags()->attach($item);
            }
            // questions
            $product->questions()->detach();
            foreach ($json->bookingQuestions as $item) {
                $product->questions()->attach($item);
            }
        } catch (\Exception $e) {
            \Log::error("Error importing product: ". $e->getMessage());
            $product = null;
        }
        return $product;
    }
}

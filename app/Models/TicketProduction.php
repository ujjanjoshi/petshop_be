<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TicketProduction extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'ticket_productions';
    //public $incrementing = false;
    protected $fillable = [
        'production_id',
        'name',
        'category_id',
        'venue_id',
        'status',
        'occurred_at',
        'created_at',
        'updated_at',
    ];
    protected $primaryKey = 'production_id';
    protected $keyType    = 'string';

    protected $casts = [
         'name'                        => 'string',
         'occurred_at'                 => 'datetime',
         'configuration_id'            => 'integer',
         'popularity_score'            => 'decimal:12',
         'created_at'                  => 'datetime',
         'updated_at'                  => 'datetime',
    ];
    public function venue()
    {
        return $this->belongsTo(TicketVenue::class,'venue_id','venue_id');
    }
    public function altVenue()
    {
        return $this->belongsTo(TicketVenue::class,'venue_id','alt_venue_id');
    }
    public function category()
    {
        return $this->belongsTo(TicketCategory::class,'category_id','category_id');
    }
    public function performers()
    {
        return $this->belongsToMany(TicketPerformer::class, TicketProductionPerformer::class, 
                                    'production_id', 'performer_id');
    }
    /**
     * Accessor for the TicketVenue, good for using eager loadding on the production
     */
    public function getTicketVenueAttribute()
    {
        // buggy laravel eager load with string key
        //return $this->venue ?? $this->alt_venue ?? new TicketVenue;
        //
        //
        // Caceh It to avoid N+1 loading
        //
        $venueId = $this->venue_id;
        $cacheKey= 'venue-' . $venueId;
        $venue   = Cache::remember($cacheKey, now()->addDays(1), function () use ($venueId) {
            return TicketVenue::where('venue_id', $venueId)
                              ->orWhere('alt_venue_id', $venueId)
                              ->first();
        });
        return $venue ?? new TicketVenue;
    }
}

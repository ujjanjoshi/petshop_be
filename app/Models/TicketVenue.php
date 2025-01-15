<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketVenue extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'ticket_venues';
    protected $fillable = [
        'venue_id',
        'alt_venue_id',
        'name',
        'address',
        'address2',
        'city',
        'state',
        'country',
        'country_code',
        'postal_code',
        'latitude',
        'longitude',
        'created_at',
        'updated_at',
        'events_count',
    ];
    protected $primaryKey = "venue_id";
    protected $keyType  = "string";

    public function ticketProduction()
    {
        return $this->belongsTo(TicketProduction::class,'venue_id','venue_id');
    }
    public function productions()
    {
        return $this->hasMany(TicketProduction::class, 'venue_id');
    }
    public function altProductions()
    {
        return $this->hasMany(TicketProduction::class, 'alt_venue_id');
    }
}

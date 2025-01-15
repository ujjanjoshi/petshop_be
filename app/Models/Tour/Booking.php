<?php

namespace App\Models\Tour;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $connection = 'mysql_resource_db';
    protected $table = 'viator_bookings';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'transaction_ref', 
        'event_type', 
        'booking_ref', 
        'partner_ref',
        'last_updated_at',
        'acknowledged_at',
        'booking_item',
        'cancellation',
    ];
    protected $primaryKey = 'transaction_ref';
    protected $keyType    = 'string';

    /**
     * The attributes that should be cast to native types.
     *   defined as object instead of json/array
     *
     * @var array
     */
    protected $casts = [
        'transaction_ref' => 'string',
        'event_type'      => 'string',
        'booking_ref'     => 'string',
        'partner_ref'     => 'string',
        'acknowledged_at' => 'datetime',
        'last_updated_at' => 'datetime',
        'booking_item'    => 'object',
        'cancellation'    => 'object',
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
    /**
     * Import JSON destination data into db
     *
     * @param   object    $json
     * @return  this
     */
    public static function import($json)
    {
        $id = $json->transactionRef;

        $booking = Booking::find($id) ?? new Booking;
        $booking->transaction_ref   = $id;
        $booking->event_type        = $json->eventType;
        $booking->bookign_ref       = $json->bookingRef; 
        $booking->partner_ref       = $json->partnerBookingRef;
        $booking->last_updated_at   = $json->lastUpdated;
        $booking->acknowledged_at   = $json->acknowledgeBy;
        $booking->booking_item      = $json->bookingItem;
        $booking->cancellation      = $json->cancellation ?? null;

        try {
            $booking->save();
        } catch (\Exception $e) {
            \Log::error("Error importing booking: ". $e->getMessage());
            $question = null;
        }
        return $question;
    }
}

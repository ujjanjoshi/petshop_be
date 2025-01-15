<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacationRental extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'rentals';
    protected $fillable = [
        'id',
        'agency',
        'externalId',
        'active',
        'name',
        'headline',
        'summary',
        'description',
        'story',
        'benefits',
        'features',
        'address1',
        'address2',
        'city',
        'state',
        'country',
        'zip',
        'latitude',
        'longitude',
        'show_exact_location',
        'nearest_places',
        'contact_email',
        'contact_fax',
        'language_spoken',
        'contact_name',
        'contact_phone',
        'contact_phone2',
        'contact_phone3',
        'created_at',
        'updated_at',
    ];
    public function rentalImage(){
        return $this->belongsTo(ImageRental::class, 'id',
        'rental_id');
    }
    public function unitRental(){
        return $this->belongsTo(UnitRental::class, 'id',
        'rental_id');
    }

    public function rateRental(){
        return $this->hasMany(RateRental::class,'rental_id', 'id' );
    }
    public function availabilityRentals()
    {
        return $this->belongsTo(AvailabilityRental::class, 'id',
        'rental_id');
    }
    
}

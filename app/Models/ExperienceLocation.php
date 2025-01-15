<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienceLocation extends Model
{
    use HasFactory;
    protected $table = 'experience_locations';
    protected $connection = 'mysql_resource_db';
    protected $fillable = [
        'location_id', // Add 'id' to the fillable attributes
        'name',
        'city',
        'state',
        'state_id',
        'country',
        'country_id'
    ];

    public $timestamps = false;

    public function experiences()
    {
        return $this->belongsToMany(Experience::class, 'experience_locations_experiences',
        'experience_id','location_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienceCity extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    public $incrementing = false;
    protected $table = 'experience_cities';
    protected $fillable = ['id', 'name', 'state', 'state_id', 'country', 'country_id'];
    public $timestamps = false; 
}

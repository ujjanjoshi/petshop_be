<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienceState extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    public $incrementing = false;
    protected $table = 'experience_states';
    protected $fillable = ['id', 'name', 'code', 'country_id','country_name'];
    public $timestamps = false; 
}

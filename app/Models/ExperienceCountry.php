<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienceCountry extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    public $incrementing = false;
    protected $table = 'experience_countries';
    protected $fillable = ['id', 'name', 'image', 'parent_id', 'count'];
    public $timestamps = false; 
}

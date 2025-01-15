<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'experiences';
    protected $fillable = [
        'experience_id',
        'sku', // Add 'sku' to the fillable attributes
        'name',
        'description',
        'short_desc',
        'image',
        'thumbnail',
        'retail_price',
        'wholesale_price',
        'created_at',
        'updated_at',
    ];

    public function categories()
    {
        return $this->belongsToMany(ExperienceCategory::class, 'experience_categories_experiences',
                                                              'category_id', 'experience_id');
    }

    public function locations()
    {
        return $this->belongsToMany(ExperienceLocation::class, 'experience_locations_experiences',
                                                              'location_id', 'experience_id');
    }
}

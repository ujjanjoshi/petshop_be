<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;
    protected $table = 'hotels';
    protected $connection = 'mysql_resource_db';
    protected $fillable = [
        'id',
        'giata_id',
        'code',
        'name',
        'description',
        'address',
        'city',
        'state',
        'country',
        'zip',
        'phone',
        'phones',
        'url',
        'email',
        'image',
        'images',
        'rating',
        'category_code',
        'latitude',
        'longitude',
        'destination_code',
        'rooms',
        'issues',
        'created_at',
        'updated_at',
        // 'detail',
        'prefer',
        'comment',
    ];

    protected $casts = [
        'phones' => 'json',
        // 'images' => 'json',
        'rooms' => 'json',
    ];

    public function getImageAttribute($value)
    {
        return str_replace('http://', 'https://', $value);
    }

    // Accessor for image_hi
    public function getImagesAttribute($value)
    {
        return str_replace('http://', 'https://', $value);
    }

}

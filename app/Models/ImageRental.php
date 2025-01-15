<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageRental extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'rental_images';
    protected $fillable = [
        'image',
        'rental_id'
    ];
    public function getImageAttribute($value)
    {
        return json_decode($value, true);
    }
}

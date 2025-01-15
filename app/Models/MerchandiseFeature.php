<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchandiseFeature extends Model
{
    use HasFactory;
    protected $table = 'merchandise_features';
    protected $connection = 'mysql_resource_db';
    protected $fillable = [
        'feature',
        'featureSort',
        'merchandise_id'  
    ];
}

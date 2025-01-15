<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchandiseResource extends Model
{
    use HasFactory;
    protected $table = 'merchandise_resources';
    protected $connection = 'mysql_resource_db';
    protected $fillable = [
       'brandResourceLink',
       'brandResourceName',
       'merchandise_id'  
        
    ];
}

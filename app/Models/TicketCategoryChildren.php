<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketCategoryChildren extends Model
{
    use HasFactory;
    protected $table = 'ticket_categories_childrens';
    protected $connection = 'mysql_resource_db';
    protected $fillable = [
        'id',
        'name',
        'parent_id',
        'featured',
        'created_at',
        'updated_at',
    ];
    public $timestamps = false;
}

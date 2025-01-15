<?php

namespace App\Models\PetShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureTicket extends Model
{
    use HasFactory;
    protected $connection = 'mysql_pet_shop';
    protected $table = 'feature_tickets';
    protected $fillable = ['ticket_id'];
}

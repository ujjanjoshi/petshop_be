<?php

namespace App\Models\PetShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotAllowedTicket extends Model
{
    use HasFactory;
    protected $connection = 'mysql_pet_shop';
    protected $table = 'not_allowed_tickets';
    protected $fillable = ['ticket_id'];
}

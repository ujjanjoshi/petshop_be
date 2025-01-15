<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketSpecialRequest extends Model
{
    use HasFactory;
    protected $table = 'ticket_special_requests';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'event_name',
        'no_of_tickets',
        'seating_category',
        'special_instruction',

    ];
}

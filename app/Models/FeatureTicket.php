<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureTicket extends Model
{
    use HasFactory;
    protected $connection = 'mysql_pet_shop';
    protected $table = 'feature_tickets';
    protected $fillable = ['ticket_id'];

    public function production()
    {
        return $this->hasOne(TicketProduction::class, 'id', 'ticket_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketProductionPerformer extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'ticket_productions_performers';
    protected $fillable = [
        'production_id',
        'performer_id',
    ];
    public $timestamps = false; 
    public function ticketProduction()
    {
        return $this->belongsTo(TicketProduction::class,'production_id','production_id');
    }
    public function ticketPerformer()
    {
        return $this->belongsTo(TicketPerformer::class,'performer_id','performer_id');
    }
}

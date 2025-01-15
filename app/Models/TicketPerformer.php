<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketPerformer extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'ticket_performers';
    protected $fillable = [
        'performer_id',
        'name',
        'created_at',
        'updated_at',
        'events_count',
        // Add other fillable attributes here if needed
    ];
    protected $primaryKey = 'performer_id';
    protected $keyType = 'string';

    public $timestamps = false;

    /**
     * Allow route binding to column name in addition to id
     *
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $model = $this->firstWhere('name', $value);
        if ($model == null)
            return $this->firstWhere('id', $value);
        return $model;
    }
    public function productions()
    {
        return $this->belongsToMany(TicketProduction::class,'ticket_productions_performers', 'performer_id', 'production_id');
    }
    /**
     */
    public function getAltProductionsAttribute()
    {
        $productionIds = TicketProductionPerformer::where('performer_id', $this->alt_performer_id)->pluck('production_id');
        if ($productionIds->count() == 0)
            return [];
        return TicketProduction::whereIn('production_id', $productionIds->toArray())->get();
    }
    
    public function getAllProductionsAttribute()
    {
        return $this->productions->merge($this->alt_productions);
        return $this->productions()::with('venue')->merge($this->alt_productions());
    }
}

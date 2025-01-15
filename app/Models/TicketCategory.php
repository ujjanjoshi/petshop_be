<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'ticket_categories';
    protected $fillable = [
        'category_id',
        'name',
        'parent_id',
        'featured',
        'created_at',
        'updated_at',
        'events_count',
    ];
    protected $primaryKey = "category_id";
    protected $keyType = "string";
    // public $timestamps = false;
    /**
     * Allow route binding to column sku in addition to id
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

    public function parent()
    {
        return $this->belongsTo('App\Models\TixStock\Category', 'parent_id');
    }
    public function children()
    {
        return $this->hasMany('App\Models\TixStock\Category', 'parent_id');
    }
    public function productions()
    {
        return $this->hasMany(TicketProduction::class,'category_id');
    }
}

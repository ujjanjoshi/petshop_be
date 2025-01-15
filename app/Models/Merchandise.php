<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchandise extends Model
{
    use HasFactory;
    // merchandises
    protected $table = 'merchandises';
    protected $connection = 'mysql_resource_db';
    protected $fillable = [
        'product_id',
        'name',
        'description',
        'brand',
        'model',
        'upc',
        'weight',
        'dimension_id',
        'image_lo',
        'image_hi',
        'selling_price',
        'ship_in_days',
        'prop65',
        'prop65_message',
        'category_id',
        'updated_at_date',
    ];

    public function merchandiseFeature()
    {
        return $this->hasMany(MerchandiseFeature::class, 'merchandise_id', 'id');
    }

    public function merchandiseCategory(){
        return $this->belongsTo(MerchandiseCategory::class,'category_id','id');
    }

    public function merchandiseDimension(){
        return $this->belongsTo(MerchandiseDimension::class,'dimension_id','id');
    }

    public function merchandiseOption(){
        return $this->hasMany(MerchandiseOption::class, 'merchandise_id', 'id');
    }

    public function merchandiseResource(){
        return $this->hasMany(MerchandiseResource::class, 'merchandise_id', 'id');
    }

    public function features()
    {
        return $this->hasMany(MerchandiseFeature::class);
    }

    public function category() 
    {
        return $this->belongsTo(MerchandiseCategory::class);
    }

    public function dimension() 
    {
        return $this->belongsTo(MerchandiseDimension::class);
    }

    public function options() 
    {
        return $this->hasMany(MerchandiseOption::class);
    }

    public function resources()
    {
        return $this->hasMany(MerchandiseResource::class);
    }
}

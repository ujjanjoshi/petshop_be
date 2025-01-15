<?php

namespace App\Models\Tour;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Availability extends Model
{
    use hasFactory;

    protected $connection = 'mysql_resource_db';
    protected $table = 'viator_schedules';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_code', 'bookable_items', 'currency', 'from_price', 'timeofday', 'duration'
    ];
    /**
     * The attributes that should be cast to native types.
     *   defined as object instead of json/array
     *
     * @var array
     */
    protected $casts = [
        'product_code'      => 'string',
        'bookable_items'    => 'object',
        'current'           => 'string',
    ];
    protected $primaryKey = 'product_code';
    protected $keyType  = 'string';

    /**
     * The attributes with default values
     *
     * @var array
     */
    protected $attributes = [
    ];

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_code', 'code');
    }

    /**
     * Import JSON destination data into db
     *
     * @param   object    $json
     * @return  this
     */
    public static function import($json)
    {
        $code = $json->productCode;

        $availability = Availability::find($code) ?? new Availability;
        $availability->product_code = $code;
        $availability->bookable_items = $json->bookableItems;
        $availability->currency     = $json->currency;
        $availability->from_price   = $json->summary->fromPrice ?? '';
        if (!empty($json->summary->fromPriceBeforeDiscount)) 
            $availability->from_price_before_discount   = $json->summary->fromPriceBeforeDiscount;

        $availability->save();
        return $availability;
    }
}

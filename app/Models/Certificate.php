<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Services\CertificateService;
use App\Traits\Searchable;

class Certificate extends Model
{
    use Searchable;

    protected $connection = 'mysql_resource_db';
    protected $table = 'certificates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'sku', 'price', 'redeemer_id',
        'order_id', 'invoice_id', 'status_id',
        'start_date', 'end_date', 'expire'
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['start_date', 'end_date', 'expire', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime:Y-m-d',
        'end_date'  => 'datetime:Y-m-d',
        'expire'    => 'datetime:Y-m-d',
        'created_at' => 'datetime:Y-m-d',
    ];
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    /**
     * The attributes with default values
     *
     * @var array
     */
    protected $attributes = [
        'price' => 999999.99,
        'order_id' => 0,
        'invoice_id' => 0,
        'status_id' => 0,
        'program_id' => 0,
        'vip'   => false,
    ];
    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'invoice_id', 'order_id', 'code', 'created_at'
    ];
    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'redeemer' => ['first_name', 'last_name'],
    ];
    /**
     * Allow route binding to column sku in addition to id
     *
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (is_numeric($value)) {
            return $this->where('id', $value)->first();
        }
        return $this->where('code', $value)->first();
    }

    public function status()
    {
        return $this->belongsTo('\App\Models\Status');
    }
    public function travel()
    {
        return $this->belongsTo('\App\Models\Experience', 'sku', 'sku');
    }
    public function costsheet()
    {
        return $this->belongsTo('\App\Models\CostSheet', 'sku', 'sku');
    }
    public function invoice()
    {
        return $this->belongsTo('\App\Models\Invoice', 'invoice_id', 'invoice_id');
    }
    public function order()
    {
        return $this->belongsTo('\App\Models\Order', 'invoice_id', 'invoice_id');
    }
    public function payments()
    {
        return $this->hasMany('\App\Models\Payment', 'order_id', 'order_id');
    }
    public function redeemer()
    {
        return $this->belongsTo('\App\Models\Redeemer');
    }
    public function orderhistories()
    {
        return $this->setConnection(null)->belongsTo(OrderHistory::class, 'code', 'certificate_code');
    }

    public function scopeGiftCard($query)
    {
        return $query->where('code', 'LIKE', 'GFT%');
    }
    public function scopeExceptGiftCard($query)
    {
        return $query->where('code', 'NOT LIKE', 'GFT%');
    }
    public function isGiftCard()
    {
        return (strncasecmp($this->code, 'GFTCARD', 7) == 0);
    }
    public function scopeOfStatus($query, $status)
    {
        if (is_numeric($status)) {
            return $query->where('status_id',  $status);
        }
        return $query->whereHas('status', function ($query) use ($status) {
            $query->where('name',  $status);
        });
    }

    public function isCustomizedSku()
    {
        return $this->travel()->isEmpty();
    }
    public function traveldate()
    {
        if (empty($this->start_date))
            return '';

        $retval = $this->start_date->format('m/d/Y')  . ' - ' . $this->end_date->format('m/d/Y');
        return $retval;
    }
    public function getTravelDateAttribute()
    {
        if (empty($this->start_date))
            return '';
        if (empty($this->end_date))
            return $this->start_date->format('m/d/Y');
        return $this->start_date->format('m/d/Y') . ' - ' . $this->end_date->format('m/d/Y');
    }
    public function getRedeemerAttribute()
    {
        if (empty($this->middle_name))
            return $this->first_name . ' ' . $this->last_name;
        return $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
    }
    /**
     * Certificate's kind -- certificate, giftcard, eco impact, shop, ...
     *
     * @return string $kind
     */
    public function getKindAttribute()
    {
        if (strncasecmp($this->code, "ECO", 3) == 0) {
            return "dotseco";
        }
        if (strncasecmp($this->code, "SHOP", 4) == 0) {
            return "shop";
        }
        if (strncasecmp($this->code, "GFTCARD", 4) == 0) {
            return "giftcard";
        }
        // normal experience package
        return "certificate";
    }
    /**
     * Certificate name...
     */
    public function getNameAttribute()
    {
        if (strncasecmp($this->code, "SHOP", 4) == 0) {
            return Cache::rememberForever($this->code, function() {
                $srv = new CertificateService;
                $retval = $srv->petRequest ('GET', 'orders/'. $this->code .'/order');

                return $retval->name ?? $this->code;
            });
        }
        return $this->travel->name ?? $this->code;
    }
}

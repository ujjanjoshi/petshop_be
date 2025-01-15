<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;

class GiftCard extends Certificate
{

    /**
     * The "booting" method of the model
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // restrict to only GFT cert
        static::addGlobalScope('giftcard', function (Builder $builder) {
            $builder->where('certificates.code', 'LIKE', 'GFT%');
        });
    }
    /**
     * Card face value based on the code
     */
    public function amount()
    {
        $parts = explode('-', trim($this->code, 'GFTCARD'));
        return ltrim($parts[0], '0');
    }
    public function amountUsed()
    {
       return collect($this->details)->sum('paymentAmount');
    }
    public function amountResidual()
    {
        return $this->amount() - $this->amountUsed();
    }
}

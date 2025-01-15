<?php

namespace App\Services;

use App\Models\GiftCard;
use App\Traits\ResellerApi;
use App\Models\Redeemer;

class RedeemerService
{
    use ResellerApi;

    /**
     * @param string|array $config
     * @throw Exception
     */
    public function __construct($config = null)
    {
        $this->petInit ();
    }

    /**
     * @param \App\Models\Redeemer $redeemer
     * @return mixed $redeemer or false;
     */
    public function create(Redeemer $redeemer)
    {
        $retval = $this->petRequest ('POST', 'redeemers', [
            'json' => $redeemer->toArray()
        ]);
        if ($retval) {
            $redeemer->id = $retval->data->id ?? 0;
        }
        return $retval === false ? $retval : $redeemer;
    }
    public function update(Redeemer $redeemer)
    {
        $retval = $this->petRequest ('PUT', 'redeemers/'. $redeemer->id, [
            'json' => $redeemer->toArray()
        ]);
        return $retval === false ? $retval : $redeemer;
    }
}

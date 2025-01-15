<?php

namespace App\Services;

use App\Models\GiftCard;
use App\Traits\ResellerApi;

class GiftCardService
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
    public function check(GiftCard $giftcard, $action = 'check')
    {
        $giftcardData = $this->petRequest('GET', 'giftcards/'. $giftcard->code ."/$action");
        return $giftcardData;
    }
    public function register(GiftCard $giftcard, array $registerData)
    {
        return $this->petRequest('POST', 'giftcards/'. $giftcard->code .'/register', [
            'json'  => $registerData
        ]);
    }
    public function redeem(GiftCard $giftcard, array $redeemData)
    {
        return $this->petRequest('POST', 'giftcards/'. $giftcard->code .'/redeem', [
            'json'  => $redeemData
        ]);
    }
}

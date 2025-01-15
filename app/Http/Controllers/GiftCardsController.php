<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Models\GiftCard;
use App\Models\Redeemer;
use App\Models\User;
use App\Services\GiftCardService;

class GiftCardsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $redeemer = $request->user()->redeemer ?? new Redeemer;

        $retval = [];

        $srv = new GiftCardService;

        // known giftcards ...
        foreach ($redeemer->giftcards as $giftcard) {
            $gcdata = $srv->check($giftcard, 'isCertValid');
            if ($gcdata === false) {
                continue;
            }
            $gcdata = $gcdata->data;

            $retval[] = [
                'code'          => $giftcard->code,
                'status'        => $gcdata->status,
                'originalValue' => $gcdata->originalValue,
                'residualValue' => $gcdata->residualValue,
            ];
        }
        return $retval;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request, $code)
    {
        $giftcard = GiftCard::firstWhere('code',  $code);
        if ($giftcard == null) {
            return false;
        }
        $user = $request->user();

        $srv = new GiftCardService;
        return $srv->register($giftcard, [
            'name'  =>  $user->name,
            'email' =>  $user->email,
            'userId' => $user->redeemer_id,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $code
     * @return \Illuminate\Http\Response
     */
    public function check($code)
    {
        $giftcard = GiftCard::firstWhere('code',  $code);
        if ($giftcard == null) {
            return '';
        }

        $srv = new GiftCardService;
        return $srv->check($giftcard);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function redeem(Request $request, $code)
    {
        $giftcard = GiftCard::firstWhere('code',  $code);
        if ($giftcard == null) {
            return '';
        }

        $srv = new GiftCardService;
        return $srv->redeem($giftcard, [
            'paymentAmount' =>  $request->paymentAmount,
            'invoice_id'  =>  $request->invoice_id,
            'certificate' =>  $request->certificate
        ]);
    }
}

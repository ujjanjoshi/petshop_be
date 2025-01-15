<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Country;
use App\Models\OrderHistory;
use App\Models\Redeemer;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session as FacadesSession;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Refund;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function procceedToCheckout(Request $request)
    {
        $login_user = $request->user();
        $redeemer_user_data = Redeemer::with(['certificates'])->where('id', $login_user['redeemer_id'])->first();
        $cart_data = $request->cart_data;
        $shipping_data = $request->shipping_data;
        $amount = $request->reduce_amount;
        $gift_card_amount = $request->gift_card_amount;
        $total_amount = $request->total_amount;
        // dd($amount);
        if ($redeemer_user_data) {
            if ($gift_card_amount == null || $amount != 0) {
                $user = User::select(['stripe_id'])->where('redeemer_id', $login_user['redeemer_id'])->get();
                Stripe::setApiKey(config('app.stripe_secret'));

                // $amount = $request->input('amount'); // Assuming you're passing the amount in the request
                if ($login_user['stripe_id'] === "" || $login_user['stripe_id'] === null) {
                    $customer = \Stripe\Customer::create([
                        'email' => $redeemer_user_data['email'],
                        'source' => $request->input('stripeToken'),
                        "address" => [
                            "line1" => '510 Townsend St',
                            "postal_code" => '98140',
                            "city" => 'San Francisco',
                            "state" => 'CA',
                            "country" => 'US',
                        ],

                    ]);
                    User::where('redeemer_id', $login_user['redeemer_id'])
                        ->update(['stripe_id' => $customer->id]);
                    $user = User::where('redeemer_id', $login_user['redeemer_id'])->select(['stripe_id'])->first();
                    // FacadesSession::put('login_user', $user);
                } else {
                    $user = User::where('redeemer_id', $login_user['redeemer_id'])->select(['stripe_id'])->first();
                    // dd($user);
                }

                $session = Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [
                        [
                            'price_data' => [
                                'currency' => 'usd',
                                'product_data' => [
                                    'name' => 'Total Amount'
                                ],
                                'unit_amount' => floatval($amount) * 100
                            ],
                            'quantity' => 1
                        ]
                    ],
                    'mode' => 'payment',
                    'success_url' => $request->base_url . '/success-payment?session_id={CHECKOUT_SESSION_ID}&amount=' . $amount,
                    'cancel_url' => $request->base_url . '/cart',
                    'customer' => $user->stripe_id // Assuming you have a user model with a stripe_id field
                ]);
                $data = [
                    "message" => "success",
                    "url" => $session->url
                ];
                return $data;
            }
        }
    }

    public function success(Request $request)
    {
        $login_user = $request->user();
        $redeemer_user_data = Redeemer::where('id', $login_user['redeemer_id'])->first();
        //   dd($redeemer_user_data);
        Stripe::setApiKey(config('app.stripe_secret'));
        $amount = $request->amount;
        $cart_datas = $request->cart_datas;
        $shipping_datas = $request->shipping_datas;
        $gift_datas = $request->gift_datas;
        $redeemer_id = $redeemer_user_data->id;
        $buyer_information = [
            "userFirstName" => $redeemer_user_data['first_name'],
            "userLastName" => $redeemer_user_data['last_name'],
            "userAddressOne" => $redeemer_user_data['address'],
            "userCity" => $redeemer_user_data['city'],
            "userRegion" => $redeemer_user_data['state'],
            "userCountry" => $redeemer_user_data['country'],
            "userZip" => $redeemer_user_data['zip'],
            "userPhone" => strval($redeemer_user_data['phone']),
            "userEmail" => $redeemer_user_data['email']
        ];
        // dd($buyer_information);
        $order_information = [];
        $invoice_number = "";
        $certificate_code = "";
        $room_information = [];
        $bodyData = [];
        $adult_details_information = [];
        $session_id = $request->session_id;

        $prefix = "tnx_";
        $transaction_id = $prefix . uniqid() . mt_rand(1000, 9999);

        // dd($shipping_address);
        $verify_api = true;
        $sku_id = "";
        $shipping_id = null;
        // dd(FacadesSession::get('shipping_address'));
        $shipping_details = [];
        $gifted_details = [];
        if ($shipping_datas != null) {
            $shipping_address = $shipping_datas;
            $shipping_id = $shipping_address['id'];
            // dd($shipping_address);
            $shipping_details = [
                'name' => $shipping_address['name'],
                'address1' => $shipping_address['address1'],
                'address2' => $shipping_address['address2'],
                'city' => $shipping_address['city'],
                'region' => $shipping_address['region'],
                'country' => $shipping_address['country'],
                'zip' => $shipping_address['zip'],
                'phone' => $shipping_address['phone'],
                'email' => $shipping_address['email']
            ];

            if ($shipping_address['is_gifted']) {
                $gifted_details = [
                    'isGift' => $shipping_address['is_gifted'],
                    'fromName' => $shipping_address['fromName'],
                    'fromEmail' => $shipping_address['fromEmail'],
                    'message' => $shipping_address['message']
                ];
            }
        }
        $card_information = [];
        $cardType = "";
        $cardNumber = "";
        $paymentIntentId = "";
        if ($session_id != null) {
            $stripeSession = Session::retrieve($session_id);
            $paymentIntent = PaymentIntent::retrieve($stripeSession['payment_intent']);
            $paymentIntentId = $paymentIntent['id'];
            // dd($paymentIntent['id']);
            $paymentMethod = PaymentMethod::retrieve($paymentIntent['payment_method']);
            $cardType = $paymentMethod['card']['brand'];
            $cardNumber = $paymentMethod['card']['last4'];
            // "name": "CreditCard",
            // "code": "transaction_id",
            // "amount": "200",
            // "cardType": "VISA",
            // "cardNumber":"****1234",
            // "nameOnCard":"John Doe",
            // "expireYear": "2026",
            // "expireMonth": "1"

            $card_information[] = [
                "name" => "CreditCard",
                "code" => $transaction_id,
                "amount" => $amount,
                "cardType" => $paymentMethod['card']['brand'],
                "cardNumber" => $paymentMethod['card']['last4'],
                "expireMonth" => $paymentMethod['card']['exp_month'],
                "cvv" => "***",
                "expireYear" => $paymentMethod['card']['exp_year'],
                "nameOnCard" => $paymentMethod['billing_details']['name']
            ];
        }
        if ($session_id == null) {
            $session_id = "";
        }
        if ($gift_datas != null) {
            foreach ($gift_datas as $gift_data) {
                $card_information[] = [
                    "name" => "GiftCard",
                    "code" => $gift_data['code'],
                    "amount" => $gift_data['amount']
                ];
            }
        }

        if ($cart_datas != null) {
            // dd(FacadesSession::get('cart_datas'));
            foreach ($cart_datas as $cartItem) {
                $order_information = [
                    "orderId" => $transaction_id,
                    "quantity" => intval($cartItem['quantity']),

                ];
                if ($cartItem['hotel_id'] != null) {
                    $sku_id = $cartItem['hotel_id'];
                    foreach ($cartItem['rooms']['adult_details'] as $adult_details) {
                        $adult_details_information[] = [
                            "roomId" => $cartItem['rooms']['rates']['rooms'],
                            "type" => "AD",
                            "name" => $adult_details['first_name'],
                            "surname" => $adult_details['last_name']
                        ];
                    }
                    if (isset($cartItem['rooms']['age'])) {
                        foreach ($cartItem['rooms']['age'] as $age) {
                            $adult_details_information[] = [
                                "roomId" => $cartItem['rooms']['rates']['room'],
                                "type" => "CH",
                                "age" => $age
                            ];
                        }
                    }
                    // dd($adult_details_information);
                    $room_information = [
                        [
                            "rateKey" => $cartItem['rate_key'],
                            "paxes" => $adult_details_information

                        ]
                    ];

                    $bodyData = [
                        "order" => $order_information,
                        "buyer" => $buyer_information,
                        "rooms" => $room_information,
                        "payments" => $card_information,
                        "remahotel_idrk" => $cartItem['rooms']['special_request']
                    ];
                    $url = '/hotels/' . $cartItem['hotel_id'] . '/booking';
                    $response = Http::petapi()->post($url, $bodyData);
                    // dd($response->json());

                    if ($response->successful()) {
                        $data = $response->json();
                        $invoice_number = $data['salesOrderId'];
                        $certificate_code = $data['reservation'];
                    } else {
                        $verify_api = false;
                    }
                }

                if ($cartItem['ticket_id'] != null) {
                    $sku_id = $cartItem['ticket_id'];
                    if ($cartItem['type'] == 'tevo') {
                        $queryParams_third = [
                            "quantity" => $cartItem['quantity'],
                            "price" => $cartItem['retail_price'],
                            'shipping' => $cartItem['shipping'] ?? 15
                        ];
                        $url = '/tickets/' . $sku_id . '/tax-quote';
                        $response = Http::petapi()->post($url, $queryParams_third);
                        if ($response->failed()) {
                            $details = [
                                'message' => 'Failed to fetch tickets from external API. Please try again later.'
                            ];
                        }
                        $data = $response->json();

                        foreach (FacadesSession::get('hold_id') as $hold_id) {
                            // dd($hold_id);
                            if ($cartItem['ticket_id'] == $hold_id['ticketId']) {
                                $order_information = [

                                    "orderId" => $transaction_id,
                                    "quantity" => intval($cartItem['quantity']),
                                    "tax" => $data['retail']['tax'],
                                    "tax_signature" => $data['tax_signature'],
                                    "holdId" => $hold_id['hold_id']

                                ];
                            }
                        }
                    } else {
                        foreach (FacadesSession::get('hold_id') as $hold_id) {
                            // dd($hold_id);
                            if ($cartItem['ticket_id'] == $hold_id['ticketId']) {
                                $order_information = [

                                    "orderId" => $transaction_id,
                                    "quantity" => intval($cartItem['quantity']),
                                    "holdId" => $hold_id['hold_id']

                                ];
                            }
                        }
                    }
                    $bodyData = [
                        "order" => $order_information,
                        "buyer" => $buyer_information,
                        "payments" => $card_information,
                    ];
                    $url = '/tickets/' . $cartItem['ticket_id'] . '/purchase';
                    $response = Http::petapi()->post($url, $bodyData);
                    // dd($response->json());

                    // dCheck if the request was successful
                    if ($response->successful()) {
                        // Data received successfully
                        $data = $response->json();
                        $invoice_number = $data['salesOrderId'];
                        $certificate_code = $data['reservation'];
                    } else {
                        $verify_api = false;
                    }
                }

                if ($cartItem['sku'] != null) {
                    $sku_id = $cartItem['sku'];
                    $order_information = [
                        "orderId" => $transaction_id,
                        "quantity" => intval($cartItem['quantity']),

                    ];
                    $bodyData = [
                        "order" => $order_information,
                        "buyer" => $buyer_information,
                        "payments" => $card_information,
                    ];
                    // dd($bodyData);
                    $url = '/experiences/' . $cartItem['sku'] . '/purchase';
                    $response = Http::petapi()->post($url, $bodyData);

                    // dCheck if the request was successful
                    if ($response->successful()) {
                        // Data received successfully
                        $data = $response->json();
                        $invoice_number = $data['data']['purchaseData']['salesOrderId'];
                        $certificate_code = $data['data']['purchaseData']['certificateNumber'];
                        // dd($data);
                    } else {
                        $verify_api = false;
                    }
                }
                // dd($cartItem);
                if ($cartItem['product_id'] != null) {
                    $sku_id = $cartItem['product_id'];
                    $order_information = [
                        "orderId" => $transaction_id,
                        "quantity" => intval($cartItem['quantity']),
                        "option_id" => $cartItem['option_id']
                    ];
                    $bodyData = [
                        "order" => $order_information,
                        "buyer" => $buyer_information,
                        "payments" => $card_information,
                        "shipping" => $shipping_details,
                        "gifter" => $gifted_details
                    ];
                    //    dd($bodyData);
                    $url = '/merchandises/' . $cartItem['product_id'] . '/purchase';
                    $response = Http::petapi()->post($url, $bodyData);
                    // dd($response->json());0

                    // dCheck if the request was successful
                    if ($response->successful()) {
                        // Data received successfully
                        $data = $response->json();
                       
                        $invoice_number = $data['salesOrderId'];
                        $certificate_code = $data['reference'];
                        // dd($invoice_number);
                    } else {
                        $verify_api = false;
                    }
                }

                if ($cartItem['rental_id'] != null) {
                    $sku_id = $cartItem['rental_id'];
                    $order_information = [
                        "orderId" => $transaction_id,
                        "quantity" => intval($cartItem['quantity']),
                        "amount" => $cartItem['retail_price']
                    ];
                    $bodyData = [
                        "order" => $order_information,
                        "buyer" => $buyer_information,
                        "payments" => $card_information,
                        "reservation" =>  $cartItem['reservations'],
                        "remark" => null
                    ];
                    $url = '/rentals/' . $cartItem['rental_id'] . '/booking';
                    $response = Http::petapi()->post($url, $bodyData);
                    // dd($response->json());
                    //
                    // dCheck if the request was successful
                    if ($response->successful()) {
                        // Data received successfully
                        $data = $response->json();
                        $invoice_number = $data['salesOrderId'];
                        $certificate_code = $data['reservation'];
                    } else {
                        $verify_api = false;
                    }
                }
                if ($cartItem['product_code'] != null) {
                    $product_code = $cartItem['product_code'];
                    if(!empty($cartItem['startTime'])){
                        $order_information = [
                            "orderId" => $transaction_id,
                            "quantity" => intval($cartItem['quantity']),
                            "price" => $cartItem['retail_price'],
                            "travelDate"=> $cartItem['travelDate'],
                            "startTime"=> $cartItem['startTime'],
                            "paxMix"=>$cartItem['paxMix'],
                            "languageGuide"=>$cartItem['languageGuide'] ?? null,
                            "productOptionCode"=>$cartItem['productOptionCode'],
                            "currency"=>$cartItem['currency'],
                            "bookingRef"=>$cartItem['bookingRef'],
                            "bookingQuestionAnswers"=>$cartItem['bookingQuestionAnswers'],
                        ];
                    }else{
                        $order_information = [
                            "orderId" => $transaction_id,
                            "quantity" => intval($cartItem['quantity']),
                            "price" => $cartItem['retail_price'],
                            "travelDate"=> $cartItem['travelDate'],
                            "paxMix"=>$cartItem['paxMix'],
                            "languageGuide"=>$cartItem['languageGuide'] ?? null,
                            "productOptionCode"=>$cartItem['productOptionCode'],
                            "currency"=>$cartItem['currency'],
                            "bookingRef"=>$cartItem['bookingRef'],
                            "bookingQuestionAnswers"=>$cartItem['bookingQuestionAnswers'],
                        ];
                    }
                   
                    // dd($order_information);
                    $bodyData = [
                        "order" => $order_information,
                        "buyer" => $buyer_information,
                        "payments" => $card_information,
                    ];

                    // $bodyData = [
                    //     "order" => $order_information,
                    //     "buyer" => $buyer_information,
                    // ];
                    // dd($product_code);
                    // dd($bodyData);
                    $url = '/tours/' . $cartItem['product_code'] . '/book';
                    $response = Http::petapi()->post($url, $bodyData);
                    // dCheck if the request was successful
                    if ($response->successful()) {
                        // Data received successfully
                        $data = $response->json();
                        $invoice_number = $data['salesOrderId'];
                        $certificate_code = $data['reservation'];
                    } else {
                        $verify_api = false;
                    }
                }
                if ($verify_api) {
                    $orderHistoryEntry = [
                        'transaction_id' => $transaction_id,
                        'hotel_id' => $cartItem['hotel_id'],
                        'property_id' => $cartItem['rental_id'],
                        'product_title' => $cartItem['product_title'],
                        'product_code'=>$cartItem['product_code'],
                        'sku' => $cartItem['sku'],
                        'quantity' => $cartItem['quantity'],
                        'product_id' => $cartItem['product_id'],
                        'retail_price' => $cartItem['retail_price'],
                        'user_id' => $login_user->redeemer_id,
                        'ticket_id' => $cartItem['ticket_id'],
                        'session_id' => $session_id,
                        'invoice' => $invoice_number,
                        'certificate_code' => $certificate_code,
                        'total_price' => $cartItem['quantity'] * $cartItem['retail_price'],
                        'type_of_payment' => $cardType,
                        'last_four_digit' => $cardNumber,
                        'shipping_id' => $shipping_id
                    ];
                    // dd($orderHistoryEntry);
                    OrderHistory::create($orderHistoryEntry);
                    // $certificate = new Certificate();
                    // $certificate->code = $certificate_code;
                    // $certificate->sku =  $sku_id;
                    // $certificate->price = $cartItem['quantity'] * $cartItem['retail_price'];
                    // $certificate->order_id = $transaction_id;
                    // $certificate->invoice_id = $invoice_number;
                    // $certificate->status_id = 0;
                    // $certificate->redeemer_id = $redeemer_id;
                    // // $certificate->start_date = $certificate_data['start_date'];
                    // // $certificate->end_date = $certificate_data['end_date'];
                    // $certificate->expire = new DateTime();
                    // // $certificate->created_at = $certificate_data['created_at'];
                    // // $certificate->updated_at = $certificate_data['updated_at'];
                    // // $certificate->deleted_at = $certificate_data['deleted_at'];
                    // $certificate->save();
                }
            }
        }

        // Cart::where('user_id', $login_user['user_id'])->delete();
        if ($verify_api) {
            $data = [
                "amount" => $amount,
                "message" => "success"
            ];
            return $data;
        } else {
            try {
                $refund_data = Refund::create([
                    'payment_intent' => $paymentIntentId,
                ]);
                return response()->json(['message' => 'Refund Sucessfully']);

            } catch (ApiErrorException $e) {
                // Optionally, return a response or throw an exception
                return response()->json(['message' => 'Refund failed due to Stripe API error.'], 200);

            } catch (\Exception $e) {
                return response()->json(['message' => 'Refund failed due to an unexpected error.'], 200);
            }
        }
    }

    public function initiatePayment(Request $request)
    {
        $login_user = $request->user();
        $amount = $request->amount;
        if (is_float($amount)) {
            $amount = floatval($amount) * 100;
        }
        $currency = $request->currency;
        //$redeemer_user_data = Redeemer::where('id', $login_user['redeemer_id'])->first();
        $redeemer_user_data = $login_user->redeemer;

        Stripe::setApiKey(config('app.stripe_secret'));

        // $amount = $request->input('amount'); // Assuming you're passing the amount in the request
        if ($login_user['stripe_id'] === "" || $login_user['stripe_id'] === null) {
            $customer = \Stripe\Customer::create([
                'email' => $redeemer_user_data['email'],
                'source' => $request->input('stripeToken'),
                "address" => [
                        "line1" => '510 Townsend St',
                        "postal_code" => '98140',
                        "city" => 'San Francisco',
                        "state" => 'CA',
                        "country" => 'US',
                    ],

            ]);
            $login_user->update(['stripe_id' => $customer->id]);

            //            User::where('redeemer_id', $login_user['redeemer_id'])
//                ->update(['stripe_id' => $customer->id]);
//            $user =  User::where('redeemer_id', $login_user['redeemer_id'])->select(['stripe_id'])->first();
            // FacadesSession::put('login_user', $user);
        } else {
            //$user =  User::where('redeemer_id', $login_user['redeemer_id'])->select(['stripe_id'])->first();
            // dd($user);
        }

        $ephemeralKey = \Stripe\EphemeralKey::create([
            'customer' => $login_user->stripe_id,
        ], [
            'stripe_version' => '2024-06-20',
        ]);

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'customer' => $login_user->stripe_id,
            // In the latest version of the API, specifying the `automatic_payment_methods` parameter
            // is optional because Stripe enables its functionality by default.
            'automatic_payment_methods' => [
                'enabled' => 'true',
            ],
        ]);

        return json_encode(
            [
                'paymentIntent' => $paymentIntent->client_secret,
                'ephemeralKey' => $ephemeralKey->secret,
                'customer' => $login_user->stripe_id,
                'publishableKey' => 'pk_test_TYooMQauvdEDq54NiTphI7jx'
            ]
        );
    }
}

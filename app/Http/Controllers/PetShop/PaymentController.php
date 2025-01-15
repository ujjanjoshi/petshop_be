<?php

namespace App\Http\Controllers\PetShop;

use App\Http\Controllers\Controller;
use App\Jobs\SendOrderHistoryMailJobs;
use App\Models\PetShop\Branding;
use App\Models\Country;
use App\Models\Merchandise;
use App\Models\PetShop\OrderHistory;
use App\Models\PetShop\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Refund;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        $user_id = $request->user()->user_id;
        $user = User::where('user_id', $user_id)->first();
        if ($user) {
            $login_user = $request->user();
            $country_name = Country::where('id', intval($login_user['phone_country_code']))->select(['iso'])->first();
            $buyer_information = [
                "userFirstName" => $login_user['first_name'],
                "userLastName" => $login_user['last_name'],
                "userAddressOne" => $login_user['address1'],
                "userCity" => $login_user['city'],
                "userRegion" => $login_user['state'],
                "userCountry" => $country_name['iso'],
                "userZip" => $login_user['postal_code'],
                "userPhone" => $login_user['phone'],
                "userEmail" => $login_user['email']
            ];
            // dd($login_user);
            $user = User::select(['email', 'stripe_id'])->where('user_id', $login_user->id)->get();
            // dd($login_user);
            $total_amount = $request->total_amount;
            // dd($request->query('amount'));
            Stripe::setApiKey(config('app.petshop_stripe_secret'));

            // $amount = $request->input('amount'); // Assuming you're passing the amount in the request
            if ($login_user['stripe_id'] === "" || $login_user['stripe_id'] === null) {
                $customer = \Stripe\Customer::create([
                    'email' => $login_user['email'],
                    'source' => $request->input('stripeToken'),
                    "address" => [
                        "line1" => '510 Townsend St',
                        "postal_code" => '98140',
                        "city" => 'San Francisco',
                        "state" => 'CA',
                        "country" => 'US',
                    ],

                ]);
                User::where('user_id', $login_user['user_id'])
                    ->update(['stripe_id' => $customer->id]);
                $user = User::where('user_id', $login_user['user_id'])->select(['stripe_id'])->first();
                // FacadesSession::put('login_user', $user);
            } else {
                $user = User::where('user_id', $login_user['user_id'])->select(['stripe_id'])->first();
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
                            'unit_amount' => floatval($total_amount) * 100
                        ],
                        'quantity' => 1
                    ]
                ],
                'mode' => 'payment',
                'success_url' => $request->base_url . '/success-payment?session_id={CHECKOUT_SESSION_ID}&amount=' . $total_amount,
                'cancel_url' => $request->base_url . '/cart',
                'customer' => $user->stripe_id // Assuming you have a user model with a stripe_id field
            ]);
            $data = [
                "message" => "success",
                "url" => $session->url
            ];
            return $data;
        } else {
           $data=[
            "status"=>"error",
            "message"=>"Not Login"
           ];
           return response()->json($data);
        }
    }

    public function success(Request $request)
    {
        $user_id = $request->user()->user_id;
        $user = User::where('user_id', $user_id)->first();
        if ($user) {
            $login_user = $request->user();
            Stripe::setApiKey(config('app.petshop_stripe_secret'));
            $amount = $request->amount;
            $cart_datas = $request->cart_datas;
            $shipping_datas = $request->shipping_datas;
            $gift_datas = $request->gift_datas;
           
            $country_name = Country::where('id', intval($login_user['phone_country_code']))->select(['iso'])->first();

            $buyer_information = [
                "userFirstName" => $login_user['first_name'],
                "userLastName" => $login_user['last_name'],
                "userAddressOne" => $login_user['address1'],
                "userCity" => $login_user['city'],
                "userRegion" => $login_user['state'],
                "userCountry" => $country_name['iso'],
                "userZip" => $login_user['postal_code'],
                "userPhone" => strval($login_user['phone']),
                "userEmail" => $login_user['email']
            ];
            $order_information = [];
            $invoice_number = "";
            $certificate_code = "";
            $room_information = [];
            $bodyData = [];
            $adult_details_information = [];
            $session_id = $request->session_id;
            $prefix = "tnx_";
            $transaction_id = $prefix . uniqid() . mt_rand(1000, 9999);
            $stripeSession = Session::retrieve($session_id);
            $paymentIntent = PaymentIntent::retrieve($stripeSession['payment_intent']);
            $paymentIntentId = $paymentIntent['id'];
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
            $paymentMethod = PaymentMethod::retrieve($paymentIntent['payment_method']);
            $cardType = $paymentMethod['card']['brand'];
            $cardNumber = $paymentMethod['card']['last4'];
            $card_information = [[
                "name" => "CreditCard",
                "code" => $transaction_id,
                "amount" => $amount,
                "cardType" => $paymentMethod['card']['brand'],
                "cardNumber" => $paymentMethod['card']['last4'],
                "expireMonth" => $paymentMethod['card']['exp_month'],
                "cvv" => "***",
                "expireYear" => $paymentMethod['card']['exp_year'],
                "nameOnCard" => $paymentMethod['billing_details']['name']
            ]];

            // dd(FacadesSession::get('cart_datas'));
            foreach ($cart_datas ?? [] as $cartItem) {
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
                    $queryParams = $bodyData;
                    // echo ($response);
                    if ($response->successful()) {
                        $data = $response->json();
                        $invoice_number = $data['salesOrderId'];
                        $certificate_code = $data['reservation'];
                    } else {
                        $verify_api = false;
                    }
                }
               
                if ($cartItem['ticket_id'] != null) {
                    // if tax existed, pass through...
                    if (isset($cartItem['tax_signature'])) {
                        $order_information['tax_signature'] = $cartItem['tax_signature'];
                        $order_information['tax'] = $cartItem['tax'] ?? 0;
                    }

                    $bodyData = [
                        "order" => $order_information,
                        "buyer" => $buyer_information,
                        "payments" => $card_information,
                    ];
                    $url = '/tickets/' . $cartItem['ticket_id'] . '/purchase';

                    $response = Http::petapi()->post($url, $bodyData);
                    // dCheck if the request was successful
                    if ($response->successful()) {
                        // Data received successfully
                        $data = $response->json();
                        // dd($data);
                        $invoice_number = $data['salesOrderId'];
                        $certificate_code = $data['reservation'];
                    } else {
                        $verify_api = false;
                        \Log::error("Error purchasing ticket - ". $cartItem['ticket_id']);
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

                    // dd($response->json());
                    // dCheck if the request was successful
                    if ($response->successful()) {
                        // Data received successfully
                        $data = $response->json();
                        $invoice_number = $data['data']['purchaseData']['salesOrderId'];
                        $certificate_code = $data['data']['purchaseData']['certificateNumber'];
                    } else {
                        $verify_api = false;
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

                            foreach ($cartItem['hold_id'] as $hold_id) {
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
                        }
                        else{
                        foreach ($cartItem['hold_id'] as $hold_id) {
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
                        // dd($bodyData);
                        $url = '/tickets/' . $cartItem['ticket_id'] . '/purchase';

                        $response = Http::petapi()->post($url, $bodyData);
                        // dCheck if the request was successful
                        if ($response->successful()) {
                            // Data received successfully
                            $data = $response->json();
                            // dd($data);
                            $invoice_number = $data['salesOrderId'];
                            $certificate_code = $data['reservation'];
                        } else {
                            $verify_api = false;
                        }
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
                        "reservation" => $cartItem['reservations'],
                        "remark" => null
                    ];
                    $url = '/rentals/' . $cartItem['rental_id'] . '/booking';
                    $response = Http::petapi()->post($url, $bodyData);
                    // dd($response->json());
                    // dCheck if the request was successful
                    if ($response->successful()) {
                        // Data received successfully
                        $data = $response->json();
                        // dd($data);
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
                //    return $response;s
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
                        'user_id' => $login_user->user_id,
                        'ticket_id' => $cartItem['ticket_id'],
                        'session_id' => $session_id,
                        'invoice' => $invoice_number,
                        'certificate_code' => $certificate_code,
                        'total_price' => $cartItem['quantity'] * $cartItem['retail_price'],
                        'type_of_payment' => $cardType,
                        'last_four_digit' => $cardNumber,
                        'shipping_id' => $shipping_id
                    ];
                    $order_data = OrderHistory::create($orderHistoryEntry);
                    $this->pdfSend($request,$order_data->transaction_id);
                }
            }

            // $cartItems = Cart::where('user_id', $login_user['user_id'])->select(['product_title', 'sku', 'quantity', 'retail_price', 'user_id', 'ticket_id'])->get();

            // Cart::where('user_id', $login_user['user_id'])->delete();
            if ($verify_api) {
                $data = [
                    "amount" => $amount,
                    "message" => "success"
                ];
                return response()->json($data);
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
    }

    public function pdfSend(Request $request,$transaction_id)
    {
        $user_id = $request->user()->user_id;
        $user = User::where('user_id', $user_id)->first();
        if ($user) {
            $login_user = $request->user();
            $order_data = [];
            $order_history = OrderHistory::where('transaction_id', $transaction_id)->orderBy('created_at', 'desc')->get();
            $branding = Branding::first();
            $combinedData = [];
            foreach ($order_history as $item) {
                $transactionId = $item['transaction_id'];
                $merchandise_description = null;
                if ($item->product_id != null) {
                    $merchandise_description = Merchandise::where('product_id', $item->product_id)->select('description')->first()->description;
                }

                if (!isset($combinedData[$transactionId])) {
                    $combinedData[$transactionId] = [
                        'transaction_id' => $transactionId,
                        'created_at' => $item->created_at->toDateTimeString(),
                        'total_price' => 0, // Initialize total price
                        'data' => []
                    ];
                }
                unset($item['transaction_id']);

                // Add the item's total price to the total price for this transaction
                $combinedData[$transactionId]['total_price'] += floatval($item->total_price);
                $combinedData[$transactionId]['data'][] = [
                    "id" => $item->id,
                    "product_title" => $item->product_title,
                    "sku" => $item->sku,
                    "quantity" => $item->quantity,
                    "retail_price" => $item->retail_price,
                    "ticket_id" => $item->ticket_id,
                    "hotel_id" => $item->hotel_id,
                    "merchandise_description" => $merchandise_description,
                    "type_of_payment" => $item->type_of_payment,
                    "total_price" => $item->total_price,
                    "last_four_digit" => $item->last_four_digit,
                    "product_id" => $item->product_id,
                    'invoice' => $item->invoice,
                    'certificate_code' => $item->certificate_code
                ];
            }
            foreach ($combinedData as &$transaction) {
                $transaction['total_price'] = number_format(floatval($transaction['total_price']), 2);
            }

            // Convert associative array to indexed array
            $combinedData = array_values($combinedData);

            $original_data = [
                "details" => [
                    "id" => $branding->id,
                    "header_logo" => $branding->header_logo,
                    "footer_logo" => $branding->footer_logo,
                    "address" => $branding->address,
                    "phone_number" => $branding->phone_number,
                    "trade_mark" => $branding->trade_mark,
                    "term_policy" => $branding->term_policy,
                    "first_name" => $login_user->first_name,
                    "email" => $login_user->email,
                ],
                "transaction_data" => $combinedData
            ];
            $user = new UserController();
            $email_template = $user->emailTemplate(3, $login_user->email);
            $data = [
                'email' => $login_user->email,
                'subject' => $email_template['subject'],
                'body' => $email_template['body'],
                'order_histories' => $original_data
            ];
            dispatch(new SendOrderHistoryMailJobs($data));
        }
    }
}

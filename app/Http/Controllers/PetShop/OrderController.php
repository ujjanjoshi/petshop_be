<?php

namespace App\Http\Controllers\PetShop;

use App\Http\Controllers\Controller;
use App\Models\PetShop\Branding;
use App\Models\Merchandise;
use App\Models\PetShop\OrderHistory;
use App\Models\PetShop\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OrderController extends Controller
{
    public function getHistory(Request $request)
    {
        $user_id = $request->user()->user_id;
        $user = User::where('user_id', $user_id)->first();
        if ($user) {
            $order_data = [];
            $login_user = $request->user();
            $order_history = OrderHistory::where('user_id', $user_id)->orderBy('created_at', 'desc')->get();
            $branding = Branding::first();
            $combinedData = [];
            foreach ($order_history as $item) {
                $transactionId = $item['transaction_id'];
                $merchandise_description=null;
                if($item->product_id!=null){
                    $merchandise_description= Merchandise::where('product_id',$item->product_id)->select('description')->first()->description;
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
                    "merchandise_description"=>$merchandise_description,
                    "type_of_payment" => $item->type_of_payment,
                    "total_price" => $item->total_price,
                    "last_four_digit" => $item->last_four_digit,
                    "product_id"=>$item->product_id,
                    'invoice' => $item->invoice,
                    'certificate_code' => $item->certificate_code
                ];
            }
            foreach ($combinedData as &$transaction) {
                $transaction['total_price'] = number_format(floatval($transaction['total_price']), 2);
            }

            // Convert associative array to indexed array
            $combinedData = array_values($combinedData);

            $original_data=[
                "details"=> [
                    "id" => $branding->id,
                    "header_logo" => $branding->header_logo,
                    "footer_logo" => $branding->footer_logo,
                    "address" => $branding->address,
                    "phone_number" => $branding->phone_number,
                    "trade_mark" => $branding->trade_mark,
                    "term_policy" => $branding->term_policy,
                    "first_name"=>$login_user->first_name,
                    "email"=>$login_user->email,
                ],
                "transaction_data"=>$combinedData
            ];
            return response()->json($original_data);
            // dd($original_data);
            // return view('Cart.orderList', compact('original_data'));
        }
    }
}

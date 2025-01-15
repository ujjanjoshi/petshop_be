<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\OrderHistory;
use App\Models\Redeemer;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function getOrderHistory(Request $request){
        $redeemer_id = $request->user()->redeemer_id;
       $order_histories= OrderHistory::where('user_id',$redeemer_id)->get();
     
       $data=[
        "status"=>"success",
        "data"=>$order_histories
       ];
       return $data;     
    }
}

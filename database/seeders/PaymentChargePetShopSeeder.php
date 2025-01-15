<?php

namespace Database\Seeders;

use App\Models\PetShop\PaymentCharge;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentChargePetShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas=[
            [
                "payment_method"=>"credit_card",
                "type"=>"experience",
                "status"=>true,
                "charges"=>5.00
            ],
            [
                "payment_method"=>"credit_card",
                "type"=>"tickets",
                "status"=>true,
                "charges"=>5.00
            ],
            [
                "payment_method"=>"credit_card",
                "type"=>"merchandise",
                "status"=>true,
                "charges"=>5.00
            ],
            [
                "payment_method"=>"credit_card",
                "type"=>"rentals",
                "status"=>true,
                "charges"=>5.00
            ],
            [
                "payment_method"=>"credit_card",
                "type"=>"tours",
                "status"=>true,
                "charges"=>5.00
            ],
            [
                "payment_method"=>"credit_card",
                "type"=>"hotels",
                "status"=>true,
                "charges"=>5.00
            ]
        ];
        $paymentCharges=PaymentCharge::count();
        if($paymentCharges>0){
            PaymentCharge::truncate();
        }
        foreach ($datas as $data) {
            $payment_charges = new PaymentCharge();
            $payment_charges->payment_method = $data["payment_method"];
            $payment_charges->type = $data["type"];
            $payment_charges->status = $data["status"];
            $payment_charges->charges = $data["charges"];
            $payment_charges->save();
        }
    }
}

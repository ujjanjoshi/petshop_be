<?php

namespace Database\Seeders;

use App\Models\PetShop\PetPoint;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PetPointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas=[
            [
                "dollar"=>1,
                "rate"=>0.1,
                "status"=>true,
                "purchase_limit"=>20000,
            ],
      
        ];
        $pet_point=PetPoint::count();
        if($pet_point>0){
            PetPoint::truncate();
        }
        foreach ($datas as $data) {
            $pet_points = new PetPoint();
            $pet_points->dollar = $data["dollar"];
            $pet_points->rate = $data["rate"];
            $pet_points->status = $data["status"];
            $pet_points->purchase_limit = $data["purchase_limit"];
            $pet_points->save();
        }   
    }
}

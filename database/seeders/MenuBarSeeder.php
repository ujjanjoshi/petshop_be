<?php

namespace Database\Seeders;

use App\Models\MenuBar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuBarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data=[
            [
                "name"=> 'EXPERIENCES',
                "url"=> "experience",
                "is_active"=>true
            ],
            [
                "name"=> 'TICKETS',
                "url"=> "tickets",
                "is_active"=>true
            ],
            [
                "name"=> 'HOTELS',
                "url"=> "hotels",
                "is_active"=>true
            ],
            [
                "name"=> 'MERCHANDISE',
                "url"=> "merchandise",
                "is_active"=>true
            ],
            [
                "name"=> 'HOMES/VILLAS',
                "url"=> "home-villas",
                "is_active"=>true
            ],
            [
                "name"=> 'TOURS',
                "url"=> "tours",
                "is_active"=>true
            ],
         
        ];
        $menu_bar=MenuBar::count();
        if($menu_bar>0){
            MenuBar::truncate();
        }
        DB::table('menu_bars')->insert($data); 
    }
}

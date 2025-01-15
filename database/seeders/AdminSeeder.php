<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = password_hash("12345678", PASSWORD_DEFAULT);

        $data=[
            "name"=>"Super Admin",
           "email"=>"super@gmail.com",
           "password"=>$password,
           "is_super"=>true,  
        ];
        $admin=Admin::count();
        if($admin>0){
            Admin::truncate();
        }
        DB::table('admins')->insert($data);
    }
}

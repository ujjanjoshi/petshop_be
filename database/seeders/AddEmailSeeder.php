<?php

namespace Database\Seeders;

use App\Models\Email;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddEmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas=[
            [
                "title"=> 'OTP Mail',
                "subject"=> "OTP Subject",
                "body"=> "OTP Body"
            ], 
             [
                "title"=> 'Notification Mail',
                "subject"=> "Notification Subject",
                "body"=> "Notification Body"
            ]
            ];
        
            foreach ($datas as $data) {
                $email_data_data = new Email();
                $email_data_data->title = $data["title"];
                $email_data_data->subject = $data["subject"];
                $email_data_data->body = $data["body"];
                $email_data_data->save();
            }
    }
}

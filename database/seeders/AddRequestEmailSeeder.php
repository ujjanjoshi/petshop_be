<?php

namespace Database\Seeders;

use App\Models\Email;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddRequestEmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas=[
            [
                "title"=> 'Request Mail',
                "subject"=> "Request Mail",
                "body"=> "Request Mail Send Sucesfully"
            ],
            [
                "title"=> 'Invoice Mail',
                "subject"=> "Invoice Mail",
                "body"=> "Invoice Mail Send Sucesfully"
            ],
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

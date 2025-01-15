<?php

namespace Database\Seeders;

use App\Models\Email;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas=[
            [
                "title"=> 'Verfication Mail',
                "subject"=> "Verification Subject",
                "body"=> "Verification Body"
            ],
            [
                "title"=> 'Reset Password Mail',
                "subject"=> "Reset Password Subject",
                "body"=> "Reset Password Body"
            ],
            [
               "title"=> 'Approve Mail',
                "subject"=> "Approve Subject",
                "body"=> "Approve Body" 
            ]
            ];
            $email_data = Email::count();
            if ($email_data > 0) {
                Email::truncate();
            }
            foreach ($datas as $data) {
                $email_data_data = new Email();
                $email_data_data->title = $data["title"];
                $email_data_data->subject = $data["subject"];
                $email_data_data->body = $data["body"];
                $email_data_data->save();
            }
    }
}

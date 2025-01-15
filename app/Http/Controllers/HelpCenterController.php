<?php

namespace App\Http\Controllers;

use App\Jobs\SendHelpCenterMailJobs;
use App\Models\Redeemer;
use Illuminate\Http\Request;

class HelpCenterController extends Controller
{
    public function sendEmail(Request $request)
    {
        $redeemer = $request->user()->redeemer ?? new Redeemer;
        
        $name = $redeemer->first_name;
        $email_from = $redeemer->email;
        // $email = "aviralgit@gmail.com";
        $email="customercare@justrewardsredemption.com";
        $subject = $request->subject;
        $body = $request->body;
        $data = [
            "name" => $name,
            "email_from" => $email_from,
            "email_to" => $email,
            "subject" => $subject,
            "body" => $body
        ];
        // dd($details);
        dispatch(new SendHelpCenterMailJobs($data));
        $data=[
            "status"=>"success",
            "message"=>"Email Send Sucessfully"
        ];
        return $data;
    }
}

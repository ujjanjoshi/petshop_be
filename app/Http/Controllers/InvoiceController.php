<?php

namespace App\Http\Controllers;

use App\Jobs\SendInvoiceMailJobs;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
   public function sendInvoice(Request $request){
    $email_template= new UserController();
    $email_template=$email_template->emailTemplate(5,$request->email);
    $data=[
        'email'=>$request->email,
        'subject'=> $email_template['subject'],
        'body'=>$email_template['body']
        ] ;
    dispatch(new SendInvoiceMailJobs($data));
   }
}

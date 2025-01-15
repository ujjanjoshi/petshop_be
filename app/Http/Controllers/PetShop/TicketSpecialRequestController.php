<?php

namespace App\Http\Controllers\PetShop;

use App\Http\Controllers\Controller;
use App\Jobs\SendRequestMailJobs;
use App\Jobs\SendTicketRequestSupportJobs;
use App\Models\TicketSpecialRequest;
use Illuminate\Http\Request;

class TicketSpecialRequestController extends Controller
{
    /**
     * present the request form
     */
    public function create()
    {
        return view('Tickets.special-request');
    }
    /**
     * process the special request data
     */
    public function store(Request $request)
    {

        return redirect('/tickets')->with('status', 'OK');
    }

    public function sendRequestMail(Request $request){
        
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $email = $request->email;
        $phone_number = $request->phone_number;
        $event_name = $request->event_name;
        $no_of_tickets = $request->no_of_tickets;
        $seating_category = $request->seating_category;
        $special_instruction = $request->special_instruction;

        $ticket_request = new TicketSpecialRequest();
        $ticket_request->first_name = $first_name;
        $ticket_request->last_name = $last_name;
        $ticket_request->email = $email;
        $ticket_request->phone_number = $phone_number;
        $ticket_request->event_name = $event_name;
        $ticket_request->no_of_tickets = $no_of_tickets;
        $ticket_request->seating_category = $seating_category;
        $ticket_request->special_instruction = $special_instruction;
        $ticket_request->save();
        $email_template= new UserController();
        $email_template=$email_template->emailTemplate(4,$request->email);
        $data=[
            'email'=>$request->email,
            'subject'=> $email_template['subject'],
            'body'=>$email_template['body'],
            'ticket_request'=>$ticket_request
            ] ;
        dispatch(new SendRequestMailJobs($data));
        dispatch(new SendTicketRequestSupportJobs($ticket_request));

        return $ticket_request;
    }
}


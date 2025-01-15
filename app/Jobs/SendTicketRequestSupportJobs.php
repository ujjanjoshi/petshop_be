<?php

namespace App\Jobs;

use App\Mail\SendTicketRequestSupport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTicketRequestSupportJobs implements ShouldQueue
{
    use Queueable;
    protected $ticketRequest;
    /**
     * Create a new job instance.
     */
    public function __construct($ticketRequest)
    {
        $this->ticketRequest=$ticketRequest;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $email=new SendTicketRequestSupport($this->ticketRequest);

        $supportEmail = config('app.mail_support_address');
        if (!empty($supportEmail)) {
            Mail::to($supportEmail)->send($email);
        }
    }
}

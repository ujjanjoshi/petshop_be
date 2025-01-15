<?php

namespace App\Jobs;

use App\Mail\SendOTPMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOTPMailJobs implements ShouldQueue
{
    use Queueable;
    protected $data;
    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
      $this->data=$data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $email=new SendOTPMail($this->data);
        Mail::to($this->data['actual_email'])->send($email);
    }
}

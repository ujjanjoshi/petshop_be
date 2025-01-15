<?php

namespace App\Jobs;

use App\Mail\SendOrderHistoryMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderHistoryMailJobs implements ShouldQueue
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
        $email=new SendOrderHistoryMail($this->data);
        Mail::to($this->data['email'])->send($email);
    }
}

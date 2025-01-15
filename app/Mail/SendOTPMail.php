<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOTPMail extends Mailable
{
    use Queueable, SerializesModels;
    public $actual_email;
    public $transfer_email;
    public $subject;
    public $body;
    public $otp;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->actual_email=encrypt($data['actual_email']);
        $this->transfer_email=encrypt($data['transfer_email']);      
        $this->subject=$data['subject'];
        $this->body=$data['body'];
        $this->otp=$data['otp'];

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.sendOTPEmail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

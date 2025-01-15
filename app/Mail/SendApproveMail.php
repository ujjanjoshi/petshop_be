<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendApproveMail extends Mailable
{
    use Queueable, SerializesModels;
    public $email; 
    public $subject;
    public $body;
    public $base_url;
    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->email=encrypt($data['email']);
        $this->subject=$data['subject'];
        $this->body=$data['body'];
        $this->base_url=$data['base_url'];
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
            markdown: 'emails.approveEmail',
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

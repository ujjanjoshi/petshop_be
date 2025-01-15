<?php

namespace App\Mail;

use App\Models\TicketSpecialRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendTicketRequestSupport extends Mailable
{
    use Queueable, SerializesModels;
    public $email;
    public $subject;
    public $body;
    public $ticketRequest;
    /**
     * Create a new message instance.
     */
    public function __construct(TicketSpecialRequest $ticketRequest)
    {
        $this->email  = config('app.mail_support_address');
        $this->subject= 'Special Ticket Request';

        $this->ticketRequest = $ticketRequest;
        //$this->body   = $data['body'];
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
            markdown: 'emails.ticket-request',
            with:   [
                'ticketRequest' =>  $this->ticketRequest,
            ],
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

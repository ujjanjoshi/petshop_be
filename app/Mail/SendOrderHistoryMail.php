<?php

namespace App\Mail;

use Barryvdh\DomPDF\PDF;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOrderHistoryMail extends Mailable
{
    use Queueable, SerializesModels;
    public $email;
    public $subject;
    public $body;
    public $order_histories;
    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->email=encrypt($data['email']);
        $this->subject=$data['subject'];
        $this->body=$data['body'];
        $this->order_histories=$data['order_histories'];

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject:  $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orderHistoryEmail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdf = app()->make('dompdf.wrapper');
        $pdf->loadView('Pdf.pdf', ['order_histories' =>$this->order_histories]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'order_confirmation.pdf')
                ->withMime('application/pdf'),
        ];
    }
}

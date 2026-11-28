<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** The admin's answer to a contact-form message, sent as plain text with the original message quoted below. */
class InquiryReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Inquiry $inquiry,
        public string $replySubject,
        public string $replyBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->replySubject,
            // Customers who hit "Reply" write to the desk mailbox.
            replyTo: [new Address(config('app.desk_email'), config('app.name'))],
        );
    }

    public function content(): Content
    {
        $quoted = collect(preg_split('/\R/', $this->inquiry->message))
            ->map(fn (string $line) => '> '.$line)
            ->implode("\n");

        return new Content(
            text: 'emails.inquiry-reply',
            with: ['quoted' => $quoted],
        );
    }
}

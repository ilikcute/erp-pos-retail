<?php

namespace App\Mail;

use App\Models\POS\SalesTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SalesReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public SalesTransaction $transaction
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "Sales Receipt #{$this->transaction->transaction_no}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sales.receipt',
            with: [
                'transaction' => $this->transaction,
                'items' => $this->transaction->items,
                'payments' => $this->transaction->payments,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorage(
                path: "receipts/{$this->transaction->transaction_no}.pdf",
            )->as("receipt_{$this->transaction->transaction_no}.pdf"),
        ];
    }
}

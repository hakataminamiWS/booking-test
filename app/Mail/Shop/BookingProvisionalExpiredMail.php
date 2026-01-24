<?php

namespace App\Mail\Shop;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class BookingProvisionalExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Booking $booking, public bool $forOwner = false)
    {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $shop = $this->booking->shop;

        $subject = $this->forOwner
            ? '【' . $shop->name . '】仮予約が自動キャンセルされました（期限切れ）'
            : '【' . $shop->name . '】仮予約キャンセルのお知らせ';

        return new Envelope(
            subject: $subject,
            replyTo: [
                new Address($shop->email, $shop->name),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = $this->forOwner
            ? 'emails.shop.booking_provisional_expired_owner'
            : 'emails.shop.booking_provisional_expired';

        return new Content(
            text: $view,
        );
    }
}

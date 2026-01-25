<?php

namespace App\Mail\Shop;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class BookingProvisionalMail extends Mailable
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
            ? '【' . $shop->name . '】仮予約が入りました（要承認）'
            : '【' . $shop->name . '】仮予約を受け付けました';

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
            ? 'emails.shop.booking_provisional_owner'
            : 'emails.shop.booking_provisional';

        // 予約確定用URL (予約者向けのみ)
        $verifyUrl = null;
        if (!$this->forOwner && $this->booking->provisionalBooking) {
            $verifyUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'guest.bookings.verify',
                $this->booking->provisionalBooking->expires_at,
                ['shop' => $this->booking->shop->slug, 'booking' => $this->booking->id]
            );
        }

        return new Content(
            text: $view,
            with: [
                'verifyUrl' => $verifyUrl,
            ],
        );
    }
}

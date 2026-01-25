<?php

namespace App\Mail\Shop;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class BookingUpdatedMail extends Mailable
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
            ? '【' . $shop->name . '】予約が変更されました（オーナー控え）'
            : '【' . $shop->name . '】予約変更のお知らせ';

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
            ? 'emails.shop.booking_updated_owner'
            : 'emails.shop.booking_updated';

        // キャンセル用URL (予約者向けのみ) - 変更時もキャンセル期限内ならキャンセル可能
        $cancelUrl = null;
        if (!$this->forOwner) {
            $deadlineService = app(\App\Services\CancellationDeadlineService::class);
            $deadline = $deadlineService->calculate($this->booking->shop, $this->booking->menu, $this->booking->start_at);
            
            $cancelUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'guest.bookings.cancel.show',
                $deadline,
                ['shop' => $this->booking->shop->slug, 'booking' => $this->booking->id]
            );
        }

        return new Content(
            text: $view,
            with: [
                'cancelUrl' => $cancelUrl,
                'deadline' => isset($deadline) ? $deadline : null,
            ],
        );
    }
}

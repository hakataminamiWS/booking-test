<?php

namespace App\Notifications\Shop;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProvisionalBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): \Illuminate\Mail\Mailable
    {
        $isOwner = $notifiable instanceof \App\Models\User;

        return (new \App\Mail\Shop\BookingProvisionalMail($this->booking, $isOwner))
            ->to($notifiable->email);
    }
}

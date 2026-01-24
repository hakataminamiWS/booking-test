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

    public function toMail(object $notifiable): MailMessage
    {
        $shop = $this->booking->shop;

        return (new MailMessage)
            ->subject('【' . $shop->name . '】仮予約を受け付けました')
            ->replyTo($shop->email, $shop->name)
            ->greeting($this->booking->booker_name . ' 様')
            ->line('仮予約を受け付けました。')
            ->line('店舗からの確定連絡をお待ちください。')
            ->line('--------------------------------------------------')
            ->line('店舗: ' . $shop->name)
            ->line('日時: ' . $this->booking->start_at->format('Y年m月d日 H:i'))
            ->line('メニュー: ' . $this->booking->menu_name)
            ->line('--------------------------------------------------')
            ->line('※この時点ではまだ予約は確定しておりません。');
    }
}

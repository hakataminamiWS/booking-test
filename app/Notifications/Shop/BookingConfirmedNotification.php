<?php

namespace App\Notifications\Shop;

use App\Models\Booking;
use App\Services\BookingCancellationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
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
        
        // Generate Cancellation Link
        $service = app(BookingCancellationService::class);
        $token = $service->generateToken($this->booking);
        // Assuming route name is 'guest.bookings.cancel.show' per plan
        $cancelUrl = route('guest.bookings.cancel.show', ['token' => $token]);

        return (new MailMessage)
            ->subject('【' . $shop->name . '】予約確定のお知らせ')
            ->replyTo($shop->email, $shop->name)
            ->greeting($this->booking->booker_name . ' 様')
            ->line('以下の内容で予約が確定しました。')
            ->line('--------------------------------------------------')
            ->line('店舗: ' . $shop->name)
            ->line('日時: ' . $this->booking->start_at->format('Y年m月d日 H:i'))
            ->line('メニュー: ' . $this->booking->menu_name)
            ->line('金額: ¥' . number_format($this->booking->menu_price)) // Considering options might change price, but using snapshot
            ->line('--------------------------------------------------')
            ->action('予約内容の確認・キャンセル', $cancelUrl)
            ->line('ご来店を心よりお待ちしております。');
    }
}

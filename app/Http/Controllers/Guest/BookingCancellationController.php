<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Services\BookingCancellationService;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingCancellationController extends Controller
{
    public function show(\App\Models\Shop $shop, Booking $booking)
    {
        // 既にキャンセル済みの場合
        if ($booking->status === 'cancelled') {
             return redirect()->route('shop.entry', $shop->slug)->with('error', 'この予約は既にキャンセルされています。');
        }

        // キャンセル期限チェック
        $deadlineService = app(\App\Services\CancellationDeadlineService::class);
        $cancelDeadline = $deadlineService->calculate($shop, $booking->menu, $booking->start_at);
        
        if (now()->gt($cancelDeadline)) {
            abort(403, 'キャンセル可能な期限を過ぎています。店舗へ直接お問い合わせください。');
        }
        
        // Eager load necessary relations for display
        $booking->load(['shop', 'menu', 'bookingOptions']);

        // POST用の署名付きURLを生成 (有効期限はキャンセル期限と同じ)
        $cancelUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'guest.bookings.cancel.perform',
            $cancelDeadline,
            ['shop' => $shop->slug, 'booking' => $booking->id]
        );

        // 必要な情報のみを許可 (ホワイトリスト)
        $booking->setVisible([
            'id', 'start_at', 'end_at', 
            'menu_name', 'menu_price', 'menu_duration', 
            'assigned_staff_name', 
            'note_from_booker',
            'status', // Cancel.vueで使用
            'bookingOptions', 
            'menu', 
            'shop'
        ]);

        return view('guest.bookings.cancel', [
            'booking' => $booking,
            'cancelUrl' => $cancelUrl,
            // tokenは不要
        ]);
    }

    public function perform(\App\Models\Shop $shop, Booking $booking, BookingCancellationService $service)
    {
        // 既にキャンセル済みの場合
        if ($booking->status === 'cancelled') {
            return redirect()->back()->with('error', 'この予約は既にキャンセルされています。');
        }

        // キャンセル期限チェック (Double Check)
        $deadlineService = app(\App\Services\CancellationDeadlineService::class);
        $cancelDeadline = $deadlineService->calculate($shop, $booking->menu, $booking->start_at);

        if (now()->gt($cancelDeadline)) {
            abort(403, 'キャンセル可能な期限を過ぎています。店舗へ直接お問い合わせください。');
        }

        $service->cancel($booking);
        
        // Notify cancellation (Booker)
        $booking->booker->notify(new \App\Notifications\Shop\BookingCancelledNotification($booking));

        // Notify cancellation (Shop)
        if ($shop->email) {
            $booking->shop->notify(new \App\Notifications\Shop\BookingCancelledNotification($booking));
        }

        // 必要な情報のみを許可 (ホワイトリスト)
        $booking->setVisible([
            'id', 'start_at', 'end_at', 
            'menu_name', 'menu_price', 'menu_duration', 
            'assigned_staff_name', 
            'note_from_booker',
            'status',
            'bookingOptions', 
            'menu', 
            'shop'
        ]);

        // キャンセル完了画面を表示
        return view('guest.bookings.cancelled', [
            'shop' => $shop,
            'booking' => $booking,
        ]);
    }
}

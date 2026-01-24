<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Services\BookingCancellationService;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingCancellationController extends Controller
{
    public function show(string $token, BookingCancellationService $service)
    {
        $booking = $service->resolveBooking($token);

        if (!$booking) {
            abort(404, 'キャンセル期限切れ、または無効なURLです。');
        }
        
        // Eager load necessary relations for display
        $booking->load(['shop', 'menu', 'bookingOptions']);

        return view('guest.bookings.cancel', [
            'booking' => $booking,
            'token' => $token,
        ]);
    }

    public function perform(string $token, BookingCancellationService $service)
    {
        $booking = $service->resolveBooking($token);

        if (!$booking) {
            abort(404);
        }

        $service->cancel($booking);
        
        // Notify cancellation
        $booking->booker->notify(new \App\Notifications\Shop\BookingCancelledNotification($booking));

        // Use Inertia-like redirect or return view with success prop?
        // Since we are posting to this same controller, we can just return back with success (if we were staying on same page)
        // Or render a success view.
        // Let's redirect back and let the Vue component handle "success" state if it persists, 
        // or actually, since the booking is now cancelled, showing the "Cancel" form again might be weird if we check status.
        // But the service might allow cancelling again (idempotent).
        
        return redirect()->back()->with('success', '予約をキャンセルしました。');
    }
}

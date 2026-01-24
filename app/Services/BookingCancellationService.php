<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingCancellationToken;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingCancellationService
{
    /**
     * Cancel a booking.
     */
    public function cancel(Booking $booking): void
    {
        $booking->update(['status' => 'cancelled']);
    }
}

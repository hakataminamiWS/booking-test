<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingCancellationToken;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingCancellationService
{
    /**
     * Generate a cancellation token for a booking.
     */
    public function generateToken(Booking $booking): string
    {
        // Delete existing token if any
        $booking->cancellationToken()->delete();

        $token = Str::random(64);
        
        $booking->cancellationToken()->create([
            'token' => $token,
            'expires_at' => Carbon::now()->addDays(7), // Token valid for 7 days
        ]);

        return $token;
    }

    /**
     * Resolve a booking from a token.
     */
    public function resolveBooking(string $token): ?Booking
    {
        $tokenRecord = BookingCancellationToken::where('token', $token)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$tokenRecord) {
            return null;
        }

        return $tokenRecord->booking;
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Booking $booking): void
    {
        $booking->update(['status' => 'cancelled']);
    }
}

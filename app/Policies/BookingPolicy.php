<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Booking;
use App\Models\Shop;

class BookingPolicy
{
    /**
     * Determine whether the user can view any bookings.
     */
    public function viewAny(User $user): bool
    {
        // Only admins, owners, or staff can view all bookings
        return $user->isAdmin() || $user->shops()->wherePivot('role', 'owner')->exists() || $user->shops()->wherePivot('role', 'staff')->exists();
    }

    /**
     * Determine whether the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        // User can view their own booking
        if ($user->id === $booking->booker_id) {
            return true;
        }

        // Admins, owners, or staff of the shop can view the booking
        return $user->isAdmin() || $user->isOwnerOf($booking->shop) || $user->isStaffOf($booking->shop);
    }

    /**
     * Determine whether the user can create bookings.
     */
    public function create(User $user): bool
    {
        return true; // Anyone (including guests) can create bookings
    }

    /**
     * Determine whether the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        // User can update their own booking
        if ($user->id === $booking->booker_id) {
            return true;
        }

        // Admins, owners, or staff of the shop can update the booking
        return $user->isAdmin() || $user->isOwnerOf($booking->shop) || $user->isStaffOf($booking->shop);
    }

    /**
     * Determine whether the user can delete the booking.
     */
    public function delete(User $user, Booking $booking): bool
    {
        // User can delete their own booking
        if ($user->id === $booking->booker_id) {
            return true;
        }

        // Admins, owners, or staff of the shop can delete the booking
        return $user->isAdmin() || $user->isOwnerOf($booking->shop) || $user->isStaffOf($booking->shop);
    }
}

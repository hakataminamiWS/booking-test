<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\Menu;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AvailabilityService
{
    public function getAvailableSlots(Shop $shop, Menu $menu, string $date): array
    {
        // 1. Define shop hours (hardcoded for now)
        $openingTime = Carbon::parse($date)->setTime(9, 0);
        $closingTime = Carbon::parse($date)->setTime(18, 0);

        // 2. Get bookings for the day
        $bookings = $shop->bookings()
            ->with('menu') // Eager load menu relationship
            ->whereDate('start_at', $date)
            ->get();

        // 3. Create time slots for the day
        $slotInterval = 30; // minutes
        // Exclude closing time from the period
        $period = new CarbonPeriod($openingTime, "{$slotInterval} minutes", $closingTime->copy()->subMinute());
        $availableSlots = [];

        foreach ($period as $slot) {
            $isAvailable = true;
            $potentialBookingEnd = $slot->copy()->addMinutes($menu->duration);

            // Ensure the slot doesn't end after closing time
            if ($potentialBookingEnd->gt($closingTime)) {
                continue;
            }

            // 4. Check against existing bookings
            foreach ($bookings as $booking) {
                $bookingStart = Carbon::parse($booking->start_at);
                // Ensure booking has a menu before accessing duration
                if ($booking->menu) {
                    $bookingEnd = $bookingStart->copy()->addMinutes($booking->menu->duration);

                    // Check for overlap
                    if ($slot->lt($bookingEnd) && $potentialBookingEnd->gt($bookingStart)) {
                        $isAvailable = false;
                        break;
                    }
                }
            }

            if ($isAvailable) {
                $availableSlots[] = $slot->toIso8601String();
            }
        }

        return $availableSlots;
    }
}

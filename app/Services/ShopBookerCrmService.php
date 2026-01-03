<?php

namespace App\Services;

use App\Models\ShopBooker;
use Illuminate\Support\Facades\DB;

class ShopBookerCrmService
{
    /**
     * Update CRM stats for a specific booker (booking count, last booking date).
     *
     * @param ShopBooker $booker
     * @return void
     */
    public function updateStats(ShopBooker $booker): void
    {
        // Calculate stats
        $stats = $booker->bookings()
            ->selectRaw('count(*) as booking_count, max(start_at) as last_booking_at')
            ->where('status', '!=', 'cancelled')
            ->first();

        // Update or create CRM record
        $booker->crm()->updateOrCreate(
            ['shop_booker_id' => $booker->id],
            [
                'booking_count' => $stats->booking_count ?? 0,
                'last_booking_at' => $stats->last_booking_at,
            ]
        );
    }
}

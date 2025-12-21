<?php

namespace App\Http\Controllers\Api\Booker;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopMenu;
use App\Models\ShopOption;
use App\Models\ShopStaff;
use App\Services\TimeSlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AvailableSlotController extends Controller
{
    protected $timeSlotService;

    public function __construct(TimeSlotService $timeSlotService)
    {
        $this->timeSlotService = $timeSlotService;
    }

    /**
     * Get available time slots for the booker.
     */
    public function index(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'menu_id' => ['required', 'integer', 'exists:shop_menus,id'],
            'option_ids' => ['nullable', 'array'],
            'option_ids.*' => ['integer', 'exists:shop_options,id'],
            'staff_id' => ['nullable', 'integer', 'exists:shop_staffs,id'],
        ]);

        $userTimezone = $shop->timezone;
        $date = Carbon::parse($validated['date'], $userTimezone);
        $currentTime = Carbon::now();

        $bookingIntervalMinutes = $shop->time_slot_interval ?? 15;

        // Calculate total duration
        $menu = ShopMenu::findOrFail($validated['menu_id']);
        $options = ShopOption::find($validated['option_ids'] ?? []);
        $totalDurationMinutes = $menu->duration + $options->sum('additional_duration');

        $bookingDeadlineMinutes = $menu->deadline_minutes ?? $shop->booking_deadline_minutes ?? 0;

        // Get candidate staffs
        $staffs = [];
        if (!empty($validated['staff_id'])) {
            $staffs = [ShopStaff::findOrFail($validated['staff_id'])];
        } else {
            // Find all staffs available for this menu
            $staffs = $menu->staffs()->where('shop_id', $shop->id)->get();
            if ($staffs->isEmpty()) {
                // If no specific staff assigned to menu, assume all shop staffs can handle it
                $staffs = $shop->staffs()->get();
            }
        }

        $allAvailableSlots = [];

        foreach ($staffs as $staff) {
            // Get shifts for this staff
            $startOfDayInUserTz = $date->copy()->startOfDay();
            $endOfDayInUserTz = $date->copy()->endOfDay();

            $startOfDayUtc = $startOfDayInUserTz->copy()->setTimezone(config('app.timezone'));
            $endOfDayUtc = $endOfDayInUserTz->copy()->setTimezone(config('app.timezone'));

            $shifts = $staff->schedules()
                ->whereBetween('workable_start_at', [$startOfDayUtc, $endOfDayUtc])
                ->get()
                ->map(fn($schedule) => (object)[
                    'start' => $schedule->workable_start_at->setTimezone($userTimezone)->format('H:i'),
                    'end' => $schedule->workable_end_at->setTimezone($userTimezone)->format('H:i')
                ])
                ->all();

            // Get existing bookings for this staff
            $existingBookings = $staff->bookings()
                ->where(function($query) use ($startOfDayUtc, $endOfDayUtc) {
                    $query->whereBetween('start_at', [$startOfDayUtc, $endOfDayUtc])
                          ->orWhereBetween('end_at', [$startOfDayUtc, $endOfDayUtc]);
                })
                ->whereIn('status', ['pending', 'confirmed'])
                ->get()
                ->map(fn($booking) => (object)[
                    'start' => Carbon::parse($booking->start_at)->setTimezone($userTimezone)->format('H:i'),
                    'end' => Carbon::parse($booking->end_at)->setTimezone($userTimezone)->format('H:i')
                ])
                ->all();

            $slots = $this->timeSlotService->calculateAvailableTimeSlots(
                $date,
                $bookingIntervalMinutes,
                $shifts,
                $existingBookings,
                $totalDurationMinutes,
                $bookingDeadlineMinutes,
                $currentTime
            );

            $allAvailableSlots = array_merge($allAvailableSlots, $slots);
        }

        // Return unique and sorted slots
        $uniqueSlots = array_unique($allAvailableSlots);
        sort($uniqueSlots);

        return response()->json($uniqueSlots);
    }
}

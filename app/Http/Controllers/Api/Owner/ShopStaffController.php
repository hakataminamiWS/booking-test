<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Owner\IndexShopStaffsRequest;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;

class ShopStaffController extends Controller
{
    public function index(IndexShopStaffsRequest $request, Shop $shop): JsonResponse
    {
        $query = $shop->staffs()->with('profile');

        // Filtering
        if ($request->filled('nickname')) {
            $query->whereHas('profile', function ($q) use ($request) {
                $q->where('nickname', 'like', '%'.$request->nickname.'%');
            });
        }

        if ($request->filled('type')) {
            if ($request->type === 'user') {
                $query->whereNotNull('user_id');
            } elseif ($request->type === 'frame') {
                $query->whereNull('user_id');
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortBy === 'nickname') {
            $query->select('shop_staffs.*')->leftJoin('shop_staff_profiles', 'shop_staffs.id', '=', 'shop_staff_profiles.shop_staff_id')
                ->orderBy('shop_staff_profiles.nickname', $sortOrder);
        } elseif ($sortBy === 'type') {
            $query->orderBy('user_id', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $staffs = $query->paginate($request->input('per_page', 20));

        return response()->json($staffs);
    }

    public function getSchedule(\Illuminate\Http\Request $request, Shop $shop, \App\Models\ShopStaff $staff): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $timezone = $shop->timezone;
        $date = \Carbon\Carbon::parse($request->input('date'), $timezone);
        
        // Find individual schedule for the date
        // Note: workable_start_at is in UTC.
        // We need to find a schedule that overlaps or is contained within the target date (in shop timezone)
        // For simplicity as requested, we look for schedules that start within the target day (shop timezone).
        
        $startOfDay = $date->copy()->startOfDay()->setTimezone('UTC');
        $endOfDay = $date->copy()->endOfDay()->setTimezone('UTC');

        $schedule = $staff->schedules()
            ->whereBetween('workable_start_at', [$startOfDay, $endOfDay])
            ->first();

        $scheduleData = null;
        if ($schedule) {
            $scheduleData = [
                'start' => $schedule->workable_start_at->setTimezone($timezone)->format('H:i'),
                'end' => $schedule->workable_end_at->setTimezone($timezone)->format('H:i'),
            ];
        }

        // Get bookings for the staff on that date
        // Bookings start_at is stored in DB (usually UTC or configured app timezone, but assumed consistent with Model access)
        // We filter by date in Shop Timezone
        $bookings = $staff->bookings()
            ->whereBetween('start_at', [$date->copy()->startOfDay()->setTimezone(config('app.timezone')), $date->copy()->endOfDay()->setTimezone(config('app.timezone'))])
            ->with('booker') // Load booker info
            ->get()
            ->map(function ($booking) use ($timezone) {
                $start = \Carbon\Carbon::parse($booking->start_at)->setTimezone($timezone);
                $end = \Carbon\Carbon::parse($booking->end_at)->setTimezone($timezone);
                return [
                    'id' => $booking->id,
                    'start' => $start->format('H:i'),
                    'end' => $end->format('H:i'),
                    'booker_name' => $booking->booker_name,
                    'booker_number' => $booking->booker?->number, // Optional
                ];
            });

        return response()->json([
            'schedule' => $scheduleData,
            'bookings' => $bookings,
        ]);
    }
}

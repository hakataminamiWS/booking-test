<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\TimeSlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopStaffController extends Controller
{
    protected $timeSlotService;

    public function __construct(TimeSlotService $timeSlotService)
    {
        $this->timeSlotService = $timeSlotService;
    }

    public function index(Request $request, Shop $shop): JsonResponse
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
            // Validate sort column to prevent SQL injection
            $allowedSorts = ['id', 'created_at'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                 $query->orderBy('created_at', 'desc');
            }
        }

        $staffs = $query->paginate($request->input('per_page', 20));

        return response()->json($staffs);
    }

    public function getSchedule(Request $request, Shop $shop, \App\Models\ShopStaff $staff): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $timezone = $shop->timezone;
        $date = \Carbon\Carbon::parse($request->input('date'), $timezone);
        
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

        // アクティブな予約を取得（サービスを利用）
        $bookings = $this->timeSlotService->getActiveBookingsForDate($staff, $date, $timezone)
            ->load('booker')
            ->map(function ($booking) use ($timezone) {
                $start = \Carbon\Carbon::parse($booking->start_at)->setTimezone($timezone);
                $end = \Carbon\Carbon::parse($booking->end_at)->setTimezone($timezone);
                return [
                    'id' => $booking->id,
                    'start' => $start->format('H:i'),
                    'end' => $end->format('H:i'),
                    'booker_name' => $booking->booker_name,
                    'booker_number' => $booking->booker?->number,
                ];
            });

        return response()->json([
            'schedule' => $scheduleData,
            'bookings' => $bookings,
        ]);
    }
}

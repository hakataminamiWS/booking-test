<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * スタッフ用ダッシュボード表示
     */
    public function index(Shop $shop)
    {
        $timezone = $shop->timezone;
        $now = Carbon::now($timezone);
        
        // 店舗タイムゾーンでの「今日」をUTCに変換
        $todayStart = $now->copy()->startOfDay()->setTimezone('UTC');
        $todayEnd = $now->copy()->endOfDay()->setTimezone('UTC');
        
        // 店舗タイムゾーンでの「来週」をUTCに変換
        $nextWeekStart = $todayStart->copy();
        $nextWeekEnd = $now->copy()->addDays(7)->endOfDay()->setTimezone('UTC');

        // 現在ログイン中のユーザーに紐づくスタッフを取得
        $currentUser = Auth::user();
        $currentStaff = $shop->staffs()->where('user_id', $currentUser->id)->first();

        // 1. Shift Status
        // Check identifying staff with missing shifts for the next 7 days
        $staffs = $shop->staffs()->with(['user', 'profile', 'schedules' => function ($query) use ($nextWeekStart, $nextWeekEnd) {
            $query->whereBetween('workable_start_at', [$nextWeekStart, $nextWeekEnd]);
        }])->get();

        $unregisteredStaff = [];
        foreach ($staffs as $staff) {
            if ($staff->schedules->isEmpty()) {
                $unregisteredStaff[] = [
                    'id' => $staff->id,
                    'name' => $staff->profile->nickname ?? $staff->user->name ?? "Staff #{$staff->id}",
                ];
            }
        }

        $shifts = [
            'status' => empty($unregisteredStaff) ? 'complete' : 'incomplete',
            'unregistered_staff' => $unregisteredStaff,
            'range_start' => $nextWeekStart->timezone($shop->timezone)->format('Y/m/d'),
            'range_end' => $nextWeekEnd->timezone($shop->timezone)->format('m/d'),
        ];

        // 2. Today's Cancellations
        $todayCancellations = $shop->bookings()
            ->whereBetween('updated_at', [$todayStart, $todayEnd])
            ->where('status', 'cancelled')
            ->count();

        $today = now()->timezone($shop->timezone)->format('Y-m-d');
        $cancellations = [
            'count' => $todayCancellations,
            'actionUrl' => $todayCancellations > 0 
                ? "/shops/{$shop->slug}/staff/bookings?status=cancelled&start_at_from={$today}&start_at_to={$today}" 
                : null,
        ];

        // 3. Today's Bookings & Summary
        $bookings = $shop->bookings()
            ->with(['staff.profile', 'menu', 'booker'])
            ->whereBetween('start_at', [$todayStart, $todayEnd])
            ->whereIn('status', ['confirmed', 'visited'])
            ->orderBy('start_at')
            ->get()
            ->map(function ($booking) use ($timezone, $now) {
                return [
                    'id' => $booking->id,
                    'start_at' => $booking->start_at->setTimezone($timezone)->format('H:i'),
                    'end_at' => $booking->end_at->setTimezone($timezone)->format('H:i'),
                    'customer_name' => $booking->booker_name,
                    'menu_name' => $booking->menu_name,
                    'staff_name' => $booking->assigned_staff_name,
                    'staff_image_url' => $booking->staff && $booking->staff->profile && $booking->staff->profile->image_url
                        ? Storage::disk('public')->url($booking->staff->profile->image_url)
                        : null,
                    'status' => $booking->status,
                    'is_past' => $booking->end_at->setTimezone($timezone)->lessThan($now),
                    'note_from_booker' => $booking->note_from_booker,
                ];
            });

        // 4. Next Arrival logic
        $nowUtc = Carbon::now('UTC');
        $nextArrivalId = null;
        $nextBooking = $shop->bookings()
            ->whereBetween('start_at', [$todayStart, $todayEnd])
            ->where('status', 'confirmed')
            ->where('start_at', '>=', $nowUtc)
            ->orderBy('start_at')
            ->first();

        if ($nextBooking) {
            $nextArrivalId = $nextBooking->id;
        }

        return view('staff.shops.dashboard', [
            'shop' => $shop,
            'bookings' => $bookings,
            'cancellations' => $cancellations,
            'shifts' => $shifts,
            'summary' => [
                'total_today' => $bookings->count(),
            ],
            'next_arrival_id' => $nextArrivalId,
            'current_user_staff_id' => $currentStaff?->id,
        ]);
    }
}

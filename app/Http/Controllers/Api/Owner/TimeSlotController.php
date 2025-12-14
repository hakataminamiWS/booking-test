<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopMenu;
use App\Models\ShopOption;
use App\Models\ShopStaff;
use App\Services\TimeSlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class TimeSlotController extends Controller
{
    /**
     * 指定されたスタッフと日付の予約可能な時間枠を取得する
     */
    public function index(Request $request, Shop $shop, ShopStaff $staff, TimeSlotService $timeSlotService)
    {
        // TODO: 認証済みオーナーがこのスタッフを管理する権限があるか、ポリシー等でチェックする

        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'menu_id' => ['required', 'integer', 'exists:shop_menus,id'],
            'option_ids' => ['nullable', 'array'],
            'option_ids.*' => ['integer', 'exists:shop_options,id'],
        ]);

        $userTimezone = $shop->timezone;
        $date = Carbon::parse($validated['date'], $userTimezone);
        $currentTime = Carbon::now();

        // --- TimeSlotServiceに渡す引数を準備 ---

        $bookingIntervalMinutes = $shop->time_slot_interval ?? 15;

        // その日のシフト情報を取得
        // ユーザーのタイムゾーンでの1日の始まりと終わり
        $startOfDayInUserTz = $date->copy()->startOfDay();
        $endOfDayInUserTz = $date->copy()->endOfDay();

        // UTCに変換して検索範囲を設定
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

        // その日の既存予約を取得
        $existingBookings = $staff->bookings()
            ->whereDate('start_at', $date)
            ->get()
            ->map(fn($booking) => (object)[
                'start' => Carbon::parse($booking->start_at)->format('H:i'),
                'end' => Carbon::parse($booking->end_at)->format('H:i')
            ])
            ->all();

        // メニューとオプションから合計所要時間を計算
        $menu = ShopMenu::findOrFail($validated['menu_id']);
        $options = ShopOption::find($validated['option_ids'] ?? []);
        $totalDurationMinutes = $menu->duration + $options->sum('additional_duration');

        // 締め切り時間を取得
        $bookingDeadlineMinutes = $menu->deadline_minutes ?? $shop->booking_deadline_minutes ?? 0;

        // --- サービスの呼び出し ---
        Log::debug('TimeSlotService Arguments', [
            'date' => $date,
            'bookingIntervalMinutes' => $bookingIntervalMinutes,
            'shifts' => $shifts,
            'existingBookings' => $existingBookings,
            'totalDurationMinutes' => $totalDurationMinutes,
            'bookingDeadlineMinutes' => $bookingDeadlineMinutes,
            'currentTime' => $currentTime,
        ]);
        $availableTimeSlots = $timeSlotService->calculateAvailableTimeSlots(
            $date,
            $bookingIntervalMinutes,
            $shifts,
            $existingBookings,
            $totalDurationMinutes,
            $bookingDeadlineMinutes,
            $currentTime
        );

        return response()->json($availableTimeSlots);
    }
}

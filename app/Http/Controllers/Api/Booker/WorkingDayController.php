<?php

namespace App\Http\Controllers\Api\Booker;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopStaff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WorkingDayController extends Controller
{
    /**
     * 指定されたスタッフの稼働日（予約可能日）を月単位で取得する
     */
    public function index(Request $request, Shop $shop, ShopStaff $staff): JsonResponse
    {
        // 基本的なバリデーション (スタッフが店舗に属しているか)
        if ($staff->shop_id !== $shop->id) {
            return response()->json(['error' => 'Invalid staff for this shop.'], 403);
        }

        $request->validate([
            'year_month' => ['required', 'date_format:Y-m'],
            'menu_id' => ['nullable', 'integer', 'exists:shop_menus,id'],
        ]);

        $yearMonth = $request->input('year_month');
        $startOfMonth = Carbon::parse($yearMonth . '-01')->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        // タイムゾーンの取得 (店舗設定を優先)
        $timezone = $shop->timezone;

        // 予約締め切り時間の取得
        $deadlineMinutes = $shop->booking_deadline_minutes ?? 0;
        if ($request->filled('menu_id')) {
            $menu = \App\Models\ShopMenu::find($request->input('menu_id'));
            if ($menu && $menu->requires_booking_deadline) {
                $deadlineMinutes = $menu->booking_deadline_minutes ?? $deadlineMinutes;
            }
        }

        // 予約可能境界（デッドライン）
        $deadlineBoundary = Carbon::now()->addMinutes($deadlineMinutes);

        // UTC範囲の算出
        $startUtc = $startOfMonth->copy()->setTimezone('UTC');
        $endUtc = $endOfMonth->copy()->setTimezone('UTC');

        $scheduledDays = $staff->schedules()
            ->whereBetween('workable_start_at', [$startUtc, $endUtc])
            ->get()
            ->filter(function ($schedule) use ($deadlineBoundary) {
                // 勤務終了時刻がデッドラインを過ぎているものだけを残す
                return $schedule->workable_end_at->isAfter($deadlineBoundary);
            })
            ->map(function ($schedule) use ($timezone) {
                return $schedule->workable_start_at->setTimezone($timezone)->format('Y-m-d');
            })
            ->unique()
            ->values()
            ->all();

        return response()->json($scheduledDays);
    }
}

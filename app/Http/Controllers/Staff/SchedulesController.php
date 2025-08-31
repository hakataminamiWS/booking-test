<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // 日付操作用

class SchedulesController extends Controller
{
    public function index(Request $request, $shop_id)
    {
        // 現在の週の開始日（月曜日）を取得
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        // クエリパラメータで週の開始日が指定されていればそれを使う
        if ($request->has('week_start_date')) {
            $startOfWeek = Carbon::parse($request->input('week_start_date'))->startOfWeek(Carbon::MONDAY);
        }

        $scheduleData = [
            'week_start_date' => $startOfWeek->format('Y-m-d'),
            'week_end_date' => $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d'),
            'days' => [],
            'general_settings' => [
                'default_open_time' => '09:00',
                'default_close_time' => '18:00',
                'default_break_start' => '12:00',
                'default_break_end' => '13:00',
            ],
            'considerations' => [
                'shop_holiday_reflection' => '店舗の店休日（オーナーが設定）は、この画面に反映され、スタッフは予約を受け付けられなくなります。',
                'flexible_booking_slots' => '予約受付可能時間、予約受付終了時間は、予約の最終受付時間を示します。',
            ]
        ];

        // 1週間分のダミースケジュールを生成
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $isHoliday = ($date->dayOfWeek === Carbon::SATURDAY || $date->dayOfWeek === Carbon::SUNDAY); // 土日を休日とするダミー
            $scheduleData['days'][] = [
                'date' => $date->format('Y-m-d'),
                'day_of_week' => $date->isoFormat('ddd'), // 月, 火, 水...
                'is_working_day' => !$isHoliday,
                'open_time' => $isHoliday ? null : $scheduleData['general_settings']['default_open_time'],
                'close_time' => $isHoliday ? null : $scheduleData['general_settings']['default_close_time'],
                'break_start' => $isHoliday ? null : $scheduleData['general_settings']['default_break_start'],
                'break_end' => $isHoliday ? null : $scheduleData['general_settings']['default_break_end'],
                'is_shop_holiday' => false, // オーナー設定の店休日（ダミー）
            ];
        }

        return view('staff.schedules.index', compact('shop_id', 'scheduleData'));
    }

    public function store(Request $request, $shop_id)
    {
        // バリデーションルール（簡略化）
        $rules = [
            'week_start_date' => 'required|date_format:Y-m-d',
            'days' => 'required|array|min:7|max:7',
            'days.*.date' => 'required|date_format:Y-m-d',
            'days.*.is_working_day' => 'required|boolean',
            'days.*.open_time' => 'nullable|date_format:H:i',
            'days.*.close_time' => 'nullable|date_format:H:i',
            'days.*.break_start' => 'nullable|date_format:H:i',
            'days.*.break_end' => 'nullable|date_format:H:i',
        ];

        $validated = $request->validate($rules);

        Log::info("Staffが予約可能枠を更新しました: shop_id={$shop_id}", $validated);

        return redirect()->route('staff.schedules.index', ['shop_id' => $shop_id, 'week_start_date' => $validated['week_start_date']])
            ->with('status', '予約可能枠が更新されました。');
    }
}

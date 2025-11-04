<?php

namespace App\Http\Controllers\Owner;

use App\Http\Requests\Owner\UpdateShopStaffScheduleRequest;
use App\Models\Shop;
use App\Models\ShopBusinessHoursRegular;
use App\Models\ShopStaff;
use App\Models\ShopStaffSchedule;
use App\Models\ShopSpecialOpenDay;
use App\Models\ShopSpecialClosedDay;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ShopStaffScheduleController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Shop $shop)
    {
        $this->authorize('view', $shop);

        $month = $request->input('month', today()->format('Y-m'));
        $targetMonth = Carbon::createFromFormat('Y-m', $month, $shop->timezone)->startOfMonth();

        $startOfMonth = $targetMonth->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $endOfMonth = $targetMonth->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $weekHeaders = [];
        $currentDate = $startOfMonth->copy();
        while ($currentDate <= $endOfMonth) {
            $weekHeaders[] = [
                'start' => $currentDate->copy(),
                'end' => $currentDate->copy()->endOfWeek(Carbon::SATURDAY),
            ];
            $currentDate->addWeek();
        }

        $staffs = $shop->staffs()->with('profile')->get();

        $schedules = ShopStaffSchedule::whereIn('shop_staff_id', $staffs->pluck('id'))
            ->whereBetween('workable_start_at', [
                $startOfMonth->copy()->setTimezone('UTC'),
                $endOfMonth->copy()->endOfDay()->setTimezone('UTC'),
            ])
            ->get();

        $weeksWithShiftStatus = collect($weekHeaders)->map(function ($week) use ($staffs, $schedules, $shop) {
            $statuses = $staffs->mapWithKeys(function ($staff) use ($week, $schedules, $shop) {
                $hasSchedule = $schedules
                    ->where('shop_staff_id', $staff->id)
                    ->some(function ($schedule) use ($week, $shop) {
                        $scheduleStart = Carbon::parse($schedule->workable_start_at)->setTimezone($shop->timezone);
                        return $scheduleStart->between($week['start'], $week['end']);
                    });
                return [$staff->id => $hasSchedule ? 'entered' : 'not_entered'];
            });

            return [
                'week' => $week,
                'statuses' => $statuses,
            ];
        });

        return view('owner.shops.staffs.shifts.index', compact(
            'shop',
            'targetMonth',
            'staffs',
            'weeksWithShiftStatus'
        ));
    }

    /**
     * Display the specified resource.
     */
    public function edit(Request $request, Shop $shop, ShopStaff $staff)
    {
        $this->authorize('update', $staff);

        $staff->load('profile'); // 追加

        $date = $request->input('date', today()->toDateString());
        $targetDate = Carbon::parse($date, $shop->timezone);
        $startOfWeek = $targetDate->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = $targetDate->copy()->endOfWeek(Carbon::SATURDAY);

        $schedules = ShopStaffSchedule::where('shop_staff_id', $staff->id)
            ->whereBetween('workable_start_at', [
                $startOfWeek->copy()->setTimezone('UTC'),
                $endOfWeek->copy()->setTimezone('UTC'),
            ])
            ->get();

        $businessHours = ShopBusinessHoursRegular::where('shop_id', $shop->id)->get();
        $specialOpenDays = ShopSpecialOpenDay::where('shop_id', $shop->id)
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->get();
        $specialClosedDays = ShopSpecialClosedDay::where('shop_id', $shop->id)
            ->where(function ($query) use ($startOfWeek, $endOfWeek) {
                $query->where('start_at', '<=', $endOfWeek->toDateString())
                    ->where('end_at', '>=', $startOfWeek->toDateString());
            })->get();

        return view('owner.shops.staffs.shifts.edit', compact(
            'shop',
            'staff',
            'schedules',
            'businessHours',
            'specialOpenDays',
            'specialClosedDays',
            'date'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShopStaffScheduleRequest $request, Shop $shop, ShopStaff $staff)
    {
        $validated = $request->validated();

        $date = $request->input('date', today()->toDateString());
        $targetDate = Carbon::parse($date, $shop->timezone);
        $startOfWeek = $targetDate->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = $targetDate->copy()->endOfWeek(Carbon::SATURDAY);

        // Remove duplicate schedules before transaction
        if (isset($validated['schedules'])) {
            $validated['schedules'] = array_map(function ($shifts) {
                if (empty($shifts)) {
                    return [];
                }
                // Create a unique string for each shift to identify duplicates
                $unique_shifts = array_unique(array_map(function ($shift) {
                    return $shift['start_time'] . '-' . $shift['end_time'];
                }, $shifts));

                // Rebuild the shifts array from the unique strings
                return array_values(array_map(function ($unique_shift) {
                    list($start, $end) = explode('-', $unique_shift);
                    return ['start_time' => $start, 'end_time' => $end];
                }, $unique_shifts));
            }, $validated['schedules']);
        }

        DB::transaction(function () use ($shop, $staff, $startOfWeek, $endOfWeek, $validated) {
            // Delete existing schedules for the week
            ShopStaffSchedule::where('shop_staff_id', $staff->id)
                ->whereBetween('workable_start_at', [
                    $startOfWeek->copy()->setTimezone('UTC'),
                    $endOfWeek->copy()->endOfDay()->setTimezone('UTC'),
                ])
                ->delete();

            // Create new schedules
            if (isset($validated['schedules'])) {
                foreach ($validated['schedules'] as $dayIndex => $shifts) {
                    $currentDate = $startOfWeek->copy()->addDays($dayIndex);
                    foreach ($shifts as $shift) {
                        if (!empty($shift['start_time']) && !empty($shift['end_time'])) {
                            $startAt = Carbon::createFromFormat(
                                'Y-m-d H:i',
                                $currentDate->toDateString() . ' ' . $shift['start_time'],
                                $shop->timezone
                            )->setTimezone(config('app.timezone'));

                            $endAt = Carbon::createFromFormat(
                                'Y-m-d H:i',
                                $currentDate->toDateString() . ' ' . $shift['end_time'],
                                $shop->timezone
                            )->setTimezone(config('app.timezone'));

                            // 終了時刻が開始時刻より前の場合は、日付を1日進める
                            if ($endAt->lessThan($startAt)) {
                                $endAt->addDay();
                            }

                            ShopStaffSchedule::create([
                                'shop_staff_id' => $staff->id,
                                'workable_start_at' => $startAt,
                                'workable_end_at' => $endAt,
                            ]);
                        }
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'シフトを更新しました。');
    }
}

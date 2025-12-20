<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UpdateStaffShiftRequest;
use App\Models\Shop;
use App\Models\ShopBusinessHoursRegular;
use App\Models\ShopStaff;
use App\Models\ShopStaffSchedule;
use App\Models\ShopSpecialOpenDay;
use App\Models\ShopSpecialClosedDay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    /**
     * Display the shift edit form for the authenticated staff.
     */
    public function edit(Request $request, Shop $shop)
    {
        $staff = $this->getAuthenticatedStaff($shop);
        $staff->load('profile');

        $date = $request->input('date', today()->toDateString());
        $targetDate = Carbon::parse($date, $shop->timezone);
        $startOfWeek = $targetDate->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = $targetDate->copy()->endOfWeek(Carbon::SATURDAY);

        $schedules = ShopStaffSchedule::where('shop_staff_id', $staff->id)
            ->whereBetween('workable_start_at', [
                $startOfWeek->copy()->utc(),
                $endOfWeek->copy()->utc(),
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

        return view('staff.shifts.edit', compact(
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
     * Update the shift schedules for the authenticated staff.
     */
    public function update(UpdateStaffShiftRequest $request, Shop $shop)
    {
        $staff = $this->getAuthenticatedStaff($shop);
        $validated = $request->validated();

        $date = $request->input('date', today()->toDateString());
        $targetDate = Carbon::parse($date, $shop->timezone);
        $startOfWeek = $targetDate->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = $targetDate->copy()->endOfWeek(Carbon::SATURDAY);

        DB::transaction(function () use ($shop, $staff, $startOfWeek, $endOfWeek, $validated) {
            // Delete existing schedules for the week
            ShopStaffSchedule::where('shop_staff_id', $staff->id)
                ->whereBetween('workable_start_at', [
                    $startOfWeek->copy()->utc(),
                    $endOfWeek->copy()->endOfDay()->utc(),
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

    private function getAuthenticatedStaff(Shop $shop): ShopStaff
    {
        $staff = ShopStaff::where('shop_id', $shop->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return $staff;
    }
}

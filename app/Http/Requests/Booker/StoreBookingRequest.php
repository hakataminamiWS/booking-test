<?php

namespace App\Http\Requests\Booker;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ShopStaff;
use App\Models\ShopMenu;
use App\Models\ShopOption;
use App\Services\TimeSlotService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'menu_id' => ['required', 'exists:shop_menus,id'],
            'assigned_staff_id' => ['nullable', 'exists:shop_staffs,id'],
            'option_ids' => ['nullable', 'array'],
            'option_ids.*' => ['exists:shop_options,id'],
            'start_at' => ['required', 'date'],
            'note_from_booker' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) return;

            $shop = $this->route('shop');
            $menuId = $this->input('menu_id');
            $staffId = $this->input('assigned_staff_id');
            $startAtStr = $this->input('start_at');
            $optionIds = $this->input('option_ids', []);

            if (!$staffId) return; // 担当スタッフなしの場合はここではチェックしない（TimeSlotService側の挙動に依存）

            $timezone = $shop->timezone;
            $startAt = Carbon::parse($startAtStr, $timezone);
            
            $menu = ShopMenu::findOrFail($menuId);
            $options = ShopOption::find($optionIds);
            $totalDuration = $menu->duration + $options->sum('additional_duration');
            $endAt = $startAt->copy()->addMinutes($totalDuration);

            $staff = ShopStaff::findOrFail($staffId);

            // 1. スタッフのメニュー対応チェック
            if ($menu->requires_staff_assignment) {
                $isAssignedToMenu = DB::table('shop_menu_staffs')
                    ->where('shop_menu_id', $menuId)
                    ->where('shop_staff_id', $staffId)
                    ->exists();
                if (!$isAssignedToMenu) {
                    $validator->errors()->add('assigned_staff_id', '選択されたスタッフはこのメニューを担当できません。');
                }
            } else {
                // 指名不要なメニューの場合は、店舗所属のスタッフであればOK (コントローラー側で担保されているが念のため)
                if ($staff->shop_id !== $shop->id) {
                    $validator->errors()->add('assigned_staff_id', '選択されたスタッフはこの店舗に所属していません。');
                }
            }

            // 2. シフト内チェック
            $startOfDayUtc = $startAt->copy()->startOfDay()->setTimezone(config('app.timezone'));
            $endOfDayUtc = $startAt->copy()->endOfDay()->setTimezone(config('app.timezone'));

            $shifts = $staff->schedules()
                ->whereBetween('workable_start_at', [$startOfDayUtc, $endOfDayUtc])
                ->get()
                ->map(fn($schedule) => (object)[
                    'start' => $schedule->workable_start_at->setTimezone($timezone)->format('H:i'),
                    'end' => $schedule->workable_end_at->setTimezone($timezone)->format('H:i')
                ])
                ->all();

            $timeSlotService = app(TimeSlotService::class);
            if (!$timeSlotService->isWithinShift($startAt, $endAt, $shifts)) {
                $validator->errors()->add('start_at', '選択された時間はスタッフの勤務時間外です。');
            }

            // 3. 重複チェック
            $existingBookings = $staff->bookings()
                ->whereBetween('start_at', [$startOfDayUtc, $endOfDayUtc])
                ->whereIn('status', ['pending', 'confirmed'])
                ->get()
                ->map(fn($booking) => (object)[
                    'start' => Carbon::parse($booking->start_at)->setTimezone($timezone)->format('H:i'),
                    'end' => Carbon::parse($booking->end_at)->setTimezone($timezone)->format('H:i')
                ])
                ->all();

            if ($timeSlotService->hasConflict($startAt, $endAt, $existingBookings)) {
                $validator->errors()->add('start_at', '選択された時間は既に他の予約が入っています。');
            }
        });
    }
}

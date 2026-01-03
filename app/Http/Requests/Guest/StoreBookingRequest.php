<?php

namespace App\Http\Requests\Guest;

use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'booking_token' => ['nullable', 'string'], // For idempotency/csrf if needed, though mainly reliance on session csrf
            'menu_id' => ['required', 'exists:shop_menus,id'],
            'option_ids' => ['nullable', 'array'],
            'option_ids.*' => ['exists:shop_options,id'],
            // assigned_staff_id is required: must match logic in Booker/Staff controller where staff must be selected
            'assigned_staff_id' => ['required', 'exists:shop_staffs,id'],
            'start_at' => ['required', 'date', 'after:now'],
            'note_from_booker' => ['nullable', 'string', 'max:1000'],
            
            // Booker info (Guest specific)
            'booker_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required_without:contact_phone', 'nullable', 'email', 'max:255'],
            'contact_phone' => ['required_without:contact_email', 'nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) return;

            $shop = $this->route('shop');
            $menuId = $this->input('menu_id');
            $staffId = $this->input('assigned_staff_id');
            $startAtStr = $this->input('start_at');
            $optionIds = $this->input('option_ids', []);

            if (!$staffId) return;

            $timezone = $shop->timezone;
            $startAt = \Illuminate\Support\Carbon::parse($startAtStr, $timezone);
            
            $menu = \App\Models\ShopMenu::findOrFail($menuId);
            $options = \App\Models\ShopOption::find($optionIds);
            $totalDuration = $menu->duration + $options->sum('additional_duration');
            $endAt = $startAt->copy()->addMinutes($totalDuration);

            $staff = \App\Models\ShopStaff::findOrFail($staffId);

            // 1. スタッフのメニュー対応チェック
            if ($menu->requires_staff_assignment) {
                $isAssignedToMenu = \Illuminate\Support\Facades\DB::table('shop_menu_staffs')
                    ->where('shop_menu_id', $menuId)
                    ->where('shop_staff_id', $staffId)
                    ->exists();
                if (!$isAssignedToMenu) {
                    $validator->errors()->add('assigned_staff_id', '選択されたスタッフはこのメニューを担当できません。');
                }
            } else {
                if ($staff->shop_id !== $shop->id) {
                    $validator->errors()->add('assigned_staff_id', '選択されたスタッフはこの店舗に所属していません。');
                }
            }

            // 2. シフト内チェック
            $startOfDayUtc = $startAt->copy()->startOfDay()->setTimezone('UTC');
            $endOfDayUtc = $startAt->copy()->endOfDay()->setTimezone('UTC');

            $shifts = $staff->schedules()
                ->whereBetween('workable_start_at', [$startOfDayUtc, $endOfDayUtc])
                ->get()
                ->map(fn($schedule) => (object)[
                    'start' => $schedule->workable_start_at->setTimezone($timezone)->format('H:i'),
                    'end' => $schedule->workable_end_at->setTimezone($timezone)->format('H:i')
                ])
                ->all();

            $timeSlotService = app(\App\Services\TimeSlotService::class);
            if (!$timeSlotService->isWithinShift($startAt, $endAt, $shifts)) {
                $validator->errors()->add('start_at', '選択された時間はスタッフの勤務時間外です。');
            }

            // 3. 重複チェック
            $existingBookings = $staff->bookings()
                ->whereBetween('start_at', [$startOfDayUtc, $endOfDayUtc])
                ->whereIn('status', ['pending', 'confirmed'])
                ->get()
                ->map(fn($booking) => (object)[
                    'start' => \Illuminate\Support\Carbon::parse($booking->start_at)->setTimezone($timezone)->format('H:i'),
                    'end' => \Illuminate\Support\Carbon::parse($booking->end_at)->setTimezone($timezone)->format('H:i')
                ])
                ->all();

            if ($timeSlotService->hasConflict($startAt, $endAt, $existingBookings)) {
                $validator->errors()->add('start_at', '選択された時間は既に他の予約が入っています。');
            }
        });
    }
}

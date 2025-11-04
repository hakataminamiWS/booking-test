<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class UpdateShopStaffScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $staff = $this->route('staff');
        return $this->user()->can('update', $staff);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'schedules' => ['present', 'array'],
            'schedules.*' => ['array'],
            'schedules.*.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.*.end_time' => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $schedulesByDay = $this->input('schedules', []);

            foreach ($schedulesByDay as $dayIndex => $schedules) {
                if (!is_array($schedules) || empty($schedules)) {
                    continue;
                }

                // 1. Check for start_time > end_time
                foreach ($schedules as $index => $schedule) {
                    if (empty($schedule['start_time']) || empty($schedule['end_time'])) {
                        continue;
                    }
                    // Skip check for overnight shifts, but same time is allowed for holidays
                    if ($schedule['start_time'] > $schedule['end_time']) {
                         $validator->errors()->add(
                            "schedules.{$dayIndex}.{$index}.end_time",
                            "終了時刻 ({$schedule['end_time']}) は開始時刻 ({$schedule['start_time']}) より後の時刻にしてください。"
                        );
                    }
                }

                // 2. Check for overlaps
                $sortedSchedules = $schedules;
                usort($sortedSchedules, function ($a, $b) {
                    return strcmp($a['start_time'], $b['start_time']);
                });

                for ($i = 1; $i < count($sortedSchedules); $i++) {
                    $previous = $sortedSchedules[$i - 1];
                    $current = $sortedSchedules[$i];

                    if (empty($previous['end_time']) || empty($current['start_time'])) {
                        continue;
                    }

                    if ($previous['end_time'] > $current['start_time']) {
                        $validator->errors()->add(
                            "schedules.{$dayIndex}.{$i}",
                            "スケジュール ({$previous['start_time']} - {$previous['end_time']}) と ({$current['start_time']} - {$current['end_time']}) が重複しています。"
                        );
                    }
                }
            }
        });
    }
}

<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateShopBusinessHoursRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $shop = $this->route('shop');
        return $this->user()->can('update', $shop);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'business_hours' => ['required', 'array', 'size:7'],
            'business_hours.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'business_hours.*.is_open' => ['required', 'boolean'],
            'business_hours.*.start_time' => ['required_if:business_hours.*.is_open,true', 'nullable', 'date_format:H:i'],
            'business_hours.*.end_time' => ['required_if:business_hours.*.is_open,true', 'nullable', 'date_format:H:i', 'after:business_hours.*.start_time'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'business_hours.*.is_open' => '営業日',
            'business_hours.*.start_time' => '開始時刻',
            'business_hours.*.end_time' => '終了時刻',
        ];
    }
}

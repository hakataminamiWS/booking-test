<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'time_slot_interval' => ['required', 'integer'],
            'booking_confirmation_type' => ['required', 'string'],
            'accepts_online_bookings' => ['required', 'boolean'],
            'timezone' => ['sometimes', 'string', 'in:Asia/Tokyo'],
            'cancellation_deadline_minutes' => ['required', 'integer', 'min:0'],
            'booking_deadline_minutes' => ['required', 'integer', 'min:0'],
        ];
    }
}
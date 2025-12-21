<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:shops,slug', 'regex:/^[a-z0-9-]+$/', 'not_in:create,edit'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'time_slot_interval' => ['required', 'integer'],
            'booking_confirmation_type' => ['required', 'string'],
            'accepts_online_bookings' => ['required', 'boolean'],
            'timezone' => ['required', 'string', 'timezone'],
            'cancellation_deadline_minutes' => ['required', 'integer', 'min:0'],
            'booking_deadline_minutes' => ['required', 'integer', 'min:0'],
        ];
    }
}

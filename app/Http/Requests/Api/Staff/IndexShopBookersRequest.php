<?php

namespace App\Http\Requests\Api\Staff;

use Illuminate\Foundation\Http\FormRequest;

class IndexShopBookersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by the controller's policy check.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'number' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'name_kana' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'last_booking_at_from' => ['nullable', 'date_format:Y-m-d'],
            'last_booking_at_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:last_booking_at_from'],
            'booking_count_from' => ['nullable', 'integer', 'min:0'],
            'booking_count_to' => ['nullable', 'integer', 'min:0', 'gte:booking_count_from'],

            'sort_by' => ['nullable', 'string', 'in:number,name,name_kana,contact_email,last_booking_at,booking_count'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],

            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

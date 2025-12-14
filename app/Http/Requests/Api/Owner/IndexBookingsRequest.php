<?php

namespace App\Http\Requests\Api\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexBookingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 認可はコントローラーまたはポリシー側で行うため、ここではtrue
        // ただし、Shop情報の整合性チェック等は必要に応じて追加
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
            'start_at_from' => ['nullable', 'date_format:Y-m-d'],
            'start_at_to' => ['nullable', 'date_format:Y-m-d'],
            'booker_number' => ['nullable', 'integer'],
            'booker_name' => ['nullable', 'string', 'max:255'],
            'menu_id' => ['nullable', 'integer'],
            'assigned_staff_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', Rule::in(['pending', 'confirmed', 'cancelled'])],
            'booking_channel' => ['nullable', 'string', 'max:50'],
            'sort_by' => ['nullable', 'string', Rule::in(['start_at', 'booker_number', 'booker_name', 'status', 'total_price', 'booking_channel'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

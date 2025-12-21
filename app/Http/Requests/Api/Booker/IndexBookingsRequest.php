<?php

namespace App\Http\Requests\Api\Booker;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexBookingsRequest extends FormRequest
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
            'sort_by' => ['nullable', 'string', Rule::in(['start_at', 'created_at', 'status'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'status' => ['nullable', 'string', Rule::in(['pending', 'confirmed', 'cancelled'])],
            'start_at_from' => ['nullable', 'date'],
            'start_at_to' => ['nullable', 'date'],
        ];
    }
}

<?php

namespace App\Http\Requests\Api\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexShopsRequest extends FormRequest
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
            'sort_by' => ['nullable', 'string', Rule::in(['slug', 'name', 'accepts_online_bookings', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'slug' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'accepts_online_bookings' => ['nullable', 'boolean'],
            'created_at' => ['nullable', 'date'],
        ];
    }
}

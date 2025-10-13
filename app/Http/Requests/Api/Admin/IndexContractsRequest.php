<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexContractsRequest extends FormRequest
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
            'sort_by' => ['nullable', 'string', Rule::in(['name', 'public_id', 'start_date', 'end_date'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'name' => ['nullable', 'string'],
            'public_id' => ['nullable', 'string'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['string', Rule::in(['active', 'expired'])],
            'start_date_after' => ['nullable', 'date'],
            'start_date_before' => ['nullable', 'date', 'after_or_equal:start_date_after'],
            'end_date_after' => ['nullable', 'date'],
            'end_date_before' => ['nullable', 'date', 'after_or_equal:end_date_after'],
        ];
    }
}

<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexOwnersRequest extends FormRequest
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
            'sort_by' => ['nullable', 'string', Rule::in(['public_id', 'name', 'created_at', 'contracts_count'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'public_id' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'created_at_after' => ['nullable', 'date'],
            'created_at_before' => ['nullable', 'date', 'after_or_equal:created_at_after'],
            'contracts_count_min' => ['nullable', 'integer', 'min:0'],
            'contracts_count_max' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

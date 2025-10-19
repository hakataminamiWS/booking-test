<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexContractApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Note: Authorization is handled by middleware for admin routes.
        // This can be enhanced if more specific logic is needed.
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
            'sort_by' => ['nullable', 'string', Rule::in(['id', 'customer_name', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'id' => ['nullable', 'integer'],
            'customer_name' => ['nullable', 'string'],
            'created_at_after' => ['nullable', 'date'],
            'created_at_before' => ['nullable', 'date', 'after_or_equal:created_at_after'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['string', Rule::in(['pending', 'approved', 'rejected'])],
            'contract_statuses' => ['nullable', 'array'],
            'contract_statuses.*' => ['string', Rule::in(['active', 'expired', 'none'])],
        ];
    }
}

<?php

namespace App\Http\Requests\Api\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexShopMenusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $shop = $this->route('shop');
        return $this->user()->can('view', $shop);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'price_from' => ['nullable', 'integer', 'min:0'],
            'price_to' => ['nullable', 'integer', 'min:0', 'gte:price_from'],
            'duration_from' => ['nullable', 'integer', 'min:0'],
            'duration_to' => ['nullable', 'integer', 'min:0', 'gte:duration_from'],
            'requires_staff_assignment' => ['nullable', 'boolean'],
            'sort_by' => ['nullable', 'string', Rule::in(['name', 'price', 'duration', 'requires_staff_assignment', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
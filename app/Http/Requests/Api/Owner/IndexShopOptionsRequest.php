<?php

namespace App\Http\Requests\Api\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexShopOptionsRequest extends FormRequest
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
            'additional_duration_from' => ['nullable', 'integer', 'min:0'],
            'additional_duration_to' => ['nullable', 'integer', 'min:0', 'gte:additional_duration_from'],
            'sort_by' => ['nullable', 'string', Rule::in(['name', 'price', 'additional_duration', 'created_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

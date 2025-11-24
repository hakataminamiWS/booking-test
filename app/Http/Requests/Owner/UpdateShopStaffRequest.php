<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('staff'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nickname' => ['required', 'string', 'max:255'],
            'small_image' => ['nullable', 'image', 'max:1024'], // 1MB
            'large_image' => ['nullable', 'image', 'max:3072'], // 3MB
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nickname' => 'ニックネーム',
            'small_image' => 'プロフィール画像(小)',
            'large_image' => 'プロフィール画像(大)',
        ];
    }
}

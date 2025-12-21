<?php

namespace App\Http\Requests\Booker;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'nullable', 'email', 'max:255'],
            'contact_phone' => ['required', 'nullable', 'string', 'max:20'],
            'note_from_booker' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'お名前は必須です。',
            'contact_email.required_without' => 'メールアドレスまたは電話番号のいずれかは必須です。',
            'contact_phone.required_without' => 'メールアドレスまたは電話番号のいずれかは必須です。',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingFormRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'menu_id' => ['required', 'exists:menus,id'],
            'staff_id' => ['nullable', 'exists:users,id'],
            'start_at' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'tel' => ['required', 'string', 'max:20'],
            'gender_preference' => ['nullable', 'string', 'in:male,female'],
        ];
    }
}

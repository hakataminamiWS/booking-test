<?php

namespace App\Http\Requests\Booker;

use App\Models\ShopBooker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DestroyProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if the user is the owner of this booker profile
        $shop = $this->route('shop');
        $booker = ShopBooker::where('shop_id', $shop->id)
            ->where('user_id', Auth::id())
            ->first();

        return $booker !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'member_number' => ['required', 'string'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $shop = $this->route('shop');
            $booker = ShopBooker::where('shop_id', $shop->id)
                ->where('user_id', Auth::id())
                ->first();

            if ($booker && $this->input('member_number') !== $booker->number) {
                $validator->errors()->add('member_number', '会員番号が一致しません。');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'member_number.required' => '会員番号を入力してください。',
        ];
    }
}

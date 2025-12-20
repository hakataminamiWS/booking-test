<?php

namespace App\Http\Requests\Staff;

use App\Models\ShopBooker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopBookerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $booker = $this->route('booker');
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $shopId = $this->route('shop')->id;
        $bookerId = $this->route('booker')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_kana' => ['nullable', 'string', 'max:255'],
            'contact_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('shop_bookers')->where(function ($query) use ($shopId) {
                    return $query->where('shop_id', $shopId);
                })->ignore($bookerId),
            ],
            'contact_phone' => ['required', 'string', 'max:20'],
            'note_from_booker' => ['nullable', 'string'],
            'shop_memo' => ['nullable', 'string'],
            'last_booking_at' => ['nullable', 'date_format:Y-m-d'],
            'booking_count' => ['nullable', 'integer', 'min:0'],
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
            'name' => '名前',
            'name_kana' => 'よみかた',
            'contact_email' => '連絡先メールアドレス',
            'contact_phone' => '連絡先電話番号',
            'note_from_booker' => '予約者からのメモ',
            'shop_memo' => '店舗側メモ',
            'last_booking_at' => '最終予約日時',
            'booking_count' => '予約回数',
        ];
    }
}
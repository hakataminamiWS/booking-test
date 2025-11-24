<?php

namespace App\Http\Requests\Owner;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // ルートからshopモデルインスタンスを取得
        $shop = $this->route('shop');
        // BookingPolicyのcreateメソッドで認可チェック
        return $this->user()->can('create', [Booking::class, $shop]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_at' => ['required', 'date'],
            'menu_id' => ['required', 'exists:shop_menus,id'],
            'option_ids' => ['nullable', 'array'],
            'option_ids.*' => ['exists:shop_options,id'],
            'assigned_staff_id' => ['nullable', 'exists:shop_staffs,id'],
            'shop_booker_id' => ['nullable', 'exists:shop_bookers,id'],
            'booker_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'note_from_booker' => ['nullable', 'string'],
            'shop_memo' => ['nullable', 'string'],
        ];
    }
}
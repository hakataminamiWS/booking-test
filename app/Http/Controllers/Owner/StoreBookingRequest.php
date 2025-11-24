<?php

namespace App\Http\Requests\Owner;

use App\Models\Booking;
use App\Models\ShopMenu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [Booking::class, $this->route('shop')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $shop = $this->route('shop');
        $menu = ShopMenu::find($this->input('menu_id'));

        return [
            'start_at' => ['required', 'date'],
            'menu_id' => ['required', 'integer', Rule::exists('shop_menus', 'id')->where('shop_id', $shop->id)],
            'option_ids' => ['nullable', 'array'],
            'option_ids.*' => ['integer', Rule::exists('shop_options', 'id')->whereIn('shop_menu_id', $shop->menus()->pluck('id'))],
            'assigned_staff_id' => [
                Rule::requiredIf(fn () => $menu && $menu->requires_staff_assignment),
                'nullable',
                'integer',
                Rule::exists('shop_staffs', 'id')->where('shop_id', $shop->id),
            ],
            'shop_booker_id' => ['nullable', 'integer', Rule::exists('shop_bookers', 'id')->where('shop_id', $shop->id)],
            'booker_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'shop_memo' => ['nullable', 'string'],
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
            'assigned_staff_id' => '担当スタッフ',
        ];
    }
}
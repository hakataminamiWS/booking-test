<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopMenuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $menu = $this->route('menu');
        return $this->user()->can('update', $menu);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'duration' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'requires_staff_assignment' => ['required', 'boolean'],
            'requires_cancellation_deadline' => ['required', 'boolean'],
            'cancellation_deadline_minutes' => ['required_if:requires_cancellation_deadline,true', 'nullable', 'integer', 'min:0'],
            'requires_booking_deadline' => ['required', 'boolean'],
            'booking_deadline_minutes' => ['required_if:requires_booking_deadline,true', 'nullable', 'integer', 'min:0'],
            'staff_ids' => ['nullable', 'array'],
            'staff_ids.*' => ['integer', 'exists:shop_staffs,id'],
            'option_ids' => ['nullable', 'array'],
            'option_ids.*' => ['integer', 'exists:shop_options,id'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $shopId = $this->route('shop')->id;

            if ($this->has('staff_ids')) {
                $staffIds = $this->input('staff_ids');

                if (is_array($staffIds)) {
                    $validStaffCount = \App\Models\ShopStaff::where('shop_id', $shopId)
                        ->whereIn('id', $staffIds)
                        ->count();

                    if (count($staffIds) !== $validStaffCount) {
                        $validator->errors()->add('staff_ids', '選択されたスタッフの一部がこの店舗に所属していません。');
                    }
                }
            }

            if ($this->has('option_ids')) {
                $optionIds = $this->input('option_ids');

                if (is_array($optionIds)) {
                    $validOptionCount = \App\Models\ShopOption::where('shop_id', $shopId)
                        ->whereIn('id', $optionIds)
                        ->count();

                    if (count($optionIds) !== $validOptionCount) {
                        $validator->errors()->add('option_ids', '選択されたオプションの一部がこの店舗に所属していません。');
                    }
                }
            }
        });
    }
}

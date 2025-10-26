<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $shop = $this->route('shop');

        // Deny if the user is the owner of the shop
        if ($shop->owner_user_id === Auth::id()) {
            return false;
        }

        // Deny if the user is already a staff member of the shop
        if ($shop->staffs()->where('user_id', Auth::id())->exists()) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $shop = $this->route('shop');
            if ($shop->staffApplications()->where('user_id', Auth::id())->where('status', 'pending')->exists()) {
                $validator->errors()->add('name', 'すでにこの店舗へのスタッフ申し込みが完了しています。');
            }
        });
    }
}

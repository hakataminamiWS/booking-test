<?php

namespace App\Http\Requests\Admin;

use App\Models\ContractApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Note: Authorization is handled by middleware for admin routes.
        // This can be enhanced if more specific logic is needed.
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
            'application_id' => ['required', 'integer', Rule::exists('contract_applications', 'id')],
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'max_shops' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'string', Rule::in(['active', 'expired'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
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
            'name' => '契約名',
            'max_shops' => '店舗上限数',
            'status' => '契約ステ-タス',
            'start_date' => '契約開始日',
            'end_date' => '契約終了日',
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
            $applicationId = $this->input('application_id');
            $userId = $this->input('user_id');

            $contractApplication = ContractApplication::find($applicationId);

            if (! $contractApplication || $contractApplication->user_id != $userId) {
                $validator->errors()->add('user_id', 'The provided user_id does not match the application_id.');
            }
        });
    }
}

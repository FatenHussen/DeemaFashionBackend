<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;



class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->exists('area_id')) {
            return;
        }

        $value = $this->input('area_id');
        if (
            $value === '' ||
            $value === null ||
            $value === 0 ||
            $value === 0.0 ||
            $value === '0' ||
            (is_string($value) && trim($value) === '')
        ) {
            $this->merge(['area_id' => null]);
        }
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => ['sometimes', 'string', 'max:255'],

            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],

            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($userId)
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed'
            ],

            'area_id' => [
                'nullable',
                'integer',
                'exists:areas,id'
            ],

            'image' => [
                'nullable',
                'image',
                'max:2048'
            ],

            /* ========= Marketer ========= */

            'is_affiliate' => ['nullable', 'boolean'],
            'affiliate_approved' => ['nullable', 'boolean'],

            'affiliate_id' => [
                'nullable',
                Rule::unique('users', 'affiliate_id')->ignore($userId)

            ],


            'affiliate_rate' => [
                'nullable',
                'numeric',
                'between:0,100'
            ],

            'affiliate_commission_type' => [
                'nullable',
                Rule::in([
                    'percentage_order',
                    'fixed_per_order',
                    'percentage_selected_products',
                ]),
            ],

            'affiliate_fixed_commission' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'affiliate_product_ids' => ['nullable', 'array'],
            'affiliate_product_ids.*' => ['integer', 'exists:products,id'],

            'affiliate_visit_commission_enabled' => ['nullable', 'boolean'],
            'affiliate_visit_commission_threshold' => ['nullable', 'integer', 'min:1'],
            'affiliate_visit_commission_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}

<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterRequest extends FormRequest
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
        return [
            'area_id' => [
                'nullable',
                'integer',
                'exists:areas,id'
            ],

            'is_affiliate' => ['nullable', 'boolean'],
            'affiliate_approved' => ['nullable', 'boolean'],

        ];
    }
}

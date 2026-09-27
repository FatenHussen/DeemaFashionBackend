<?php

namespace App\Http\Requests\Admin\Promotion;

use App\Models\Promotion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.en' => 'required|string|max:255',
            'name.ar' => 'required|string|max:255',
            'description' => ['required', 'array'],
            'description.en' => 'required|string',
            'description.ar' => 'required|string',
            'type' => ['required', Rule::in(Promotion::TYPES)],
            'is_active' => 'boolean',
            'position' => ['required', Rule::in(['top', 'bottom'])],
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'min_spend' => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:type,'.implode(',', Promotion::MIN_SPEND_TYPES),
            ],
            'discount_value' => [
                'nullable',
                'numeric',
                'min:0',
                Rule::requiredIf(fn () => in_array($this->input('type'), Promotion::AUTO_DISCOUNT_TYPES, true)),
            ],
            'discount_type' => [
                'nullable',
                'in:percentage,fixed',
                Rule::requiredIf(fn () => in_array($this->input('type'), Promotion::AUTO_DISCOUNT_TYPES, true)),
            ],
            'gift_description' => [
                Rule::requiredIf(fn () => in_array($this->input('type'), Promotion::GIFT_TYPES, true)),
                'array',
            ],
            'gift_description.en' => [
                Rule::requiredIf(fn () => in_array($this->input('type'), Promotion::GIFT_TYPES, true)),
                'string',
            ],
            'gift_description.ar' => [
                Rule::requiredIf(fn () => in_array($this->input('type'), Promotion::GIFT_TYPES, true)),
                'string',
            ],
            'reward_points' => [
                'nullable',
                'integer',
                'min:1',
                Rule::requiredIf(fn () => $this->input('type') === 'spend_x_get_points'),
            ],
            'page_slugs' => ['nullable', 'array'],
            'page_slugs.*' => ['string', 'max:255', 'exists:pages,slug'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'shop_ids' => ['nullable', 'array'],
            'shop_ids.*' => ['integer', 'exists:shops,id'],
            'vendor_ids' => ['nullable', 'array'],
            'vendor_ids.*' => ['integer', 'exists:vendors,id'],
            'shop_vendor_service_ids' => ['nullable', 'array'],
            'shop_vendor_service_ids.*' => ['integer', 'exists:shop_vendor_services,id'],
        ];
    }
}

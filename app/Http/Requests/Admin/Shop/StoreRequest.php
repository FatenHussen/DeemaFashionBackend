<?php

namespace App\Http\Requests\Admin\Shop;

use App\Http\Requests\Concerns\ValidatesShopAreaCityScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreRequest extends FormRequest
{
    use ValidatesShopAreaCityScope;

    public function authorize(): bool
    {
        return true;
    }

    public function withValidator(Validator $validator): void
    {
        $this->withValidatorForShopAreaCityScope($validator);
    }

    public function rules(): array
    {
        return [
            'name.ar'              => 'required|string|max:255',
            'name.en'              => 'required|string|max:255',
            'description.ar'       => 'nullable|string',
            'description.en'       => 'nullable|string',

            'address.ar'           => 'nullable|string',
            'address.en'           => 'nullable|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',

            'phone'                => 'nullable|string|max:20',
            'mobile'               => 'nullable|string|max:20',
            'whatsapp'             => 'nullable|string|max:20',
            'email'                => 'nullable|email|unique:stores,email',

            'working_hours'        => 'required|array',
            'working_hours.*'      => 'array',
            'working_hours.*.open' => 'required_without:working_hours.*.closed|date_format:H:i',
            'working_hours.*.close' => 'required_without:working_hours.*.closed|date_format:H:i',
            'working_hours.*.closed' => 'sometimes|boolean',

            'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'cover_images'         => 'nullable|array',
            'cover_images.*'       => 'image|mimes:jpeg,png,jpg,gif,webp',

            'is_active'            => 'sometimes|boolean',
            'is_restaurant'        => 'sometimes|boolean',
            'min_order_amount'     => 'nullable|numeric|min:0',
            'delivery_min_hours'   => 'nullable|integer|min:0',
            'delivery_max_hours'   => 'nullable|integer|min:0',
            'payment_methods'      => 'nullable|array',
            'payment_methods.*'    => 'required|string|in:cash,online',
            'pricing_tier'         => 'nullable|in:cheap,medium,expensive',
            'is_recommended'       => 'sometimes|boolean',

            'area_id'           => 'nullable|exists:areas,id',
            'vendor_id' => [
                'required',
                'exists:vendors,id',
                Rule::unique('shops', 'vendor_id')->whereNull('deleted_at'),
            ],

            'service_ids'          => 'nullable|array',
            'service_ids.*.id'  => 'required|integer|exists:services,id',

            'category_ids'          => 'nullable|array',
            'category_ids.*'        => 'integer|exists:categories,id',


            'badges'          => 'nullable|array',
            'badges.*' => 'integer|exists:badges,id',
            'coupon_ids'      => 'nullable|array',
            'coupon_ids.*'    => 'integer|exists:coupons,id',

        ];
    }

    public function attributes(): array
    {
        return [
            'name.ar' => 'اسم المتجر (عربي)',
            'name.en' => 'اسم المتجر (إنجليزي)',
            'owner_name' => 'اسم المالك',
            'owner_phone' => 'هاتف المالك',
            'address.ar' => 'العنوان (عربي)',
        ];
    }

    public function messages(): array
    {
        return [
            'vendor_id.unique' => __('custom.shops.vendor_already_has_shop'),
        ];
    }
}

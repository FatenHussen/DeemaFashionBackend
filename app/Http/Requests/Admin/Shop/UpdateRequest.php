<?php

namespace App\Http\Requests\Admin\Shop;

use App\Http\Requests\Concerns\ValidatesShopAreaCityScope;
use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateRequest extends FormRequest
{
    use ValidatesShopAreaCityScope;

    public function withValidator(Validator $validator): void
    {
        $this->withValidatorForShopAreaCityScope($validator);

        $validator->after(function (Validator $validator): void {
            $this->validateDeliveryLimits($validator);
        });
    }

    protected function prepareForValidation(): void
    {
        $cleared = [];

        foreach (['min_order_amount', 'delivery_min_hours', 'delivery_max_hours'] as $field) {
            if ($this->exists($field) && $this->input($field) === '') {
                $cleared[$field] = null;
            }
        }

        if ($cleared !== []) {
            $this->merge($cleared);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $shopId = $this->route('shop');

        return [
            'name.ar'              => 'nullable|string|max:255',
            'name.en'              => 'nullable|string|max:255',
            'description.ar'       => 'nullable|string',
            'description.en'       => 'nullable|string',

            'address.ar'           => 'nullable|string',
            'address.en'           => 'nullable|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',

            'phone'                => 'nullable|string|max:20',
            'mobile'               => 'nullable|string|max:20',
            'email'                => ['nullable', 'email', 'unique:shops,email,' . $shopId],

            'working_hours'        => 'nullable|array',
            'working_hours.*'      => 'array',
            'working_hours.*.open' => 'required_without:working_hours.*.closed|date_format:H:i',
            'working_hours.*.close' => 'required_without:working_hours.*.closed|date_format:H:i',
            'working_hours.*.closed' => 'sometimes|boolean',

            'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'cover_images'         => 'nullable|array',
            'cover_images.*'       => 'image|mimes:jpeg,png,jpg,gif,webp',

            'is_active'            => 'nullable|boolean',
            'is_restaurant'        => 'nullable|boolean',
            'min_order_amount'     => 'nullable|numeric|min:0',
            'delivery_min_hours'   => 'nullable|integer|min:0',
            'delivery_max_hours'   => 'nullable|integer|min:0',
            'payment_methods'      => 'nullable|array',
            'payment_methods.*'    => 'required|string|in:cash,online',
            'pricing_tier'         => 'nullable|in:cheap,medium,expensive',
            'is_recommended'       => 'nullable|boolean',
            'area_id'           => 'nullable|exists:areas,id',
            'vendor_id' => [
                'nullable',
                'exists:vendors,id',
                Rule::unique('shops', 'vendor_id')->whereNull('deleted_at')->ignore($shopId),
            ],
            'service_ids'          => 'nullable|array',
            'service_ids.*'        => 'exists:services,id',

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

    /**
     * A payload with only the three delivery fields updates those columns.
     * The platform shop keeps its values in delivery settings.
     */
    private function validateDeliveryLimits(Validator $validator): void
    {
        $fields = ['min_order_amount', 'delivery_min_hours', 'delivery_max_hours'];
        $present = array_values(array_filter(
            $fields,
            fn (string $field): bool => $this->exists($field)
        ));

        if ($present === []) {
            return;
        }

        $shop = $this->shopBeingUpdated();

        if ($shop?->is_default) {
            foreach ($present as $field) {
                $validator->errors()->add($field, __('custom.shops.platform_uses_settings'));
            }

            return;
        }

        $min = $this->exists('delivery_min_hours')
            ? $this->input('delivery_min_hours')
            : $shop?->delivery_min_hours;
        $max = $this->exists('delivery_max_hours')
            ? $this->input('delivery_max_hours')
            : $shop?->delivery_max_hours;

        if ($min === null || $min === '' || $max === null || $max === '' || ! is_numeric($min) || ! is_numeric($max)) {
            return;
        }

        if ((int) $max < (int) $min) {
            $validator->errors()->add('delivery_max_hours', __('custom.shops.delivery_max_before_min'));
        }
    }

    private function shopBeingUpdated(): ?Shop
    {
        $shopId = $this->route('shop');

        if ($shopId instanceof Shop) {
            return $shopId;
        }

        if ($shopId === null || $shopId === '') {
            return null;
        }

        return Shop::query()->find($shopId);
    }
}

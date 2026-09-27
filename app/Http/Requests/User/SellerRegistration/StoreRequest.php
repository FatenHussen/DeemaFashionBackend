<?php

namespace App\Http\Requests\User\SellerRegistration;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:seller_registrations,email',
            'password' => 'required|string|min:8',

            'seller_name' => 'required|string|max:255',
            'store_name'  => 'required|string|max:255',

            'address' => 'nullable|string|max:500',
            'commercial_register_number' => 'nullable|string|max:255',
            'commercial_register_date' => 'nullable|date',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'governorate_id' => 'nullable|exists:governorates,id',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'is_service_provider'  => 'nullable|boolean',
            'is_restaurant'        => 'nullable|boolean',
            'min_order_amount'     => 'exclude_if:is_service_provider,true|required|numeric|min:0',
            'delivery_min_hours'   => 'exclude_if:is_service_provider,true|required|integer|min:0',
            'delivery_max_hours'   => 'exclude_if:is_service_provider,true|required|integer|min:0|gte:delivery_min_hours',
            'service_type_ids'     => 'required_if:is_service_provider,true|nullable|array',
            'service_type_ids.*'   => 'integer|exists:vendor_service_types,id',
        ];
    }
}

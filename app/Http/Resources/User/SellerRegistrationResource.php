<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Country\OneResource as CountryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerRegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isServiceProvider = (bool) $this->is_service_provider;
        $isRestaurant = (bool) $this->is_restaurant;
        $type = $isServiceProvider ? 'service_provider' : ($isRestaurant ? 'restaurant' : 'shop');

        return [
            'id' => $this->id,
            'email' => $this->email,
               'phone' => $this->phone,
            'seller_name' => $this->seller_name,
            'store_name' => $this->store_name,
            'address' => $this->address,
            'commercial_register_number' => $this->commercial_register_number,
            'commercial_register_image' => $this->commercial_register_image,
            'gender' => $this->gender,
            'country_id' => $this->country_id,
            'country' => $this->country ? new CountryResource($this->country) : null,
            'city_id' => $this->city_id,
            'governorate_id' => $this->governorate_id,

            'logo' => $this->logo,
            'status' => $this->status ?? 'pending',
            'is_service_provider' => $isServiceProvider,
            'is_restaurant' => $isRestaurant,
            'min_order_amount' => $this->min_order_amount !== null ? (float) $this->min_order_amount : null,
            'delivery_min_hours' => $this->delivery_min_hours,
            'delivery_max_hours' => $this->delivery_max_hours,
            'seller_type' => $type,
            'registered_at' => $this->registered_at,
        ];
    }
}

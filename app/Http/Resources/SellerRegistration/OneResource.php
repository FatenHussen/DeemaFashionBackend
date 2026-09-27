<?php

namespace App\Http\Resources\SellerRegistration;

use App\Http\Resources\Country\OneResource as CountryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isServiceProvider = (bool) $this->is_service_provider;
        $isRestaurant = (bool) $this->is_restaurant;
        $type = $isServiceProvider ? 'service_provider' : ($isRestaurant ? 'restaurant' : 'shop');

        return [
            'id' => $this->id,
            'seller_name' => $this->seller_name,
            'email' => $this->email,
            'store_name' => $this->store_name,
            'address' => $this->address,
            'commercial_register_number' => $this->commercial_register_number,
            'commercial_register_date' => $this->commercial_register_date?->format('Y-m-d'),
            'country_id' => $this->country_id,
            'country' => $this->country ? new CountryResource($this->country) : null,
            'governorate' => [
                'id' => $this->governorate?->id,
                'name' => $this->governorate?->name,
            ],
            'city' => [
                'id' => $this->city?->id,
                'name' => $this->city?->name,
            ],
            'logo' => $this->logo ? asset('storage/' . $this->logo) : null,
            'status' => $this->status,
            'is_service_provider' => $isServiceProvider,
            'is_restaurant' => $isRestaurant,
            'min_order_amount' => $this->min_order_amount !== null ? (float) $this->min_order_amount : null,
            'delivery_min_hours' => $this->delivery_min_hours,
            'delivery_max_hours' => $this->delivery_max_hours,
            'seller_type' => $type,
            'registered_at' => $this->registered_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

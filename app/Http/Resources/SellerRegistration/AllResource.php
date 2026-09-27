<?php

namespace App\Http\Resources\SellerRegistration;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
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
            'phone' => $this->phone ?? null,
            'store_name' => $this->store_name,
            'country_id' => $this->country_id,
            'country' => $this->country?->name,
            'governorate' => $this->governorate?->name,
            'city' => $this->city?->name,
            'status' => $this->status,
            'is_service_provider' => $isServiceProvider,
            'is_restaurant' => $isRestaurant,
            'min_order_amount' => $this->min_order_amount !== null ? (float) $this->min_order_amount : null,
            'delivery_min_hours' => $this->delivery_min_hours,
            'delivery_max_hours' => $this->delivery_max_hours,
            'seller_type' => $type,
            'registered_at' => $this->registered_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

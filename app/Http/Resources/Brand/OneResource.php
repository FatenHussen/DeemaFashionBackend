<?php

namespace App\Http\Resources\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'image'          => $this->image_url,
            'products_count' => $this->products()->where('is_active', true)->inCatalogCategory()->count(),
            'shops_count'    => \App\Models\Shop::whereIn(
                'vendor_id',
                $this->vendors()->pluck('vendors.id')
            )->distinct()->count(),
            'orders_count'   => $this->orders_count,
            'rating'         => $this->average_rating ?? 0,
        ];
    }
}

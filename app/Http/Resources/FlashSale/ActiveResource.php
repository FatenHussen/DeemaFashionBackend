<?php

namespace App\Http\Resources\FlashSale;

use App\Http\Resources\Product\AllResource as ProductAllResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActiveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $products = Product::query()
            ->where('flash_sale_id', $this->id)
            ->where('is_active', true)
            ->inCatalogCategory()
            ->with([
                'category',
                'vendor',
                'variants',
                'variants.shopVariants',
                'media',
                'badges',
            ])
            ->latest();

        if (auth('user')->check()) {
            $products->withExists([
                'favorites as is_favorite' => function ($favoritesQuery) {
                    $favoritesQuery->where('user_id', auth('user')->id());
                },
            ]);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'products' => ProductAllResource::collection($products->get()),
        ];
    }
}

<?php

namespace App\Services\User;

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Product;

class FavoriteService
{
    protected array $typeMap = [
        'product' => \App\Models\Product::class,
        'recipe'  => \App\Models\Recipe::class,
        'brand'   => \App\Models\Brand::class,
        'basket'  => \App\Models\Basket::class,
        'shop' => \App\Models\Shop::class,
        'vendor' => \App\Models\Vendor::class
    ];

    public function toggle(int $userId, string $type, int $id): bool
    {
        $modelClass = $this->getModelClass($type);

        $favorite = Favorite::where([
            'user_id'           => $userId,
            'favoriteable_type' => $modelClass,
            'favoriteable_id'   => $id,
        ])->first();

        if ($favorite) {
            $favorite->delete();
            return false;
        }

        Favorite::create([
            'user_id'           => $userId,
            'favoriteable_type' => $modelClass,
            'favoriteable_id'   => $id,
        ]);

        return true;
    }

    public function list(int $userId, ?string $type = null, array $filters = [])
    {
        $query = Favorite::where('user_id', $userId)
            ->with('favoriteable');

        if ($type) {
            $query->where('favoriteable_type', $this->getModelClass($type));
        }

        // Shop filter (for products)
        if (!empty($filters['shop_id'])) {
            $query->whereHasMorph('favoriteable', [\App\Models\Product::class], function ($q) use ($filters) {
                $q->whereHas('shopVariants', function ($sq) use ($filters) {
                    $sq->where('shop_id', $filters['shop_id']);
                });
            });
        }

        // Category filter (for products and baskets)
        if (!empty($filters['category_id'])) {
            $query->whereHasMorph('favoriteable', [\App\Models\Product::class, \App\Models\Basket::class], function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        /* ================= FAVORITES ================= */
        if (
            auth('user')->check() &&
            method_exists($query->getModel(), 'favorites')
        ) {
            $query->withExists([
                'favorites as is_favorite' => function ($q) {
                    $q->where('user_id', auth('user')->id());
                }
            ]);
        }

        $hiddenCategoryIds = Category::idsHiddenFromCatalog();

        return $query->get()
            ->map(function ($fav) use ($hiddenCategoryIds) {
                $item = $fav->favoriteable;

                if (
                    $item instanceof Product
                    && $item->category_id
                    && in_array((int) $item->category_id, $hiddenCategoryIds, true)
                ) {
                    return null;
                }

                return $item?->toSectionArray();
            })
            ->filter()
            ->values();
    }

    protected function getModelClass(string $type): string
    {
        if (!isset($this->typeMap[$type])) {
            throw new \InvalidArgumentException("Invalid favorite type: {$type}");
        }

        return $this->typeMap[$type];
    }
}

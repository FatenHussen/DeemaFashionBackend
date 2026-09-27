<?php

namespace App\Services\Base\Section;

use App\Enums\OrderStatus;
use App\Enums\ProductApprovalStatus;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class SuggestedProductsService
{
    public function query(array $filters = [])
    {
        $userId = auth('user')->id();
        $deliveredStatuses = [
            OrderStatus::DELIVERED->value,
            'completed',
        ];

        $baseQuery = Product::query()
            ->where('approval_status', ProductApprovalStatus::APPROVED->value)
            ->where('is_active', true)
            ->inCatalogCategory();

        if ($userId) {
            $userTotals = OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('shop_product_variants', 'shop_product_variants.id', '=', 'order_items.shop_product_variant_id')
                ->join('product_variants', 'product_variants.id', '=', 'shop_product_variants.product_variant_id')
                ->where('orders.user_id', $userId)
                ->whereIn('orders.status', $deliveredStatuses)
                ->selectRaw('product_variants.product_id, SUM(order_items.quantity) as total')
                ->groupBy('product_variants.product_id');

            if ((clone $userTotals)->limit(1)->exists()) {
                $query = $baseQuery
                    ->select('products.*')
                    ->joinSub($userTotals, 'user_products', function ($join) {
                        $join->on('products.id', '=', 'user_products.product_id');
                    })
                    ->orderByDesc('user_products.total');

                return $this->addFavoriteFlag($query);
            }
        }

        $globalTotals = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('shop_product_variants', 'shop_product_variants.id', '=', 'order_items.shop_product_variant_id')
            ->join('product_variants', 'product_variants.id', '=', 'shop_product_variants.product_variant_id')
            ->whereIn('orders.status', $deliveredStatuses)
            ->selectRaw('product_variants.product_id, SUM(order_items.quantity) as total')
            ->groupBy('product_variants.product_id');

        if ((clone $globalTotals)->limit(1)->exists()) {
            $query = $baseQuery
                ->select('products.*')
                ->joinSub($globalTotals, 'global_products', function ($join) {
                    $join->on('products.id', '=', 'global_products.product_id');
                })
                ->orderByDesc('global_products.total');

            return $this->addFavoriteFlag($query);
        }

        $query = $baseQuery->latest();

        return $this->addFavoriteFlag($query);
    }

    private function addFavoriteFlag(Builder $query): Builder
    {
        if (
            auth('user')->check() &&
            method_exists($query->getModel(), 'favorites')
        ) {
            $query->withExists([
                'favorites as is_favorite' => fn($q) => $q->where('user_id', auth('user')->id()),
            ]);
        }

        return $query;
    }
}

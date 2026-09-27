<?php

namespace App\Services\Base;

use App\Models\CategoryAttribute;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;

/**
 * يحسب أثر حذف خاصية صنف (Category Attribute) قبل تنفيذ الحذف،
 * ويوفّر قائمة مفصّلة بكل ما هو مرتبط بها لعرضه في تبويب داخل الداشبورد.
 */
class CategoryAttributeDeleteImpactService
{
    public function impact(CategoryAttribute $attribute): array
    {
        $valueIds = $attribute->values()->pluck('id')->all();

        $variantsQuery = $this->variantsQuery($valueIds);

        $counts = [
            'attribute_values' => count($valueIds),
            'product_variants' => (clone $variantsQuery)->count(),
            'products' => (clone $variantsQuery)->distinct('product_id')->count('product_id'),
            'active_orders' => $this->activeOrdersCount($variantsQuery),
        ];

        $warnings = [];
        foreach (['attribute_values', 'product_variants', 'products', 'active_orders'] as $key) {
            if ($counts[$key] > 0) {
                $warnings[] = [
                    'key' => $key,
                    'count' => $counts[$key],
                    'message' => __("custom.category_attribute_delete_impact.{$key}", ['count' => $counts[$key]]),
                ];
            }
        }

        return [
            'type' => 'category_attribute',
            'id' => $attribute->id,
            'name' => $attribute->getTranslations('name'),
            'requires_confirmation' => !empty($warnings),
            'counts' => $counts,
            'warnings' => $warnings,
        ];
    }

    /**
     * قائمة مفصّلة بالمتغيّرات المرتبطة بالخاصية (للتبويب في شاشة الحذف).
     */
    public function linkedItems(CategoryAttribute $attribute, int $page = 1, int $perPage = 10): array
    {
        $valueIds = $attribute->values()->pluck('id')->all();

        $paginator = $this->variantsQuery($valueIds)
            ->with(['product.media', 'product.category', 'media'])
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $values = $attribute->values()
            ->with('color')
            ->get()
            ->mapWithKeys(fn($value) => [$value->id => [
                'id' => $value->id,
                'name' => $value->name,
                'hex' => $value->color?->hex,
            ]])
            ->all();

        $items = collect($paginator->items())->map(function (ProductVariant $variant) use ($valueIds, $values) {
            $usedValueIds = array_values(array_intersect($variant->attributes_values_ids ?? [], $valueIds));

            return [
                'variant_id' => $variant->id,
                'variant_name' => $variant->getTranslations('name'),
                'sku' => $variant->sku,
                'variant_image' => $variant->media->first()?->url,
                'product' => [
                    'id' => $variant->product?->id,
                    'product_number' => $variant->product?->product_number,
                    'name' => $variant->product?->name,
                    'image' => $variant->product?->image_url,
                    'category' => $variant->product?->category ? [
                        'id' => $variant->product->category->id,
                        'name' => $variant->product->category->name,
                    ] : null,
                ],
                'used_values' => array_values(array_map(
                    fn($id) => $values[$id] ?? ['id' => $id, 'name' => null, 'hex' => null],
                    $usedValueIds
                )),
            ];
        })->all();

        return [
            'items' => $items,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    /**
     * يزيل قيم الخاصية المحذوفة من متغيّرات المنتجات حتى لا تبقى معرّفات معلّقة.
     */
    public function detachValuesFromVariants(array $valueIds): int
    {
        if (empty($valueIds)) {
            return 0;
        }

        $affected = 0;

        $this->variantsQuery($valueIds)->each(function (ProductVariant $variant) use ($valueIds, &$affected) {
            $remaining = array_values(array_diff($variant->attributes_values_ids ?? [], $valueIds));

            $variant->attributes_values_ids = $remaining;
            $variant->saveQuietly();
            $affected++;
        });

        return $affected;
    }

    private function variantsQuery(array $valueIds): Builder
    {
        if (empty($valueIds)) {
            return ProductVariant::query()->whereRaw('1 = 0');
        }

        return ProductVariant::query()->where(function (Builder $query) use ($valueIds) {
            foreach ($valueIds as $valueId) {
                $query->orWhereJsonContains('attributes_values_ids', $valueId);
            }
        });
    }

    private function activeOrdersCount(Builder $variantsQuery): int
    {
        $variantIds = (clone $variantsQuery)->pluck('id')->all();

        if (empty($variantIds)) {
            return 0;
        }

        return OrderItem::whereHas(
            'shopProductVariant',
            fn($q) => $q->whereIn('product_variant_id', $variantIds)
        )
            ->whereHas('order', fn($q) => $q->whereIn('status', VariantDeleteImpactService::ACTIVE_ORDER_STATUSES))
            ->distinct('order_id')
            ->count('order_id');
    }
}

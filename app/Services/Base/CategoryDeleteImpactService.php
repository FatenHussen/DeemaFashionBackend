<?php

namespace App\Services\Base;

use App\Models\Basket;
use App\Models\Category;
use App\Models\NavMenuItem;
use App\Models\Page;
use App\Models\Product;
use App\Models\RecipeItem;
use App\Models\SectionItem;
use Illuminate\Support\Facades\DB;

/**
 * يحسب أثر حذف فئة قبل التنفيذ، ويعرض ما هو مرتبط وما الذي سيُحذف عند التأكيد.
 */
class CategoryDeleteImpactService
{
    public function impact(Category $category): array
    {
        $subtreeIds = $category->idsInSubtree();
        $childIds = array_values(array_diff($subtreeIds, [$category->id]));

        $productsCount = Product::query()->whereIn('category_id', $subtreeIds)->count();
        $basketsCount = Basket::query()->whereIn('category_id', $subtreeIds)->count();
        $pagesCount = Page::query()->whereIn('category_id', $subtreeIds)->count();
        $recipeLinksCount = RecipeItem::query()
            ->whereIn('switchable_category_id', $subtreeIds)
            ->count();

        $counts = [
            'child_categories' => count($childIds),
            'products' => $productsCount,
            'baskets' => $basketsCount,
            'pages' => $pagesCount,
            'recipe_links' => $recipeLinksCount,
        ];

        $warnings = [];
        foreach (array_keys($counts) as $key) {
            if ($counts[$key] > 0) {
                $warnings[] = [
                    'key' => $key,
                    'count' => $counts[$key],
                    'message' => __("custom.category_delete_impact.{$key}", ['count' => $counts[$key]]),
                ];
            }
        }

        return [
            'type' => 'category',
            'id' => $category->id,
            'name' => $category->getTranslations('name'),
            'requires_confirmation' => !empty($warnings),
            'counts' => $counts,
            'warnings' => $warnings,
            'subtree_ids' => $subtreeIds,
        ];
    }

    /**
     * تبويب العناصر المرتبطة (منتجات + سلال + فئات فرعية).
     */
    public function linkedItems(Category $category, int $page = 1, int $perPage = 10): array
    {
        $subtreeIds = $category->idsInSubtree();

        $products = Product::query()
            ->whereIn('category_id', $subtreeIds)
            ->with(['media', 'category'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (Product $product) => [
                'type' => 'product',
                'id' => $product->id,
                'name' => $product->getTranslations('name'),
                'product_number' => $product->product_number,
                'image' => $product->image_url,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->getTranslations('name'),
                ] : null,
                'will_be' => 'soft_deleted',
            ]);

        $baskets = Basket::query()
            ->whereIn('category_id', $subtreeIds)
            ->orderByDesc('id')
            ->get()
            ->map(fn (Basket $basket) => [
                'type' => 'basket',
                'id' => $basket->id,
                'name' => method_exists($basket, 'getTranslations')
                    ? $basket->getTranslations('name')
                    : ($basket->name ?? null),
                'will_be' => 'deleted',
            ]);

        $children = Category::query()
            ->whereIn('id', array_diff($subtreeIds, [$category->id]))
            ->orderBy('id')
            ->get()
            ->map(fn (Category $child) => [
                'type' => 'child_category',
                'id' => $child->id,
                'name' => $child->getTranslations('name'),
                'parent_id' => $child->parent_id,
                'will_be' => 'deleted',
            ]);

        $all = $children->concat($products)->concat($baskets)->values();
        $total = $all->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $lastPage));
        $items = $all->slice(($page - 1) * $perPage, $perPage)->values()->all();

        return [
            'items' => $items,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
            ],
        ];
    }

    /**
     * تنفيذ الحذف بعد التأكيد: منتجات (soft)، سلال، صفحات، nav/sections، ثم الفئات (soft).
     */
    public function executeDelete(Category $category): array
    {
        $impact = $this->impact($category);
        $subtreeIds = $impact['subtree_ids'];

        DB::transaction(function () use ($subtreeIds) {
            RecipeItem::query()
                ->whereIn('switchable_category_id', $subtreeIds)
                ->update(['switchable_category_id' => null]);

            if (class_exists(\App\Models\Gift::class)) {
                \App\Models\Gift::query()
                    ->whereIn('category_id', $subtreeIds)
                    ->update(['category_id' => null]);
            }

            // إخفاء المنتجات من الكتالوج مع الإبقاء على سجل الطلبات
            Product::query()
                ->whereIn('category_id', $subtreeIds)
                ->each(function (Product $product) {
                    $product->delete();
                });

            // فك ارتباط المنتجات (حتى المحذوفة ناعماً) لتفادي restrictOnDelete
            Product::withTrashed()
                ->whereIn('category_id', $subtreeIds)
                ->update(['category_id' => null]);

            Basket::query()
                ->whereIn('category_id', $subtreeIds)
                ->each(function (Basket $basket) {
                    $basket->delete();
                });

            // Soft-delete لا يشغّل cascadeOnDelete — احذف صفحات الفئات يدوياً
            Page::query()
                ->whereIn('category_id', $subtreeIds)
                ->each(function (Page $page) {
                    $page->delete();
                });

            // بنود Nav المربوطة بالفئة
            NavMenuItem::query()
                ->whereIn('category_id', $subtreeIds)
                ->delete();

            // عناصر يدوية في Sections تشير للفئة
            SectionItem::query()
                ->where('item_type', Category::class)
                ->whereIn('item_id', $subtreeIds)
                ->delete();

            $orderedIds = $this->orderIdsDeepestFirst($subtreeIds);

            foreach ($orderedIds as $categoryId) {
                $node = Category::query()->find($categoryId);
                if (!$node) {
                    continue;
                }

                // Soft delete — تبقى السجلات خارج قوائم المستخدم عبر deleted_at
                $node->delete();
            }
        });

        unset($impact['subtree_ids']);

        return $impact;
    }

    /**
     * @param  array<int>  $ids
     * @return array<int>
     */
    public function orderIdsDeepestFirst(array $ids): array
    {
        $depthById = [];

        foreach ($ids as $id) {
            $depth = 0;
            $current = Category::query()->select('id', 'parent_id')->find($id);
            $guard = 0;

            while ($current?->parent_id && $guard < Category::MAX_TREE_DEPTH) {
                if (!in_array((int) $current->parent_id, $ids, true)) {
                    break;
                }
                $depth++;
                $current = Category::query()->select('id', 'parent_id')->find($current->parent_id);
                $guard++;
            }

            $depthById[$id] = $depth;
        }

        arsort($depthById);

        return array_map('intval', array_keys($depthById));
    }
}

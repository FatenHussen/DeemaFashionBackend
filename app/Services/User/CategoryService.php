<?php

namespace App\Services\User;

use App\Http\Resources\Category\OneResource;
use App\Models\Category;
use App\Services\BaseService;
use App\Http\Resources\Category\AllResource;

class CategoryService extends BaseService
{
    protected $searchableFields = ['name']; // Disable default search behavior

    public function __construct(Category $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->relations = ['parent', 'activeChildren.activeChildren'];
        $this->pagination = true;
    }

    /**
     * Builder used by section API rendering (SectionApiService).
     * Supports `parent_id` to fetch direct children of a category.
     */
    public function query(array $filters = [])
    {
        $query = Category::query()->withCount([
            'activeChildren as children_count',
        ]);

        return $this->queryBuilder($query, $filters);
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Active + not soft-deleted; never orphan children of deleted/inactive parents
        $query->visibleToUsers();

        // Apply search BEFORE calling parent
        if (!empty($config['search'])) {
            $search = strtolower($config['search']);

            $query->where(function ($q) use ($search) {
                // Search in both Arabic and English (case-insensitive)
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", ["%{$search}%"]);
            });
        }

        // Remove 'type' from filters as it's handled separately
        $parentFilters = $filters;
        unset($parentFilters['type']);
        unset($parentFilters['sort_by']);
        unset($parentFilters['brand_id']); // Remove brand_id as it's handled via whereHas
        unset($parentFilters['shop_id']); // Remove shop_id as it's handled via whereHas

        // Now call parent (won't apply search since searchableFields is empty)
        $query = parent::queryBuilder($query, $parentFilters, $config);

        // Apply name filter
        if (isset($filters['name'])) {
            $locale = app()->getLocale();
            $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.{$locale}'))) LIKE ?", ['%' . strtolower($filters['name']) . '%']);
        }

        // Apply parent_id filter
        if (array_key_exists('parent_id', $filters)) {
            if ($filters['parent_id'] === null || !$filters['parent_id']) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $filters['parent_id']);
            }
        }

        // Product-based filters - categories that have products matching brand/shop conditions
        if (!empty($filters['brand_id']) || !empty($filters['shop_id'])) {
            $query->whereHas('products', function ($q) use ($filters) {
                if (!empty($filters['brand_id'])) {
                    $q->where('brand_id', $filters['brand_id']);
                }

                if (!empty($filters['shop_id'])) {
                    $q->whereHas('variants', function ($vq) use ($filters) {
                        $vq->whereHas('shopVariants', function ($sq) use ($filters) {
                            $sq->where('shop_id', $filters['shop_id']);
                        });
                    });
                }
            });
        }

        // Prioritize explicit sort/type over inherited default sort from BaseService.
        if (!empty($filters['sort_by'])) {
            $this->applySortBy($query, $filters['sort_by']);
        } elseif (!empty($filters['type'])) {
            $query->reorder();
            $this->applyTypeFilters($query, $filters['type']);
        } else {
            $query->reorder()->orderBy('order', 'asc')->orderBy('id', 'asc');
        }

        return $query;
    }

    protected function applyTypeFilters($query, $type)
    {
        switch ($type) {
            case 'new':
                $query->orderBy('created_at', 'desc');
                break;

            case 'most_popular':
                // Most popular = categories with most products sold
                $query->selectRaw('categories.*, (
                    SELECT SUM(order_items.quantity)
                    FROM products
                    INNER JOIN product_variants ON products.id = product_variants.product_id
                    INNER JOIN shop_product_variants ON product_variants.id = shop_product_variants.product_variant_id
                    INNER JOIN order_items ON shop_product_variants.id = order_items.shop_product_variant_id
                    WHERE products.category_id = categories.id
                    AND products.deleted_at IS NULL
                    AND shop_product_variants.deleted_at IS NULL
                ) as total_sales')
                    ->orderBy('total_sales', 'desc');
                break;

            case 'top_rated':
                // Top rated = categories with highest average product rating
                $query->selectRaw('categories.*, (
                    SELECT AVG(ratings.rating)
                    FROM products
                    INNER JOIN ratings ON ratings.rateable_id = products.id AND ratings.rateable_type IN ("product", "App\\\\Models\\\\Product")
                    WHERE products.category_id = categories.id
                    AND products.deleted_at IS NULL
                ) as avg_rating')
                    ->orderBy('avg_rating', 'desc');
                break;
        }
    }

    protected function applySortBy($query, string $sortBy): void
    {
        $query->reorder();

        match ($sortBy) {
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => null,
        };
    }
}

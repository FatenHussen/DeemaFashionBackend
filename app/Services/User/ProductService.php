<?php

namespace App\Services\User;

use Illuminate\Database\Eloquent\Builder;

use App\Models\Product;
use App\Models\Category;
use App\Models\FlashSale;
use App\Services\BaseService;
use App\Http\Resources\Product\OneResource;
use App\Http\Resources\Product\AllResource;

class ProductService extends BaseService
{
    protected $model      = Product::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'category',
        'originCountry',
        'warranty',
        'variants',
        'variants.shopVariants',
        'variants.shopVariants.shop',
        'variants.media',
        'media',
        'categoryDetails.categoryDetail',
        'extraDetails.category',
        'favorites',
        'icons',
        'badges',
    ];
    protected $searchableFields = ['name', 'description', 'country'];
    protected $sortableFields   = ['id', 'price', 'created_at', 'name'];

    protected function applyTypeFilters($query, $filters)
    {
        if (empty($filters['type'])) {
            return;
        }

        match ($filters['type']) {
            'new'          => $this->filterNew($query),
            'trend'        => $this->filterTrend($query),
            'top_rated'    => $this->filterTopRated($query),
            'offers'       => $this->filterOffers($query),
            'latest_flash_sale' => $this->filterLatestFlashSale($query),
            'recommended'  => $this->filterRecommended($query),
            'for_you'      => $this->filterForYou($query),
            'search_based' => $this->filterSearchBased($query, $filters),
            'most_popular' => $this->filterTrend($query),

            default        => null,
        };
    }
    protected function filterNew($query)
    {
        $query->orderBy('created_at', 'desc');
    }
    protected function filterTrend($query)
    {
        $query->whereHas('variants', function ($q) {
            $q->where('is_trend', true);
        })
            ->withCount(['variants as sold_count' => function ($q) {
                $q->join('shop_product_variants', 'product_variants.id', '=', 'shop_product_variants.product_variant_id')
                    ->join('order_items', 'shop_product_variants.id', '=', 'order_items.shop_product_variant_id')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    // ->where('orders.status', 'completed')
                    ->selectRaw('COALESCE(SUM(order_items.quantity), 0)');
            }])
            ->orderByDesc('sold_count');
    }
    protected function filterTopRated($query)
    {
        $query->withAvg('ratings', 'rating')
            ->orderByDesc('ratings_avg_rating');
    }
    protected function filterOffers($query)
    {
        $query->whereNotNull('discount')
            ->where('discount', '>', 0)
            ->orderByDesc('discount');
    }

    protected function filterLatestFlashSale($query): void
    {
        $latestActiveFlashSaleId = FlashSale::query()
            ->active()
            ->latest('id')
            ->value('id');

        if (!$latestActiveFlashSaleId) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where('flash_sale_id', $latestActiveFlashSaleId)
            ->latest();
    }
    protected function filterRecommended($query)
    {
        $user = auth('user')->user();

        if (!$user) return;
        // $categoryIds = $user
        // ->orders()
        //     ->with('items.product')
        //     ->get()
        //     ->pluck('items.*.product.category_id')
        //     ->flatten()
        //     ->unique()
        //     ->toArray();
        // $query->whereIn('category_id', $categoryIds);
        $query;
    }
    protected function filterForYou($query)
    {
        $query;
        // $user = auth('user')->user();

        // if (!$user) return;

        // $query->whereHas('variants.shopVariants.orders', function ($q) use ($user) {
        //     $q->where('user_id', $user->id);
        // })->orWhere('is_instant_delivery', 1);
    }
    protected function filterSearchBased($query, $filters)
    {
        if (empty($filters['search'])) return;

        $search = strtolower($filters['search']);
        $locale = app()->getLocale();

        $query->where(function ($q) use ($search, $locale) {
            $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.{$locale}'))) LIKE ?", ["%{$search}%"])
                ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, '$.{$locale}'))) LIKE ?", ["%{$search}%"])
                ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(country, '$.{$locale}'))) LIKE ?", ["%{$search}%"]);
        });
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query->with([
            'category',
            'originCountry',
            'variants',
            'variants.shopVariants',
            'variants.shopVariants.shop',
            'variants.media',
            'categoryDetails.categoryDetail',
            'extraDetails',
            'media',
            'icons',
            'badges',
        ]);
        $query->where('approval_status', \App\Enums\ProductApprovalStatus::APPROVED->value);

        if (!empty($filters['category_id'])) {
            $category = Category::find($filters['category_id']);
            if ($category) {
                $query->whereIn('category_id', $category->idsInSubtree());
            }
        }
        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }
        if (!empty($filters['shop_id'])) {
            $query->whereHas('variants.shopVariants', function (Builder $q) use ($filters) {
                $q->where('shop_id', $filters['shop_id']);
            });
        }

        // Price filter - تحويل السعر من عملة اليوزر للدولار قبل الفلتر
        if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
            // تحويل نطاق الأسعار من عملة اليوزر للدولار
            $priceRange = \App\Helpers\CurrencyHelper::convertPriceRangeToUSD(
                $filters['price_min'] ?? null,
                $filters['price_max'] ?? null
            );

            $query->whereHas('variants', function (Builder $q) use ($priceRange) {
                if ($priceRange['min']) {
                    $q->where('price', '>=', $priceRange['min']);
                }
                if ($priceRange['max']) {
                    $q->where('price', '<=', $priceRange['max']);
                }
            });
        }

        if (!empty($filters['search'])) {
            $locale = app()->getLocale();
            $query->where(function (Builder $q) use ($filters, $locale) {
                $q->where("name->{$locale}", 'like', '%' . $filters['search'] . '%')
                    ->orWhere("description->{$locale}", 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['country'])) {
            $query->where('country->' . app()->getLocale(), 'like', '%' . $filters['country'] . '%');
        }

        if (!empty($filters['country_id'])) {
            $query->where('country_id', $filters['country_id']);
        }

        // Free delivery filter (filter by shops that offer free delivery)
        if (isset($filters['is_free_delivery'])) {
            $isFreeDelivery = filter_var($filters['is_free_delivery'], FILTER_VALIDATE_BOOLEAN);

            $query->whereHas('variants.shopVariants.shop', function ($q) use ($isFreeDelivery) {
                $q->where('is_free_delivery', $isFreeDelivery);
            });
        }

        // Instant delivery filter
        if (isset($filters['is_instant_delivery'])) {
            $isInstantDelivery = filter_var($filters['is_instant_delivery'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_instant_delivery', $isInstantDelivery);
        }

        // On Sale filter (products with discount)
        if (isset($filters['on_sale'])) {
            $onSale = filter_var($filters['on_sale'], FILTER_VALIDATE_BOOLEAN);
            if ($onSale) {
                $query
                    ->where('discount', '>', 0);
            }
        }

        // In Stock Only filter
        if (isset($filters['in_stock_only'])) {
            $inStockOnly = filter_var($filters['in_stock_only'], FILTER_VALIDATE_BOOLEAN);
            if ($inStockOnly) {
                $query->whereHas('variants', function (Builder $q) {
                    $q->where('quantity', '>', 0);
                });
            }
        }

        // Attribute Values filter (Color, Size, etc.)
        if (!empty($filters['attribute_values'])) {
            // Support both array and comma-separated string
            $attributeValues = $filters['attribute_values'];
            if (is_string($attributeValues)) {
                $attributeValues = explode(',', $attributeValues);
            }

            if (is_array($attributeValues) && count($attributeValues) > 0) {
                $query->whereHas('variants', function (Builder $q) use ($attributeValues) {
                    $q->where(function ($subQuery) use ($attributeValues) {
                        foreach ($attributeValues as $attributeValueId) {
                            $subQuery->orWhereJsonContains('attributes_values_ids', (int)$attributeValueId);
                        }
                    });
                });
            }
        }

        if (!empty($filters['type'])) {
            $this->applyTypeFilters($query, $filters);
        }

        // Apply sort_by after type filters, or use default ordering
        if (!empty($filters['sort_by'])) {
            $this->applySortBy($query, $filters['sort_by']);
        } elseif (empty($filters['type'])) {
            // Default ordering when no type or sort_by is specified
            $query->latest();
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


        return $query->where('is_active', true)->inCatalogCategory();
    }


    public function query(array $filters = [])
    {
        $query = Product::query();
        $query =  $this->queryBuilder($query, $filters);
        return $query->where('is_active', true)->inCatalogCategory();
    }

    protected function applySortBy($query, string $sortBy): void
    {
        // Clear any previous ordering
        $query->reorder();

        match ($sortBy) {
            'price_desc' => $query->orderBy('price', 'desc'),
            'price_asc' => $query->orderBy('price', 'asc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            'rating' => $query->withAvg('ratings', 'rating')->orderByDesc('ratings_avg_rating'),
            default => null,
        };
    }
}

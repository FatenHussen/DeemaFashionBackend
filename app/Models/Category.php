<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Category extends Model implements Sectionable
{
    use HasFactory, HasTranslations, SoftDeletes, LogsActivity;

    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'icon',
        'parent_id',
        'order',
        'is_active',
        'is_restaurant',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_restaurant' => 'boolean',
    ];

    /** Root + up to 5 nested subcategory levels. */
    public const MAX_TREE_DEPTH = 6;

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function root(): self
    {
        return static::findRoot($this) ?? $this;
    }

    public static function resolveRootId(?int $categoryId): ?int
    {
        if (!$categoryId) {
            return null;
        }

        $current = static::query()->select('id', 'parent_id')->find($categoryId);

        return static::findRoot($current)?->id;
    }

    public static function findRoot(?self $current): ?self
    {
        if (!$current) {
            return null;
        }

        $guard = 0;

        while ($current->parent_id && $guard < self::MAX_TREE_DEPTH) {
            $parent = static::query()->select('id', 'parent_id')->find($current->parent_id);

            if (!$parent) {
                break;
            }

            $current = $parent;
            $guard++;
        }

        return $current;
    }

    public function attributes()
    {
        return $this->hasMany(CategoryAttribute::class);
    }

    public function inheritedAttributes()
    {
        $rootId = $this->isRoot() ? $this->id : static::resolveRootId($this->id);

        return CategoryAttribute::query()->where('category_id', $rootId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('deleted_at');
    }

    /**
     * Visible in user catalog: not soft-deleted, active, and either a root
     * or nested under an active (non-deleted) parent.
     */
    public function scopeVisibleToUsers($query)
    {
        return $query
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('parent_id')
                    ->orWhereHas('parent', fn ($parent) => $parent->active());
            });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /** The auto-generated Page Builder page for this category. */
    public function page()
    {
        return $this->hasOne(Page::class);
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function activeChildren()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->active()
            ->orderBy('order', 'asc');
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class);
    }

    // recursively
    public function descendants()
    {
        return $this->children()->with('descendants');
    }
    public function  getImageUrlAttribute()
    {
        return asset('storage/' . $this->icon);
    }
    public function leafDescendants()
    {
        $leaves = collect();

        $this->loadMissing('children');

        foreach ($this->children as $child) {

            $child->loadMissing('children');

            if ($child->children->isEmpty()) {
                $leaves->push($child);
            } else {
                $leaves = $leaves->merge($child->leafDescendants());
            }
        }

        return $leaves;
    }

    /**
     * This category id plus every descendant at any depth (parent, intermediate, or leaf).
     */
    public function idsInSubtree(): array
    {
        return static::expandIdsToSubtrees([$this->id]);
    }

    /**
     * Categories that must not appear in the public catalog, plus every descendant.
     * Includes inactive and soft-deleted categories so hiding a parent hides its subtree.
     *
     * @return list<int>
     */
    public static function idsHiddenFromCatalog(): array
    {
        $hiddenIds = static::withTrashed()
            ->where(function ($query) {
                $query->where('is_active', false)
                    ->orWhereNotNull('deleted_at');
            })
            ->pluck('id')
            ->all();

        if ($hiddenIds === []) {
            return [];
        }

        return static::expandIdsToSubtrees($hiddenIds);
    }

    /**
     * Expand one or more category ids to include each id and all descendants.
     *
     * @param  array<int|string>  $categoryIds
     * @return list<int>
     */
    public static function expandIdsToSubtrees(array $categoryIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));

        if ($ids === []) {
            return [];
        }

        $expanded = $ids;
        $frontier = $ids;

        while ($frontier) {
            $children = static::query()
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->all();

            $expanded = array_merge($expanded, $children);
            $frontier = $children;
        }

        return array_values(array_unique($expanded));
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function shops()
    {
        return $this->belongsToMany(Shop::class, 'category_shop');
    }

    public function baskets()
    {
        return $this->hasMany(Basket::class);
    }


    public function toSectionArray(): array
    {
        $childrenCount = $this->children_count
            ?? ($this->relationLoaded('activeChildren')
                ? $this->activeChildren->count()
                : $this->activeChildren()->count());

        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'image' => $this->image_url,
            'parent_id' => $this->parent_id,
            'has_children' => $childrenCount > 0,
            'children_count' => $childrenCount,
        ];
    }
}

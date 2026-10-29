<?php

namespace App\Services\Admin;

use App\Models\CategoryAttribute;
use App\Models\AttributeValue;
use App\Models\Color;
use App\Http\Resources\Admin\Category\CategoryAttribute\OneResource;
use App\Http\Resources\Admin\Category\CategoryAttribute\AllResource;
use App\Services\Base\CategoryAttributeDeleteImpactService;
use App\Services\BaseService;
use App\Exceptions\DeleteConfirmationRequiredException;
use App\Support\AttributeColorHex;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryAttributeService extends BaseService
{
    protected $model      = CategoryAttribute::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;
    protected $pagination = true;
    protected $relations = [
        'category',
        'values',
        'values.color',
    ];

    protected $syncRelations = [];

    protected $searchableFields = [
        'id',
        'name',
        'category_id',
        'type'
    ];

    protected $sortableFields = [
        'id',
        'name',
        'type',
        'created_at',
    ];

    /**
     * Create a new category attribute
     * If type is 'color' and no values are supplied, use the colors table.
     */
    public function create($data)
    {
        $hasSuppliedValues = !empty($data['values']);

        if ($hasSuppliedValues) {
            $this->assertColorValuesAreLinked(
                $data['type'] ?? null,
                $data['name'] ?? null,
                $data['values']
            );
        }

        // Create the category attribute using parent method
        $object = $this->model::create($data);
        $this->handleSingleImages($object, $data);
        $this->handleRelations($object, $data);
        $this->handleMedia($object, $data);

        // Keep explicit color selections instead of appending the full palette.
        if (($data['type'] ?? null) === 'color' && !$hasSuppliedValues) {
            $this->createColorAttributeValues($object);
        }

        // Refresh to get updated data including the newly created values
        $object->refresh();

        return new $this->resource($object) ?? true;
    }

    public function update($id, array $data)
    {
        if (array_key_exists('values', $data)) {
            $attribute = CategoryAttribute::query()->findOrFail($id);
            $this->assertColorValuesAreLinked(
                array_key_exists('type', $data) ? $data['type'] : $attribute->type,
                array_key_exists('name', $data) ? $data['name'] : $attribute->getTranslations('name'),
                $data['values'] ?? []
            );
        }

        return parent::update($id, $data);
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
            unset($filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
            unset($filters['date_to']);
        }

        if (!empty($filters['name'])) {
            $search = strtolower(trim((string) $filters['name']));
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", ["%{$search}%"]);
            });
            unset($filters['name']);
        }

        if (!empty($filters['category_id'])) {
            $query->forCategoryTree((int) $filters['category_id']);
            unset($filters['category_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
            unset($filters['type']);
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
            $query->where('is_active', (bool) $filters['is_active']);
            unset($filters['is_active']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }

    /**
     * Create attribute values for color type from colors table
     */
    protected function createColorAttributeValues($categoryAttribute)
    {
        $colors = Color::all();

        foreach ($colors as $color) {
            AttributeValue::create([
                'category_attribute_id' => $categoryAttribute->id,
                'name' => [
                    'en' => $color->getTranslation('name', 'en', false) ?? $color->getTranslation('name', 'ar', false) ?? $color->hex,
                    'ar' => $color->getTranslation('name', 'ar', false) ?? $color->getTranslation('name', 'en', false) ?? $color->hex,
                ],
                'color_id' => $color->id,
            ]);
        }
    }

    /**
     * معاينة أثر الحذف دون تنفيذه.
     */
    public function deleteImpact($id): array
    {
        $categoryAttribute = CategoryAttribute::findOrFail($id);

        return (new CategoryAttributeDeleteImpactService())->impact($categoryAttribute);
    }

    /**
     * قائمة مفصّلة بكل ما هو مرتبط بالخاصية (تبويب "العناصر المرتبطة").
     */
    public function linkedItems($id, int $page = 1, int $perPage = 10): array
    {
        $categoryAttribute = CategoryAttribute::findOrFail($id);

        return (new CategoryAttributeDeleteImpactService())->linkedItems($categoryAttribute, $page, $perPage);
    }

    /**
     * الحذف مسموح دائماً، لكنه يتطلب تأكيداً صريحاً إذا كانت الخاصية مستخدمة.
     * عند التأكيد تُزال قيم الخاصية من متغيّرات المنتجات بدل حذف المتغيّرات نفسها.
     */
    public function deleteWithConfirmation($id, bool $confirmed = false): array
    {
        $categoryAttribute = CategoryAttribute::findOrFail($id);
        $impactService = new CategoryAttributeDeleteImpactService();

        $impact = $impactService->impact($categoryAttribute);

        if ($impact['requires_confirmation'] && !$confirmed) {
            throw new DeleteConfirmationRequiredException(
                $impact,
                'custom.category_attribute_delete_impact.requires_confirmation'
            );
        }

        $valueIds = $categoryAttribute->values()->pluck('id')->all();

        DB::transaction(function () use ($categoryAttribute, $impactService, $valueIds) {
            $impactService->detachValuesFromVariants($valueIds);

            // attribute_values تُحذف تلقائياً عبر cascade على مستوى قاعدة البيانات
            $categoryAttribute->delete();
        });

        return $impact;
    }

    public function delete($id): bool
    {
        $this->deleteWithConfirmation($id, request()->boolean('confirm'));

        return true;
    }

    /**
     * Values are referenced by product variants via attributes_values_ids.
     * Rename must update the same row; delete+create would orphan those IDs.
     */
    protected function handleRelations($object, array &$data)
    {
        if (array_key_exists('values', $data)) {
            $this->syncAttributeValues($object, $data['values'] ?? []);
            unset($data['values']);
        }

        parent::handleRelations($object, $data);
    }

    /**
     * The shade is optional. A sent color_id must be a colors row.
     * An empty value list is left alone so creating a color attribute can still
     * attach the palette, and omitting values on update does not reach this method.
     */
    private function assertColorValuesAreLinked(?string $type, mixed $names, array $items): void
    {
        if (!AttributeColorHex::isColorAttribute($type, $names)) {
            return;
        }

        $messages = [];

        foreach (array_values($items) as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $colorId = $item['color_id'] ?? null;

            if ($colorId === null || $colorId === '') {
                continue;
            }

            if (!is_numeric($colorId) || !Color::query()->whereKey((int) $colorId)->exists()) {
                $messages["values.$index.color_id"] = __('validation.exists', ['attribute' => 'color id']);
            }
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }
    }

    protected function syncAttributeValues(CategoryAttribute $attribute, array $items): void
    {
        $items = array_values($items);
        $existing = $attribute->values()->orderBy('id')->get();
        $existingById = $existing->keyBy('id');
        $keepIds = [];

        $hasAnyId = collect($items)->contains(
            fn ($item) => is_array($item) && !empty($item['id'])
        );

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = isset($item['id']) ? (int) $item['id'] : null;
            $payload = array_intersect_key($item, array_flip(['name', 'color_id']));

            if ($id && $existingById->has($id)) {
                $existingById->get($id)->update($payload);
                $keepIds[] = $id;
                continue;
            }

            if (!$hasAnyId && ($existingValue = $existing->get($index))) {
                $existingValue->update($payload);
                $keepIds[] = $existingValue->id;
                continue;
            }

            $created = $attribute->values()->create($payload);
            $keepIds[] = $created->id;
        }

        $removedIds = $existingById->keys()
            ->map(fn ($id) => (int) $id)
            ->diff($keepIds)
            ->values()
            ->all();

        if ($removedIds === []) {
            return;
        }

        (new CategoryAttributeDeleteImpactService())->detachValuesFromVariants($removedIds);
        $attribute->values()->whereIn('id', $removedIds)->delete();
    }
}

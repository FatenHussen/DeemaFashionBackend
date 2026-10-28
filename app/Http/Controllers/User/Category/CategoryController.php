<?php

namespace App\Http\Controllers\User\Category;

use App\Http\Controllers\BaseIndexController;
use App\Http\Requests\User\Category\FilterRequest;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Services\User\CategoryService;
use App\Support\AttributeColorHex;

class CategoryController extends BaseIndexController
{
    public function __construct(CategoryService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
    }

    public function attributes(int $categoryId)
    {
        $rootId = Category::resolveRootId($categoryId);

        $attributes = CategoryAttribute::with(['values.color'])
            ->forCategoryTree($categoryId)
            ->where('is_active', true)
            ->get()
            ->map(fn($attr) => [
                'id'               => $attr->id,
                'category_id'      => $attr->category_id,
                'root_category_id' => $rootId,
                'name'             => $attr->name,
                'type'             => $attr->type,
                'values'           => $attr->values->map(function ($val) use ($attr) {
                    $isColor = AttributeColorHex::isColorAttribute(
                        $attr->type ?? null,
                        $attr->getTranslations('name')
                    );

                    return [
                        'id'   => $val->id,
                        'name' => $isColor
                            ? ($val->color?->name ?? $val->name)
                            : $val->name,
                        'hex'  => $isColor
                            ? AttributeColorHex::forValue($val)
                            : null,
                    ];
                })->values(),
            ]);

        return $this->sendResponse($attributes);
    }
}

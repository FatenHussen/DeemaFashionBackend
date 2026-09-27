<?php

namespace App\Http\Controllers\User\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\Category\OneResource as CategoryResource;
use App\Http\Resources\PageSection\OneResource as PageSectionResource;
use App\Models\Category;
use App\Models\Page;
use App\Services\Base\PageSection\PageSectionPresentationService;
use Illuminate\Http\Request;

/**
 * Auto-generated page for a category: renders the shared "category-details"
 * template scoped to the given category (its children + products), so every
 * category gets a page without duplicating CMS data per category.
 */
class CategoryPageController extends Controller
{
    public const TEMPLATE_SLUG = 'category-details';

    public function __construct(
        private PageSectionPresentationService $pageSectionPresentation,
    ) {}

    public function show(Request $request, int $categoryId)
    {
        $category = Category::query()
            ->visibleToUsers()
            ->with(['parent', 'activeChildren'])
            ->findOrFail($categoryId);

        // Scope the shared template to this category:
        // - products sections consume `category_id` (subtree)
        // - children categories sections consume `parent_id`
        $request->query->add([
            'category_id' => $category->id,
            'parent_id' => $category->id,
        ]);

        $sections = collect();

        // Prefer the category's own page (created automatically per category);
        // fall back to the shared "category-details" template for legacy data.
        $page = Page::query()->where('category_id', $category->id)->first()
            ?? Page::query()->where('slug', self::TEMPLATE_SLUG)->first();

        if ($page) {
            $sections = $this->pageSectionPresentation
                ->getSectionsForUserView($page, $request);
        }

        return $this->sendResponse(data: [
            'category' => new CategoryResource($category),
            'sections' => PageSectionResource::collection($sections),
        ]);
    }
}

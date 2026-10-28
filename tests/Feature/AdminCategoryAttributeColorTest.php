<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\Category\CategoryAttribute\StoreRequest;
use App\Http\Resources\Admin\Category\CategoryAttribute\AllResource;
use App\Models\Category;
use App\Models\Color;
use App\Models\Language;
use App\Services\Admin\CategoryAttributeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdminCategoryAttributeColorTest extends TestCase
{
    use RefreshDatabase;

    public function test_explicit_color_values_keep_their_links_without_appending_the_palette(): void
    {
        $this->seedLanguages();
        $category = $this->createCategory();
        $black = $this->createColor('Black', 'أسود', '#000000');
        $this->createColor('Silver', 'فضي', '#C0C0C0');

        $request = $this->storeRequest([
            'category_id' => $category->id,
            'name' => ['en' => 'Color', 'ar' => 'اللون'],
            'type' => 'color',
            'values' => [[
                'name' => ['en' => 'Black with Silver Glitter (1)', 'ar' => 'أسود مع لمعة فضية (1)'],
                'color_id' => $black->id,
            ]],
        ]);
        $request->validateResolved();

        $validated = $request->validated();
        $this->assertSame($black->id, $validated['values'][0]['color_id']);

        $resource = app(CategoryAttributeService::class)->create($validated);
        $attribute = $resource->resource->fresh(['values.color']);
        $this->assertCount(1, $attribute->values);
        $value = $attribute->values->first();
        $this->assertSame($black->id, (int) $value->color_id);
        $this->assertSame('Black with Silver Glitter (1)', $value->getTranslation('name', 'en'));

        foreach ([$resource, new AllResource($attribute)] as $adminResource) {
            $values = collect($adminResource->resolve()['values']);
            $this->assertCount(1, $values);
            $this->assertSame($value->id, $values->first()['id']);
            $this->assertSame($black->id, (int) $values->first()['color_id']);
            $this->assertSame('#000000', $values->first()['hex']);
        }
    }

    public function test_store_request_rejects_unknown_color_links(): void
    {
        $this->seedLanguages();
        $category = $this->createCategory();
        $request = $this->storeRequest([
            'category_id' => $category->id,
            'name' => ['en' => 'Color', 'ar' => 'اللون'],
            'type' => 'color',
            'values' => [[
                'name' => ['en' => 'Black', 'ar' => 'أسود'],
                'color_id' => 999999,
            ]],
        ]);

        try {
            $request->validateResolved();
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('values.0.color_id', $exception->errors());

            return;
        }

        $this->fail('An unknown color must fail validation.');
    }

    public function test_color_attributes_without_explicit_values_still_use_the_default_palette(): void
    {
        $category = $this->createCategory();
        $black = $this->createColor('Black', 'أسود', '#000000');
        $silver = $this->createColor('Silver', 'فضي', '#C0C0C0');
        $data = [
            'category_id' => $category->id,
            'name' => ['en' => 'Color', 'ar' => 'اللون'],
            'type' => 'color',
        ];

        foreach ([$data, array_merge($data, ['values' => []])] as $payload) {
            $resource = app(CategoryAttributeService::class)->create($payload);
            $values = $resource->resource->values()->get();
            $palette = Color::query()->orderBy('id')->get();

            $this->assertCount($palette->count(), $values);
            $this->assertEqualsCanonicalizing($palette->pluck('id')->all(), $values->pluck('color_id')->all());
            $this->assertTrue($values->pluck('color_id')->contains($black->id));
            $this->assertTrue($values->pluck('color_id')->contains($silver->id));
            $this->assertEqualsCanonicalizing(
                $palette->pluck('hex')->map(fn ($hex) => strtoupper((string) $hex))->all(),
                collect($resource->resolve()['values'])->pluck('hex')->map(fn ($hex) => strtoupper((string) $hex))->all()
            );
        }
    }

    public function test_store_request_rejects_malformed_color_rows_without_a_server_error(): void
    {
        $this->seedLanguages();
        $category = $this->createCategory();

        foreach (['invalid row', null] as $row) {
            $request = $this->storeRequest([
                'category_id' => $category->id,
                'name' => ['en' => 'Color', 'ar' => 'اللون'],
                'type' => 'color',
                'values' => [$row],
            ]);

            try {
                $request->validateResolved();
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('values.0.name', $exception->errors());

                continue;
            }

            $this->fail('Malformed color rows must fail validation.');
        }
    }

    public function test_color_values_without_a_color_link_are_rejected(): void
    {
        $this->seedLanguages();
        $category = $this->createCategory();

        foreach ([
            ['type' => 'color', 'name' => ['en' => 'Finish', 'ar' => 'اللمسة']],
            ['type' => 'square', 'name' => ['en' => 'Color', 'ar' => 'اللون']],
        ] as $attribute) {
            $request = $this->storeRequest([
                'category_id' => $category->id,
                'name' => $attribute['name'],
                'type' => $attribute['type'],
                'values' => [[
                    'name' => ['en' => '2', 'ar' => '2'],
                ]],
            ]);

            try {
                $request->validateResolved();
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('values.0.color_id', $exception->errors());

                continue;
            }

            $this->fail('A color value without color_id must fail validation.');
        }
    }

    public function test_non_color_values_can_stay_plain_text(): void
    {
        $this->seedLanguages();
        $category = $this->createCategory();
        $request = $this->storeRequest([
            'category_id' => $category->id,
            'name' => ['en' => 'Size', 'ar' => 'القياس'],
            'type' => 'square',
            'values' => [[
                'name' => ['en' => 'M', 'ar' => 'وسط'],
            ]],
        ]);

        $request->validateResolved();

        $this->assertArrayNotHasKey('color_id', $request->validated()['values'][0]);
    }

    private function storeRequest(array $payload): StoreRequest
    {
        $request = StoreRequest::create('/api/admin/category-attributes', 'POST', $payload);
        $request->setContainer(app());
        $request->setRedirector(app(Redirector::class));

        return $request;
    }

    private function createCategory(): Category
    {
        return Category::create([
            'name' => ['en' => 'Cosmetics', 'ar' => 'مستحضرات تجميل'],
            'is_active' => true,
            'is_restaurant' => false,
        ]);
    }

    private function createColor(string $en, string $ar, string $hex): Color
    {
        return Color::create([
            'name' => ['en' => $en, 'ar' => $ar],
            'hex' => $hex,
            'is_active' => true,
        ]);
    }

    private function seedLanguages(): void
    {
        Language::factory()->arabic()->create(['is_active' => true]);
        Language::factory()->english()->create(['is_active' => true, 'is_default' => false]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Icon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminIconImagePersistTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_with_method_override_replaces_the_icon_file(): void
    {
        Storage::fake('public');
        $icon = Icon::query()->create([
            'name' => ['ar' => 'عضوي', 'en' => 'Organic'],
            'image' => 'icons/organic.svg',
            'description' => ['ar' => 'قديم', 'en' => 'Old'],
            'is_active' => true,
        ]);
        Storage::disk('public')->put('icons/organic.svg', '<svg></svg>');

        $this->withoutMiddleware();

        $response = $this->post('/api/admin/icons/'.$icon->id, [
            '_method' => 'PUT',
            'name' => ['ar' => 'عضوي', 'en' => 'Organic'],
            'description' => ['ar' => 'منتج عضوي طبيعي', 'en' => 'Natural organic product'],
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('leaf.png', 8, 8),
        ]);

        $response->assertOk();
        $icon->refresh();
        $this->assertNotSame('icons/organic.svg', $icon->image);
        $this->assertTrue(Storage::disk('public')->exists($icon->image));
        $this->assertStringContainsString($icon->image, (string) $response->json('data.image'));
        $this->assertStringContainsString('?v=', (string) $response->json('data.image'));
    }
}

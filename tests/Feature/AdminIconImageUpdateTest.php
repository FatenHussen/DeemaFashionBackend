<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\Icon\UpdateIconRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Redirector;
use Tests\TestCase;

class AdminIconImageUpdateTest extends TestCase
{
    public function test_png_and_svg_uploads_pass_and_a_current_url_does_not_replace_the_file(): void
    {
        $png = UploadedFile::fake()->image('leaf.png', 8, 8);
        $validated = $this->validateIconUpdate([
            'name' => ['ar' => 'عضوي', 'en' => 'Organic'],
        ], ['image' => $png]);

        $this->assertInstanceOf(UploadedFile::class, $validated['image']);
        $this->assertSame('leaf.png', $validated['image']->getClientOriginalName());

        $svg = UploadedFile::fake()->create('mark.svg', 1, 'image/svg+xml');
        $validatedSvg = $this->validateIconUpdate([], ['image' => $svg]);
        $this->assertSame('mark.svg', $validatedSvg['image']->getClientOriginalName());

        $kept = $this->validateIconUpdate([
            'name' => ['ar' => 'عضوي', 'en' => 'Organic'],
            'image' => 'https://example.com/storage/icons/organic.svg',
        ]);
        $this->assertArrayNotHasKey('image', $kept);
        $this->assertSame('عضوي', $kept['name']['ar']);
    }

    public function test_icon_file_field_is_accepted_as_the_image(): void
    {
        $png = UploadedFile::fake()->image('badge.png', 8, 8);
        $validated = $this->validateIconUpdate([
            'name' => ['ar' => 'جديد', 'en' => 'New'],
        ], ['icon' => $png]);

        $this->assertInstanceOf(UploadedFile::class, $validated['image']);
        $this->assertSame('badge.png', $validated['image']->getClientOriginalName());
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @param  array<string, mixed>  $files
     * @return array<string, mixed>
     */
    private function validateIconUpdate(array $parameters, array $files = []): array
    {
        $request = UpdateIconRequest::create('/api/admin/icons/1', 'PUT', $parameters, [], $files);
        $request->headers->set('Accept', 'application/json');
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make(Redirector::class));
        $request->validateResolved();

        return $request->validated();
    }
}

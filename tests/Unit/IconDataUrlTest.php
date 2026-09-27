<?php

namespace Tests\Unit;

use App\Services\Admin\IconDataUrl;
use PHPUnit\Framework\TestCase;

class IconDataUrlTest extends TestCase
{
    public function test_webp_data_url_decodes(): void
    {
        $bytes = base64_decode('UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEAAwA0JaQAA3AA/vuUAAA=');
        $value = 'data:image/webp;base64,'.base64_encode($bytes);

        $decoded = IconDataUrl::decode($value);

        $this->assertNotNull($decoded);
        $this->assertSame('webp', $decoded['extension']);
        $this->assertSame($bytes, $decoded['bytes']);
    }

    public function test_octet_stream_webp_uses_the_filename(): void
    {
        $bytes = 'RIFFwebp';
        $value = 'data:application/octet-stream;base64,'.base64_encode($bytes);

        $decoded = IconDataUrl::decode($value, 'OIP (1).webp');

        $this->assertNotNull($decoded);
        $this->assertSame('webp', $decoded['extension']);
        $this->assertSame($bytes, $decoded['bytes']);
    }

    public function test_non_image_data_url_is_rejected(): void
    {
        $this->assertNull(IconDataUrl::decode('data:text/plain;base64,YQ=='));
    }
}

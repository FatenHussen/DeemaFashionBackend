<?php

namespace Tests\Unit;

use App\Http\Middleware\RejectDiscardedPhpUpload;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class RejectDiscardedPhpUploadTest extends TestCase
{
    public function test_empty_body_over_post_max_is_rejected(): void
    {
        $request = Request::create('/api/admin/icons/9', 'POST', [], [], [], [
            'CONTENT_LENGTH' => (string) (64 * 1024 * 1024),
        ]);

        $response = (new RejectDiscardedPhpUpload())->handle($request, function () {
            return new Response('should-not-run');
        });

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_body_under_post_max_continues(): void
    {
        $request = Request::create('/api/admin/icons/9', 'POST', [
            'name' => ['ar' => 'توصيل', 'en' => 'Delivery'],
        ], [], [], [
            'CONTENT_LENGTH' => '1200',
        ]);

        $response = (new RejectDiscardedPhpUpload())->handle($request, function () {
            return new Response('ok');
        });

        $this->assertSame(200, $response->getStatusCode());
    }
}

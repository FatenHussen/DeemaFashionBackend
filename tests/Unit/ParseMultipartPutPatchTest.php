<?php

namespace Tests\Unit;

use App\Http\Middleware\ParseMultipartPutPatch;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ParseMultipartPutPatchTest extends TestCase
{
    public function test_put_multipart_exposes_text_fields_and_the_image_file(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $boundary = '----IconBoundary7MA4YWxk';
        $body = "--{$boundary}\r\n"
            ."Content-Disposition: form-data; name=\"name[ar]\"\r\n\r\n"
            ."عضوي\r\n"
            ."--{$boundary}\r\n"
            ."Content-Disposition: form-data; name=\"name[en]\"\r\n\r\n"
            ."Organic\r\n"
            ."--{$boundary}\r\n"
            ."Content-Disposition: form-data; name=\"image\"; filename=\"leaf.png\"\r\n"
            ."Content-Type: image/png\r\n\r\n"
            .$png."\r\n"
            ."--{$boundary}--\r\n";

        $request = Request::create('/api/admin/icons/1', 'PUT', [], [], [], [
            'CONTENT_TYPE' => 'multipart/form-data; boundary='.$boundary,
            'CONTENT_LENGTH' => (string) strlen($body),
        ], $body);

        $captured = null;
        (new ParseMultipartPutPatch())->handle($request, function (Request $parsed) use (&$captured) {
            $captured = $parsed;

            return new Response('ok');
        });

        $this->assertInstanceOf(Request::class, $captured);
        $this->assertSame('عضوي', $captured->input('name.ar'));
        $this->assertSame('Organic', $captured->input('name.en'));
        $this->assertTrue($captured->hasFile('image'));

        $file = $captured->file('image');
        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertSame('leaf.png', $file->getClientOriginalName());
        $this->assertSame($png, file_get_contents($file->getRealPath()));
    }

    public function test_post_multipart_is_left_to_php(): void
    {
        $request = Request::create('/api/admin/icons/1', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'multipart/form-data; boundary=----AlreadyParsed',
            'CONTENT_LENGTH' => '10',
        ], 'unused');

        $request->request->set('name', ['ar' => 'kept']);

        (new ParseMultipartPutPatch())->handle($request, function (Request $parsed) {
            $this->assertSame('kept', $parsed->input('name.ar'));
            $this->assertFalse($parsed->hasFile('image'));

            return new Response('ok');
        });
    }
}

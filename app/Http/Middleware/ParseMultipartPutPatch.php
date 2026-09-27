<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * PHP only fills $_POST and $_FILES for POST. A dashboard update sent as
 * PUT/PATCH multipart returns success and leaves the previous image in place.
 */
class ParseMultipartPutPatch
{
    private const MAX_BYTES = 12 * 1024 * 1024;

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldParse($request)) {
            $this->parse($request);
        }

        return $next($request);
    }

    private function shouldParse(Request $request): bool
    {
        if (! in_array($request->getRealMethod(), ['PUT', 'PATCH'], true)) {
            return false;
        }

        $contentType = strtolower((string) $request->headers->get('Content-Type', ''));
        if (! str_contains($contentType, 'multipart/form-data')) {
            return false;
        }

        // allFiles() caches its result. Reading the bag directly keeps that cache empty
        // until the parsed upload is actually on the request.
        if ($request->files->count() > 0 || $request->request->count() > 0) {
            return false;
        }

        $length = (int) $request->server->get('CONTENT_LENGTH', 0);

        return $length <= self::MAX_BYTES;
    }

    private function parse(Request $request): void
    {
        $raw = $request->getContent();
        if ($raw === '' || strlen($raw) > self::MAX_BYTES) {
            return;
        }

        $boundary = $this->boundary($request, $raw);
        if ($boundary === null || $boundary === '') {
            return;
        }

        $parameters = [];
        $files = [];

        foreach (explode('--'.$boundary, $raw) as $block) {
            $block = ltrim($block, "\r\n");
            if ($block === '' || str_starts_with($block, '--')) {
                continue;
            }

            $split = $this->splitPart($block);
            if ($split === null) {
                continue;
            }

            [$headers, $body] = $split;
            if (! preg_match('/Content-Disposition:\s*form-data;\s*name="([^"]+)"(?:;\s*filename="([^"]*)")?/i', $headers, $disposition)) {
                continue;
            }

            $name = $disposition[1];
            $filename = $disposition[2] ?? null;

            if ($filename === null) {
                $this->assign($parameters, $name, $body);
                continue;
            }

            if ($filename === '') {
                continue;
            }

            $mime = 'application/octet-stream';
            if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $headers, $mimeMatch)) {
                $mime = trim($mimeMatch[1]);
            }

            $tmp = tempnam(sys_get_temp_dir(), 'mpu');
            if ($tmp === false) {
                continue;
            }

            file_put_contents($tmp, $body);
            $this->assign($files, $name, new UploadedFile(
                $tmp,
                basename($filename),
                $mime,
                UPLOAD_ERR_OK,
                true
            ));
        }

        if ($parameters !== []) {
            $request->request->add($parameters);
        }

        if ($files !== []) {
            $request->files->add($files);
        }

        $this->forgetConvertedFiles($request);
    }

    private function forgetConvertedFiles(Request $request): void
    {
        (function () {
            $this->convertedFiles = null;
        })->call($request);
    }

    private function boundary(Request $request, string $raw): ?string
    {
        $contentType = (string) $request->headers->get('Content-Type', '');
        if (preg_match('/boundary=(?:"([^"]+)"|([^;]+))/i', $contentType, $matches)) {
            $boundary = $matches[1] !== '' ? $matches[1] : trim($matches[2]);
            if ($boundary !== '') {
                return $boundary;
            }
        }

        if (preg_match('/^--([^\r\n]+)/', $raw, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function splitPart(string $block): ?array
    {
        if (str_contains($block, "\r\n\r\n")) {
            [$headers, $body] = explode("\r\n\r\n", $block, 2);
            if (str_ends_with($body, "\r\n")) {
                $body = substr($body, 0, -2);
            }

            return [$headers, $body];
        }

        if (str_contains($block, "\n\n")) {
            [$headers, $body] = explode("\n\n", $block, 2);
            if (str_ends_with($body, "\n")) {
                $body = substr($body, 0, -1);
            }

            return [$headers, $body];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $target
     */
    private function assign(array &$target, string $name, mixed $value): void
    {
        preg_match_all('/([^\[\]]+)/', $name, $matches);
        $keys = $matches[1];
        if ($keys === []) {
            return;
        }

        $append = str_ends_with($name, '[]');
        $last = array_pop($keys);
        $cursor = &$target;

        foreach ($keys as $key) {
            if (! isset($cursor[$key]) || ! is_array($cursor[$key])) {
                $cursor[$key] = [];
            }
            $cursor = &$cursor[$key];
        }

        if ($append) {
            $cursor[$last][] = $value;

            return;
        }

        $cursor[$last] = $value;
    }
}

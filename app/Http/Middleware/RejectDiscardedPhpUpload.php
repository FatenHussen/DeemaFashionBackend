<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * When the body is larger than post_max_size, PHP drops $_POST and $_FILES
 * and Laravel would update nothing, then return 200 with the old image.
 */
class RejectDiscardedPhpUpload
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->discarded($request)) {
            $message = 'حجم الصورة يجب ألا يتجاوز 8MB';

            return new JsonResponse([
                'status' => 'error',
                'message' => $message,
                'errors' => ['image' => [$message]],
            ], 422);
        }

        return $next($request);
    }

    private function discarded(Request $request): bool
    {
        $length = (int) $request->server('CONTENT_LENGTH', 0);
        if ($length < 1) {
            return false;
        }

        $max = self::iniBytes((string) ini_get('post_max_size'));
        if ($max < 1 || $length <= $max) {
            return false;
        }

        return $request->request->count() === 0 && $request->files->count() === 0;
    }

    private static function iniBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '0') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => (float) $value,
        };
    }
}

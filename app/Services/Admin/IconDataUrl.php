<?php

namespace App\Services\Admin;

/**
 * The icon edit form's file part can be missing while the text fields still
 * arrive. The same request then carries the bytes as a data URL.
 */
class IconDataUrl
{
    public const MAX_BYTES = 8 * 1024 * 1024;

    /**
     * @return array{extension: string, bytes: string}|null
     */
    public static function decode(string $value, ?string $filename = null): ?array
    {
        if (! preg_match('#^data:([^;,]+)?(?:;charset=[^;,]+)?;base64,#i', $value, $matches)) {
            return null;
        }

        $extension = self::extensionFromMime($matches[1] ?? '')
            ?? self::extensionFromFilename($filename);

        if ($extension === null) {
            return null;
        }

        $comma = strpos($value, ',');
        if ($comma === false) {
            return null;
        }

        $bytes = base64_decode(substr($value, $comma + 1), true);
        if (! is_string($bytes) || $bytes === '' || strlen($bytes) > self::MAX_BYTES) {
            return null;
        }

        return ['extension' => $extension, 'bytes' => $bytes];
    }

    private static function extensionFromMime(string $mime): ?string
    {
        return match (strtolower($mime)) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => null,
        };
    }

    private static function extensionFromFilename(?string $filename): ?string
    {
        $extension = strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)
            ? ($extension === 'jpeg' ? 'jpg' : $extension)
            : null;
    }
}

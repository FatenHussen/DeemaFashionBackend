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
    public static function decode(string $value): ?array
    {
        if (! preg_match('#^data:image/([\w.+-]+)(?:;charset=[^;,]+)?;base64,#i', $value, $matches)) {
            return null;
        }

        $extension = match (strtolower($matches[1])) {
            'jpeg', 'jpg' => 'jpg',
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp',
            'svg+xml' => 'svg',
            default => null,
        };

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
}

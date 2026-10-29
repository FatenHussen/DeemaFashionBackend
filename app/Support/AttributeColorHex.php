<?php

namespace App\Support;

use App\Models\AttributeValue;
use App\Models\Color;

class AttributeColorHex
{
    /**
     * Labels that still mean a color picker when the row was saved as square/circle.
     * Live catalog rows named Color arrive as type "square" with no swatch otherwise.
     */
    private const COLOR_LABELS = [
        'color',
        'colour',
        'اللون',
        'لون',
        'الالوان',
        'الألوان',
        'الوان',
        'ألوان',
    ];

    /**
     * Color swatches are type "color", or an attribute whose name is Color / اللون.
     * The stored type is left unchanged; only hex resolution uses this.
     */
    public static function isColorAttribute(?string $type, mixed $names = null): bool
    {
        if ($type === 'color') {
            return true;
        }

        $candidates = match (true) {
            is_string($names) => [$names],
            is_array($names) => $names,
            default => [],
        };

        foreach ($candidates as $name) {
            if (!is_string($name)) {
                continue;
            }

            $normalized = mb_strtolower(trim($name));
            if ($normalized !== '' && in_array($normalized, self::COLOR_LABELS, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve a display hex for a color-type attribute value.
     * Prefers colors.hex via color_id; legacy values use stored translations
     * so the swatch does not depend on the request language.
     */
    public static function forValue(?AttributeValue $value): ?string
    {
        if (!$value) {
            return null;
        }

        $color = $value->color;
        $hex = self::normalize($color?->hex);
        if ($hex !== null) {
            return $hex;
        }

        $colorNames = $color?->getTranslations('name') ?? [];
        $valueNames = $value->getTranslations('name');
        $names = collect(array_merge(array_values($colorNames), array_values($valueNames)))
            ->filter(fn ($name) => is_string($name))
            ->map(fn ($name) => trim($name))
            ->filter(fn ($name) => $name !== '')
            ->unique()
            ->values()
            ->all();

        foreach ($names as $name) {
            if (($hex = self::normalize($name)) !== null) {
                return $hex;
            }
        }

        if ($names === []) {
            return null;
        }

        $locales = array_unique(array_merge(['ar', 'en'], array_keys($colorNames), array_keys($valueNames)));

        return self::findHexByNames($names, $locales);
    }

    public static function normalize(mixed $hex): ?string
    {
        if (!is_string($hex)) {
            return null;
        }

        $trimmed = trim($hex);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/^#?[0-9A-Fa-f]{6}$/', $trimmed)) {
            return str_starts_with($trimmed, '#') ? strtoupper($trimmed) : '#' . strtoupper($trimmed);
        }

        if (preg_match('/^#?[0-9A-Fa-f]{3}$/', $trimmed)) {
            $raw = ltrim($trimmed, '#');
            $expanded = $raw[0] . $raw[0] . $raw[1] . $raw[1] . $raw[2] . $raw[2];

            return '#' . strtoupper($expanded);
        }

        return null;
    }

    private static function findHexByNames(array $names, array $locales): ?string
    {
        $names = array_values(array_unique(array_map(fn ($name) => mb_strtolower($name), $names)));
        $request = request();
        $cache = $request->attributes->get(self::class, []);
        $key = json_encode([$names, array_values($locales)]);

        // Repeated variants share lookups within this response, never across requests.
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $placeholders = implode(', ', array_fill(0, count($names), '?'));

        $colors = Color::query()
            ->where(function ($query) use ($names, $locales, $placeholders) {
                foreach ($locales as $locale) {
                    $query->orWhereRaw(
                        "LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(name, ?)))) IN ({$placeholders})",
                        array_merge(['$.' . json_encode((string) $locale)], $names)
                    );
                }
            })
            ->orderBy('id')
            ->get(['hex']);

        $hex = null;
        foreach ($colors as $color) {
            if (($hex = self::normalize($color->hex)) !== null) {
                break;
            }
        }

        $cache[$key] = $hex;
        $request->attributes->set(self::class, $cache);

        return $hex;
    }
}

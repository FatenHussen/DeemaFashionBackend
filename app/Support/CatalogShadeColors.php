<?php

namespace App\Support;

use App\Models\AttributeValue;
use App\Models\Color;

class CatalogShadeColors
{
    /**
     * Nail-polish shades saved as color-attribute labels with no color_id.
     * Hex is the swatch stored on colors, not a guess made while rendering.
     *
     * @return array<int, array{en: string, ar: string, hex: string, also?: array<int, string>}>
     */
    public static function shades(): array
    {
        return [
            ['en' => 'Black with Silver Glitter (1)', 'ar' => 'اسود مع لمعة فضية (1)', 'hex' => '#3E434A', 'also' => ['أسود مع لمعة فضية (1)']],
            ['en' => 'Black (5)', 'ar' => 'اسود (5)', 'hex' => '#40444D', 'also' => ['أسود (5)']],
            ['en' => 'Pastel Mauve (8)', 'ar' => 'موف باستيل (8)', 'hex' => '#C88EB3'],
            ['en' => 'Milky White (9)', 'ar' => 'ابيض حليبي (9)', 'hex' => '#DEDDD8', 'also' => ['أبيض حليبي (9)']],
            ['en' => 'Nude Peach (10)', 'ar' => 'بيج وردي (10)', 'hex' => '#ECD2C3'],
            ['en' => 'Deep Burgundy (16)', 'ar' => 'عنابي داكن (16)', 'hex' => '#9A1B24'],
            ['en' => 'Coral Brown (21)', 'ar' => 'بني مرجاني (21)', 'hex' => '#D3745C'],
            ['en' => 'Honey Beige (23)', 'ar' => 'بيج عسلي (23)', 'hex' => '#EBB57D'],
            ['en' => 'Rose Brown (24)', 'ar' => 'بني وردي (24)', 'hex' => '#A86660'],
            ['en' => 'Warm Cocoa Brown (25)', 'ar' => 'بني كاكاو دافئ (25)', 'hex' => '#803E30'],
            ['en' => 'Mauve Brown (26)', 'ar' => 'بني موف (26)', 'hex' => '#9E5E5F'],
            ['en' => 'Rose Pink (34)', 'ar' => 'زهر وردي (34)', 'hex' => '#E07B95'],
            ['en' => 'Mauve Rose (7)', 'ar' => 'وردي موف (7)', 'hex' => '#C25B6D'],
        ];
    }

    public static function link(): int
    {
        $linked = 0;

        foreach (self::shades() as $shade) {
            $color = self::resolveColor($shade);
            $names = self::matchNames($shade);

            $values = AttributeValue::query()
                ->with(['color', 'categoryAttribute'])
                ->where(function ($query) use ($names) {
                    foreach ($names as $name) {
                        $query->orWhere('name->en', $name)
                            ->orWhere('name->ar', $name);
                    }
                })
                ->get();

            foreach ($values as $value) {
                $attribute = $value->categoryAttribute;
                if (!$attribute || !AttributeColorHex::isColorAttribute(
                    $attribute->type,
                    $attribute->getTranslations('name')
                )) {
                    continue;
                }

                if (AttributeColorHex::normalize($value->color?->hex) !== null) {
                    continue;
                }

                $value->color_id = $color->id;
                $value->save();
                $linked++;
            }
        }

        return $linked;
    }

    public static function unlink(): void
    {
        foreach (self::shades() as $shade) {
            $colors = Color::query()
                ->where('hex', $shade['hex'])
                ->get()
                ->filter(fn (Color $color) => $color->getTranslation('name', 'en', false) === $shade['en']);

            $ids = $colors->pluck('id')->all();
            if ($ids === []) {
                continue;
            }

            AttributeValue::query()->whereIn('color_id', $ids)->update(['color_id' => null]);
            Color::query()->whereIn('id', $ids)->delete();
        }
    }

    /**
     * @param  array{en: string, ar: string, hex: string, also?: array<int, string>}  $shade
     */
    private static function resolveColor(array $shade): Color
    {
        $names = self::matchNames($shade);
        $color = Color::query()
            ->where(function ($query) use ($names) {
                foreach ($names as $name) {
                    $query->orWhere('name->en', $name)
                        ->orWhere('name->ar', $name);
                }
            })
            ->orderBy('id')
            ->first();

        if ($color && AttributeColorHex::normalize($color->hex) !== null) {
            return $color;
        }

        $hex = $shade['hex'];
        if (Color::query()->where('hex', $hex)->where('id', '!=', $color?->id ?? 0)->exists()) {
            $hex = self::unusedHex($hex);
        }

        if ($color) {
            $color->hex = $hex;
            $color->save();

            return $color;
        }

        return Color::query()->create([
            'name' => ['en' => $shade['en'], 'ar' => $shade['ar']],
            'hex' => $hex,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array{en: string, ar: string, hex: string, also?: array<int, string>}  $shade
     * @return array<int, string>
     */
    private static function matchNames(array $shade): array
    {
        return array_values(array_unique(array_merge(
            [$shade['en'], $shade['ar']],
            $shade['also'] ?? []
        )));
    }

    private static function unusedHex(string $hex): string
    {
        $value = hexdec(ltrim($hex, '#'));
        do {
            $value = ($value + 1) & 0xFFFFFF;
            $candidate = sprintf('#%06X', $value);
        } while (Color::query()->where('hex', $candidate)->exists());

        return $candidate;
    }
}

<?php

namespace App\Support;

use App\Models\Settings\SystemSetting;

/**
 * Runtime theme / brand colors stored in SystemSetting (group: appearance).
 * Injected as CSS custom properties so Super Admins can re-skin the UI
 * without rebuilding frontend assets.
 */
final class ThemeSettings
{
    public const GROUP = 'appearance';

    /** @return array<string, string> */
    public static function defaults(): array
    {
        return [
            'primary' => '#1e40af',
            'gradient_from' => '#1e40af',
            'gradient_via' => '#1e3a8a',
            'gradient_to' => '#0f172a',
        ];
    }

    /** @return array<string, string> */
    public static function all(): array
    {
        $stored = SystemSetting::group(self::GROUP);

        return array_merge(self::defaults(), array_filter($stored, fn ($v) => is_string($v) && $v !== ''));
    }

    /** @param  array<string, string>  $values */
    public static function save(array $values): void
    {
        foreach (self::defaults() as $key => $default) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            SystemSetting::set(self::GROUP, $key, self::normalizeHex($values[$key], $default));
        }
    }

    public static function normalizeHex(string $hex, string $fallback): string
    {
        $hex = ltrim(trim($hex), '#');

        if (preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return '#'.strtolower($hex);
        }

        if (preg_match('/^[0-9a-fA-F]{3}$/', $hex)) {
            return '#'.strtolower($hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]);
        }

        return $fallback;
    }

    /** @return array<string, string> */
    public static function presets(): array
    {
        return [
            'default-blue' => [
                'label' => 'Default Blue',
                'primary' => '#1e40af',
                'gradient_from' => '#1e40af',
                'gradient_via' => '#1e3a8a',
                'gradient_to' => '#0f172a',
            ],
            'government-green' => [
                'label' => 'Government Green',
                'primary' => '#166534',
                'gradient_from' => '#166534',
                'gradient_via' => '#14532d',
                'gradient_to' => '#052e16',
            ],
            'flag-blue' => [
                'label' => 'Philippine Flag Blue',
                'primary' => '#0038a8',
                'gradient_from' => '#0038a8',
                'gradient_via' => '#002776',
                'gradient_to' => '#0f172a',
            ],
            'teal-modern' => [
                'label' => 'Teal Modern',
                'primary' => '#0f766e',
                'gradient_from' => '#0f766e',
                'gradient_via' => '#115e59',
                'gradient_to' => '#134e4a',
            ],
        ];
    }

    public static function cssBlock(): string
    {
        $theme = self::all();
        $palette = self::buildPrimaryPalette($theme['primary']);

        $lines = [
            ':root {',
            "  --theme-gradient-from: {$theme['gradient_from']};",
            "  --theme-gradient-via: {$theme['gradient_via']};",
            "  --theme-gradient-to: {$theme['gradient_to']};",
        ];

        foreach ($palette as $shade => $hex) {
            $lines[] = "  --color-primary-{$shade}: {$hex};";
        }

        $lines[] = '}';

        return implode("\n", $lines);
    }

    /**
     * @return array<int, string> shade => hex
     */
    public static function buildPrimaryPalette(string $baseHex): array
    {
        return [
            50 => self::mix($baseHex, '#ffffff', 0.92),
            100 => self::mix($baseHex, '#ffffff', 0.84),
            200 => self::mix($baseHex, '#ffffff', 0.68),
            300 => self::mix($baseHex, '#ffffff', 0.52),
            400 => self::mix($baseHex, '#ffffff', 0.28),
            500 => self::mix($baseHex, '#ffffff', 0.12),
            600 => self::mix($baseHex, '#000000', 0.08),
            700 => self::normalizeHex($baseHex, '#1e40af'),
            800 => self::mix($baseHex, '#000000', 0.22),
            900 => self::mix($baseHex, '#000000', 0.38),
        ];
    }

    private static function mix(string $hex, string $target, float $weight): string
    {
        $weight = max(0, min(1, $weight));
        [$r1, $g1, $b1] = self::hexToRgb(self::normalizeHex($hex, '#000000'));
        [$r2, $g2, $b2] = self::hexToRgb(self::normalizeHex($target, '#ffffff'));

        $r = (int) round($r1 * (1 - $weight) + $r2 * $weight);
        $g = (int) round($g1 * (1 - $weight) + $g2 * $weight);
        $b = (int) round($b1 * (1 - $weight) + $b2 * $weight);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /** @return array{0:int,1:int,2:int} */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}

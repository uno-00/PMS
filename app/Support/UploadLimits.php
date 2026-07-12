<?php

namespace App\Support;

/**
 * Reads effective PHP upload limits so validation messages match what the
 * server can actually accept (upload_max_filesize vs post_max_size).
 */
final class UploadLimits
{
    public static function maxKilobytes(): int
    {
        $bytes = min(
            self::iniToBytes(ini_get('upload_max_filesize')),
            self::iniToBytes(ini_get('post_max_size')),
        );

        return max(1, (int) floor($bytes / 1024));
    }

    public static function maxMegabytesLabel(): string
    {
        $kb = self::maxKilobytes();

        if ($kb >= 1024) {
            return rtrim(rtrim(number_format($kb / 1024, 1), '0'), '.').' MB';
        }

        return $kb.' KB';
    }

    /** @return int Kilobytes allowed for GAA Excel uploads (capped at 10 MB). */
    public static function gaaFileMaxKilobytes(): int
    {
        return min(10240, self::maxKilobytes());
    }

    private static function iniToBytes(string|false $value): int
    {
        if ($value === false || $value === '') {
            return 0;
        }

        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => (int) $value,
        };
    }
}

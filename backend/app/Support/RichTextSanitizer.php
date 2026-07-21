<?php

namespace App\Support;

final class RichTextSanitizer
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><blockquote><a><table><thead><tbody><tr><th><td><span><div>';

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $clean = strip_tags($html, self::ALLOWED_TAGS);
        $clean = preg_replace('/\s*on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
        $clean = preg_replace('/javascript:/i', '', $clean) ?? $clean;

        $text = trim(strip_tags($clean));

        if ($text === '' && ! preg_match('/<table\b/i', $clean)) {
            return null;
        }

        return $clean;
    }

    public static function plainText(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $text = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;
        $text = preg_replace('/<\/(p|div|li|tr|h[1-6])>/i', "\n", $text) ?? $text;

        return trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}

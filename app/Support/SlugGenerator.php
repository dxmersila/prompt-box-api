<?php

namespace App\Support;

class SlugGenerator
{
    public static function generate(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $value = str_replace(['_', '-', '–', '—'], ' ', $value);
        $value = preg_replace('/[^\p{L}\p{N}\s\x{200C}]+/u', '', $value) ?? '';
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/\s+/u', '-', $value) ?? '';

        return trim($value, '-');
    }
}

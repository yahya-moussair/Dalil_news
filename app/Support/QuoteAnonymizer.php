<?php

namespace App\Support;

final class QuoteAnonymizer
{
    public static function clean(?string $text): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        $clean = preg_replace(
            '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu',
            '[بريد محذوف]',
            $text,
        ) ?? $text;

        $clean = preg_replace(
            '/(?<!\d)\+?\d[\d\s().-]{6,}\d/',
            '[رقم محذوف]',
            $clean,
        ) ?? $clean;

        return trim($clean);
    }
}

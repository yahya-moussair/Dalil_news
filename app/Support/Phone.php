<?php

namespace App\Support;

final class Phone
{
    public static function normalize(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $digits = '212'.substr($digits, 1);
        }

        return $digits;
    }

    public static function hash(string $normalized): string
    {
        return hash('sha256', $normalized);
    }

    public static function mask(?string $normalized): string
    {
        if ($normalized === null || strlen($normalized) < 6) {
            return '••• ••';
        }

        return '+'.substr($normalized, 0, 4).' •• ••• ••'.substr($normalized, -2);
    }
}

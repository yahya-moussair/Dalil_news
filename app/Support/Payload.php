<?php

namespace App\Support;

final class Payload
{
    /**
     * @param  array<mixed>  $source
     * @return array<string, mixed>
     */
    public static function assoc(array $source): array
    {
        $out = [];

        foreach ($source as $key => $value) {
            if (is_string($key)) {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function listOfAssoc(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];

        foreach ($value as $item) {
            if (is_array($item)) {
                $out[] = self::assoc($item);
            }
        }

        return $out;
    }

    public static function str(mixed $value): ?string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }

        if (is_int($value)) {
            return (string) $value;
        }

        return null;
    }

    public static function int(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return null;
    }
}

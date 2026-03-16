<?php

declare(strict_types=1);

namespace App\Helpers;

final class Text
{
    public static function normalizeWhitespace(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return $value;
    }

    public static function toAscii(string $value): string
    {
        $value = self::normalizeWhitespace($value);

        if (class_exists(\Transliterator::class)) {
            $transliterator = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC; Any-Latin; Latin-ASCII');
            if ($transliterator) {
                $value = $transliterator->transliterate($value);
            }
        } else {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($converted !== false) {
                $value = $converted;
            }
        }

        return $value;
    }

    private function __construct()
    {
    }
}
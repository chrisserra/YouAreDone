<?php
declare(strict_types=1);

namespace App\Core;

class App
{
    public static function config(array $config, string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
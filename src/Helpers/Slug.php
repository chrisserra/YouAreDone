<?php

declare(strict_types=1);

namespace App\Helpers;

use PDO;

final class Slug
{
    public static function make(string $value): string
    {
        $value = Text::toAscii($value);
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');
        $value = preg_replace('/-+/', '-', $value) ?? '';

        return $value !== '' ? $value : 'item';
    }

    public static function unique(PDO $db, string $table, string $column, string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $suffix = 2;

        while (self::exists($db, $table, $column, $slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private static function exists(PDO $db, string $table, string $column, string $slug, ?int $ignoreId = null): bool
    {
        $sql = "SELECT id FROM {$table} WHERE {$column} = :slug";
        $params = ['slug' => $slug];

        if ($ignoreId !== null) {
            $sql .= " AND id != :ignore_id";
            $params['ignore_id'] = $ignoreId;
        }

        $sql .= " LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return (bool)$stmt->fetchColumn();
    }

    public static function buildCandidateSlug(string $fullName): string
    {
        return self::make($fullName);
    }

    public static function buildRaceSlug(string $stateSlug, string $officeSlug, int $year, ?int $district = null): string
    {
        $slug = "{$stateSlug}/{$officeSlug}/{$year}";

        if ($district !== null) {
            $slug .= "/district-{$district}";
        }

        return $slug;
    }

    private function __construct()
    {
    }
}
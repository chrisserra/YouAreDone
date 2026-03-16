<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

final class Office extends Model
{
    public function findById(int $officeId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM offices WHERE office_id = :office_id LIMIT 1",
            ['office_id' => $officeId]
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM offices WHERE slug = :slug LIMIT 1",
            ['slug' => trim($slug)]
        );
    }

    public function findByName(string $name): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM offices WHERE name = :name LIMIT 1",
            ['name' => trim($name)]
        );
    }

    public function getAllActive(): array
    {
        $sql = "
            SELECT *
            FROM offices
            ORDER BY sort_order ASC, name ASC
        ";

        return $this->fetchAllRows($sql);
    }

    public function requireBySlug(string $slug): array
    {
        $office = $this->findBySlug($slug);

        if (!$office) {
            throw new InvalidArgumentException("Office not found for slug: {$slug}");
        }

        return $office;
    }

    public function requireById(int $officeId): array
    {
        $office = $this->findById($officeId);

        if (!$office) {
            throw new InvalidArgumentException("Office not found for ID: {$officeId}");
        }

        return $office;
    }

    public function isDistrictOffice(int $officeId): bool
    {
        $office = $this->findById($officeId);

        if (!$office) {
            return false;
        }

        return (int)$office['has_district'] === 1;
    }
}
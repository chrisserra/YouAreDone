<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

final class ElectionType extends Model
{
    public function findById(int $electionTypeId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM election_types WHERE election_type_id = :election_type_id LIMIT 1",
            ['election_type_id' => $electionTypeId]
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM election_types WHERE slug = :slug LIMIT 1",
            ['slug' => trim($slug)]
        );
    }

    public function findByName(string $name): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM election_types WHERE name = :name LIMIT 1",
            ['name' => trim($name)]
        );
    }

    public function getAll(): array
    {
        $sql = "
            SELECT *
            FROM election_types
            ORDER BY sort_order ASC, name ASC
        ";

        return $this->fetchAllRows($sql);
    }

    public function requireBySlug(string $slug): array
    {
        $type = $this->findBySlug($slug);

        if (!$type) {
            throw new InvalidArgumentException("Election type not found for slug: {$slug}");
        }

        return $type;
    }

    public function requireById(int $electionTypeId): array
    {
        $type = $this->findById($electionTypeId);

        if (!$type) {
            throw new InvalidArgumentException("Election type not found for ID: {$electionTypeId}");
        }

        return $type;
    }
}
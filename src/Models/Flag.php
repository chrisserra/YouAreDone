<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Helpers\Slug;
use App\Helpers\Text;
use InvalidArgumentException;

final class Flag extends Model
{
    public function findById(int $flagId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM flags WHERE flag_id = :flag_id LIMIT 1",
            ['flag_id' => $flagId]
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM flags WHERE slug = :slug LIMIT 1",
            ['slug' => trim($slug)]
        );
    }

    public function getActive(?string $flagColor = null): array
    {
        $sql = "
            SELECT *
            FROM flags
            WHERE is_active = 1
        ";
        $params = [];

        if ($flagColor !== null && $flagColor !== '') {
            $sql .= " AND flag_color = :flag_color";
            $params['flag_color'] = $flagColor;
        }

        $sql .= " ORDER BY sort_order ASC, name ASC";

        return $this->fetchAllRows($sql, $params);
    }

    public function create(array $data): int
    {
        $name = Text::normalizeWhitespace((string)($data['name'] ?? ''));
        $slug = trim((string)($data['slug'] ?? ''));
        $description = $data['description'] ?? null;
        $flagColor = trim((string)($data['flag_color'] ?? ''));
        $defaultWeight = isset($data['default_weight']) ? (float)$data['default_weight'] : 1.00;
        $isActive = array_key_exists('is_active', $data) ? (int)(bool)$data['is_active'] : 1;
        $sortOrder = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;

        if ($name === '') {
            throw new InvalidArgumentException('Flag name is required.');
        }

        if (!in_array($flagColor, ['green', 'red'], true)) {
            throw new InvalidArgumentException('flag_color must be green or red.');
        }

        if ($defaultWeight < 0) {
            throw new InvalidArgumentException('default_weight must be 0 or greater.');
        }

        if ($slug === '') {
            $baseSlug = Slug::make($name);
            $slug = Slug::unique($this->db, 'flags', 'slug', 'flag_id', $baseSlug);
        } else {
            $slug = Slug::make($slug);
            $slug = Slug::unique($this->db, 'flags', 'slug', 'flag_id', $slug);
        }

        $sql = "
            INSERT INTO flags
            (
                slug,
                name,
                description,
                flag_color,
                default_weight,
                is_active,
                sort_order,
                created_at,
                updated_at
            )
            VALUES
            (
                :slug,
                :name,
                :description,
                :flag_color,
                :default_weight,
                :is_active,
                :sort_order,
                NOW(),
                NOW()
            )
        ";

        return $this->insert($sql, [
            'slug' => $slug,
            'name' => $name,
            'description' => $description,
            'flag_color' => $flagColor,
            'default_weight' => $defaultWeight,
            'is_active' => $isActive,
            'sort_order' => $sortOrder,
        ]);
    }

    public function update(int $flagId, array $data): bool
    {
        $existing = $this->findById($flagId);
        if (!$existing) {
            return false;
        }

        $name = array_key_exists('name', $data)
            ? Text::normalizeWhitespace((string)$data['name'])
            : (string)$existing['name'];

        $description = array_key_exists('description', $data)
            ? $data['description']
            : $existing['description'];

        $flagColor = array_key_exists('flag_color', $data)
            ? trim((string)$data['flag_color'])
            : (string)$existing['flag_color'];

        $defaultWeight = array_key_exists('default_weight', $data)
            ? (float)$data['default_weight']
            : (float)$existing['default_weight'];

        $isActive = array_key_exists('is_active', $data)
            ? (int)(bool)$data['is_active']
            : (int)$existing['is_active'];

        $sortOrder = array_key_exists('sort_order', $data)
            ? (int)$data['sort_order']
            : (int)$existing['sort_order'];

        $slugInput = array_key_exists('slug', $data)
            ? trim((string)$data['slug'])
            : (string)$existing['slug'];

        if ($name === '') {
            throw new InvalidArgumentException('Flag name is required.');
        }

        if (!in_array($flagColor, ['green', 'red'], true)) {
            throw new InvalidArgumentException('flag_color must be green or red.');
        }

        if ($defaultWeight < 0) {
            throw new InvalidArgumentException('default_weight must be 0 or greater.');
        }

        $baseSlug = Slug::make($slugInput !== '' ? $slugInput : $name);
        $slug = Slug::unique($this->db, 'flags', 'slug', 'flag_id', $baseSlug, $flagId);

        $sql = "
            UPDATE flags
            SET
                slug = :slug,
                name = :name,
                description = :description,
                flag_color = :flag_color,
                default_weight = :default_weight,
                is_active = :is_active,
                sort_order = :sort_order,
                updated_at = NOW()
            WHERE flag_id = :flag_id
        ";

        return $this->execute($sql, [
            'flag_id' => $flagId,
            'slug' => $slug,
            'name' => $name,
            'description' => $description,
            'flag_color' => $flagColor,
            'default_weight' => $defaultWeight,
            'is_active' => $isActive,
            'sort_order' => $sortOrder,
        ]);
    }
}
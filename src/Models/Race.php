<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Helpers\Slug;
use InvalidArgumentException;

final class Race extends Model
{
    protected string $table = 'races';

    public function findByNaturalKey(int $officeId, string $stateCode, int $year, ?int $district): ?array
    {
        $sql = "
            SELECT *
            FROM races
            WHERE office_id = :office_id
              AND state_code = :state_code
              AND year = :year
              AND (
                    (:district IS NULL AND district IS NULL)
                    OR district = :district
                  )
            LIMIT 1
        ";

        return $this->fetchOne($sql, [
            'office_id'  => $officeId,
            'state_code' => strtoupper($stateCode),
            'year'       => $year,
            'district'   => $district,
        ]);
    }

    public function create(array $data): int
    {
        $officeId   = (int)$data['office_id'];
        $stateCode  = strtoupper(trim((string)$data['state_code']));
        $stateSlug  = trim((string)$data['state_slug']);
        $year       = (int)$data['year'];
        $district   = isset($data['district']) && $data['district'] !== '' ? (int)$data['district'] : null;
        $officeSlug = trim((string)$data['office_slug']);
        $title      = $data['title'] ?? null;

        if ($officeId <= 0 || $stateCode === '' || $stateSlug === '' || $year <= 0 || $officeSlug === '') {
            throw new InvalidArgumentException('Missing required race fields.');
        }

        $existing = $this->findByNaturalKey($officeId, $stateCode, $year, $district);
        if ($existing) {
            return (int)$existing['id'];
        }

        $baseSlug = Slug::buildRaceSlug($stateSlug, $officeSlug, $year, $district);
        $slug = Slug::unique($this->db, 'races', 'slug', $baseSlug);

        $sql = "
            INSERT INTO races (
                office_id,
                state_code,
                state_slug,
                year,
                district,
                slug,
                title,
                is_active,
                created_at,
                updated_at
            ) VALUES (
                :office_id,
                :state_code,
                :state_slug,
                :year,
                :district,
                :slug,
                :title,
                1,
                NOW(),
                NOW()
            )
        ";

        return $this->insert($sql, [
            'office_id'   => $officeId,
            'state_code'  => $stateCode,
            'state_slug'  => $stateSlug,
            'year'        => $year,
            'district'    => $district,
            'slug'        => $slug,
            'title'       => $title,
        ]);
    }

    public function getSlugById(int $raceId): ?string
    {
        $row = $this->fetchOne(
            "SELECT race_slug FROM races WHERE race_id = :race_id LIMIT 1",
            ['race_id' => $raceId]
        );

        return $row ? $row['race_slug'] : null;
    }
}
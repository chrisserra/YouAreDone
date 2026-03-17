<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class ElectionRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function getNextElectionEvents(): array
    {
        $sql = "
            SELECT
                e.election_date,
                r.state_name,
                r.state_slug,
                et.slug AS election_type_slug,
                et.name AS election_type,
                GROUP_CONCAT(DISTINCT o.name ORDER BY o.sort_order ASC, o.name ASC SEPARATOR '||') AS offices
            FROM elections e
            INNER JOIN election_types et
                ON et.election_type_id = e.election_type_id
            INNER JOIN races r
                ON r.race_id = e.race_id
            INNER JOIN offices o
                ON o.office_id = r.office_id
            WHERE e.election_date = (
                SELECT MIN(e2.election_date)
                FROM elections e2
                INNER JOIN races r2
                    ON r2.race_id = e2.race_id
                WHERE e2.election_date >= CURDATE()
                  AND e2.status = 'upcoming'
                  AND r2.status = 'active'
            )
              AND e.status = 'upcoming'
              AND r.status = 'active'
            GROUP BY
                e.election_date,
                r.state_name,
                r.state_slug,
                et.slug,
                et.name
            ORDER BY
                e.election_date ASC,
                r.state_name ASC,
                et.sort_order ASC,
                et.name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcomingElectionEvents(int $limit = 12): array
    {
        $limit = max(1, $limit);

        $sql = "
        SELECT
            grouped.election_date,
            grouped.state_name,
            grouped.state_slug,
            grouped.election_type_slug,
            grouped.election_type,
            grouped.offices
        FROM (
            SELECT
                e.election_date,
                r.state_name,
                r.state_slug,
                et.slug AS election_type_slug,
                et.name AS election_type,
                et.sort_order AS election_type_sort,
                GROUP_CONCAT(DISTINCT o.name ORDER BY o.sort_order ASC, o.name ASC SEPARATOR '||') AS offices
            FROM elections e
            INNER JOIN election_types et
                ON et.election_type_id = e.election_type_id
            INNER JOIN races r
                ON r.race_id = e.race_id
            INNER JOIN offices o
                ON o.office_id = r.office_id
            WHERE e.election_date >= CURDATE()
              AND e.status = 'upcoming'
              AND r.status = 'active'
              AND e.election_date > (
                  SELECT MIN(e2.election_date)
                  FROM elections e2
                  INNER JOIN races r2 ON r2.race_id = e2.race_id
                  WHERE e2.election_date >= CURDATE()
                    AND e2.status = 'upcoming'
                    AND r2.status = 'active'
              )
            GROUP BY
                e.election_date,
                r.state_name,
                r.state_slug,
                et.slug,
                et.name,
                et.sort_order
        ) AS grouped
        ORDER BY
            grouped.election_date ASC,
            grouped.state_name ASC,
            grouped.election_type_sort ASC,
            grouped.election_type ASC
        LIMIT :limit
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatesWithNextElection(): array
    {
        $states = $this->getTrackedStates();
        $eventsByState = $this->getNextEventMapByState();

        $rows = [];

        foreach ($states as $state) {
            $stateSlug = (string) ($state['state_slug'] ?? '');
            $event = $eventsByState[$stateSlug] ?? null;

            $rows[] = [
                'state_name' => (string) ($state['state_name'] ?? ''),
                'state_slug' => $stateSlug,
                'next_election_date' => $event['next_election_date'] ?? null,
                'election_type' => $event['election_type'] ?? null,
                'election_type_slug' => $event['election_type_slug'] ?? null,
                'offices' => $event['offices'] ?? null,
            ];
        }

        return $rows;
    }

    private function getTrackedStates(): array
    {
        $sql = "
            SELECT
                r.state_name,
                r.state_slug
            FROM races r
            WHERE r.status = 'active'
            GROUP BY
                r.state_name,
                r.state_slug
            ORDER BY
                r.state_name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getNextEventMapByState(): array
    {
        $sql = "
            SELECT
                x.state_slug,
                x.state_name,
                x.next_election_date,
                x.election_type,
                x.election_type_slug,
                x.offices
            FROM (
                SELECT
                    r.state_slug,
                    r.state_name,
                    e.election_date AS next_election_date,
                    et.name AS election_type,
                    et.slug AS election_type_slug,
                    et.sort_order AS election_type_sort,
                    GROUP_CONCAT(DISTINCT o.name ORDER BY o.sort_order ASC, o.name ASC SEPARATOR '||') AS offices,
                    ROW_NUMBER() OVER (
                        PARTITION BY r.state_slug
                        ORDER BY
                            e.election_date ASC,
                            et.sort_order ASC,
                            et.name ASC
                    ) AS row_num
                FROM elections e
                INNER JOIN election_types et
                    ON et.election_type_id = e.election_type_id
                INNER JOIN races r
                    ON r.race_id = e.race_id
                INNER JOIN offices o
                    ON o.office_id = r.office_id
                WHERE e.election_date >= CURDATE()
                  AND e.status = 'upcoming'
                  AND r.status = 'active'
                GROUP BY
                    r.state_slug,
                    r.state_name,
                    e.election_date,
                    et.name,
                    et.slug,
                    et.sort_order
            ) AS x
            WHERE x.row_num = 1
            ORDER BY
                x.state_name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];

        foreach ($rows as $row) {
            $stateSlug = (string) ($row['state_slug'] ?? '');

            if ($stateSlug === '') {
                continue;
            }

            $map[$stateSlug] = [
                'next_election_date' => $row['next_election_date'] ?? null,
                'election_type' => $row['election_type'] ?? null,
                'election_type_slug' => $row['election_type_slug'] ?? null,
                'offices' => $row['offices'] ?? null,
            ];
        }

        return $map;
    }

    public function getElectionEventDetail(
        string $stateSlug,
        string $electionTypeSlug,
        string $electionDate
    ): ?array {
        $sql = "
        SELECT
            e.election_date,
            r.state_name,
            r.state_slug,
            et.slug AS election_type_slug,
            et.name AS election_type,
            GROUP_CONCAT(DISTINCT o.name ORDER BY o.sort_order ASC, o.name ASC SEPARATOR '||') AS offices
        FROM elections e
        INNER JOIN election_types et
            ON et.election_type_id = e.election_type_id
        INNER JOIN races r
            ON r.race_id = e.race_id
        INNER JOIN offices o
            ON o.office_id = r.office_id
        WHERE r.state_slug = :stateSlug
          AND et.slug = :electionTypeSlug
          AND e.election_date = :electionDate
          AND e.status = 'upcoming'
          AND r.status = 'active'
        GROUP BY
            e.election_date,
            r.state_name,
            r.state_slug,
            et.slug,
            et.name
        LIMIT 1
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':stateSlug' => $stateSlug,
            ':electionTypeSlug' => $electionTypeSlug,
            ':electionDate' => $electionDate,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return [
            'state_name' => $row['state_name'],
            'state_slug' => $row['state_slug'],
            'election_type' => $row['election_type'],
            'election_type_slug' => $row['election_type_slug'],
            'election_date' => $row['election_date'],
            'event_label' => $this->buildEventLabel(
                $row['state_name'],
                $row['election_type']
            ),
            'offices' => $row['offices'],
        ];
    }

    private function buildEventLabel(string $stateName, string $electionType): string
    {
        $stateName = trim($stateName);
        $typeLabel = $this->formatElectionType($electionType);

        if ($stateName === '') {
            return $typeLabel;
        }

        if ($typeLabel === '') {
            return $stateName;
        }

        return $stateName . ' ' . $typeLabel;
    }

    private function formatElectionType(string $value): string
    {
        return match (strtolower(trim($value))) {
            'primary' => 'Primary',
            'general' => 'General',
            'runoff' => 'Runoff',
            'special' => 'Special',
            'special-primary' => 'Special Primary',
            'jungle primary' => 'Jungle Primary',
            'ranked-choice general' => 'Ranked Choice General',
            default => trim($value),
        };
    }

    public function getEventRacesByOffice(
        string $stateSlug,
        string $electionTypeSlug,
        string $electionDate
    ): array {
        $sql = "
        SELECT
            o.name AS office_name,
            o.slug AS office_slug,
            r.race_id,
            r.state_name,
            r.state_slug,
            r.district_type,
            r.district_number,
            r.office_id,
            r.election_year
        FROM elections e
        INNER JOIN election_types et
            ON et.election_type_id = e.election_type_id
        INNER JOIN races r
            ON r.race_id = e.race_id
        INNER JOIN offices o
            ON o.office_id = r.office_id
        WHERE r.state_slug = :stateSlug
          AND et.slug = :electionTypeSlug
          AND e.election_date = :electionDate
          AND r.status = 'active'
        ORDER BY
            o.sort_order ASC,
            o.name ASC,
            r.district_number ASC
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':stateSlug' => $stateSlug,
            ':electionTypeSlug' => $electionTypeSlug,
            ':electionDate' => $electionDate,
        ]);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $grouped = [];

        foreach ($rows as $row) {
            $office = $row['office_name'];

            if (!isset($grouped[$office])) {
                $grouped[$office] = [];
            }

            $grouped[$office][] = [
                'race_id' => (int)$row['race_id'],
                'state_slug' => $row['state_slug'],
                'office_slug' => $row['office_slug'],
                'year' => (int)$row['election_year'],
                'district_type' => $row['district_type'],
                'district_number' => (int)$row['district_number'],
                'label' => $this->buildRaceLabel($row),
                'url' => $this->buildRaceUrl($row),
            ];
        }


        return $grouped;
    }

    private function buildRaceLabel(array $row): string
    {
        $stateName = trim((string)($row['state_name'] ?? ''));
        $officeName = trim((string)($row['office_name'] ?? ''));
        $districtType = $row['district_type'] ?? '';
        $districtNumber = (int)($row['district_number'] ?? 0);

        // U.S. House (district-based)
        if (
            $districtType === 'congressional_district' &&
            $districtNumber > 0
        ) {
            return $stateName . ' District ' . $districtNumber;
        }

        // Statewide races (Senate, Governor, President)
        if ($stateName !== '') {
            return $stateName;
        }

        return $officeName !== '' ? $officeName : 'Race';
    }

    private function buildRaceUrl(array $row): string
    {
        $base = '/races/' . rawurlencode($row['state_slug']) . '/' . rawurlencode($row['office_slug']) . '/' . (int)$row['election_year'];

        if (
            ($row['district_type'] ?? '') === 'congressional_district' &&
            (int)$row['district_number'] > 0
        ) {
            return $base . '/district-' . (int)$row['district_number'];
        }

        return $base;
    }
}
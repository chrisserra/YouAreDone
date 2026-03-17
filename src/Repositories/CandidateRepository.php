<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class CandidateRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function findBySlug(string $slug): ?array
    {
        $sql = "
            SELECT c.*
            FROM candidates c
            WHERE c.slug = :slug
              AND c.status = 'active'
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'slug' => trim($slug),
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function getCandidateFlags(int $candidateId): array
    {
        $sql = "
            SELECT
                cf.candidate_flag_id,
                cf.candidate_id,
                cf.flag_id,
                cf.source_id,
                cf.weight_override,
                cf.note,
                cf.is_active,
                cf.created_at,
                cf.updated_at,
                f.slug AS flag_slug,
                f.name AS flag_name,
                f.description AS flag_description,
                f.flag_color,
                f.default_weight,
                COALESCE(cf.weight_override, f.default_weight) AS effective_weight,
                cs.source_name,
                cs.source_url,
                cs.source_type
            FROM candidate_flags cf
            INNER JOIN flags f
                ON f.flag_id = cf.flag_id
            LEFT JOIN candidate_sources cs
                ON cs.source_id = cf.source_id
            WHERE cf.candidate_id = :candidate_id
              AND cf.is_active = 1
              AND f.is_active = 1
            ORDER BY
                f.flag_color ASC,
                f.sort_order ASC,
                f.name ASC,
                cf.candidate_flag_id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'candidate_id' => $candidateId,
        ]);

        return $stmt->fetchAll();
    }

    public function getElectionSpecificFlags(int $candidateId): array
    {
        $sql = "
            SELECT
                ecf.election_candidate_flag_id,
                ecf.election_id,
                ecf.candidate_id,
                ecf.flag_id,
                ecf.source_id,
                ecf.weight_override,
                ecf.note,
                ecf.is_active,
                ecf.created_at,
                ecf.updated_at,
                f.slug AS flag_slug,
                f.name AS flag_name,
                f.description AS flag_description,
                f.flag_color,
                f.default_weight,
                COALESCE(ecf.weight_override, f.default_weight) AS effective_weight,
                e.title AS election_title,
                e.slug AS election_slug,
                e.election_date,
                e.round_number,
                et.name AS election_type_name,
                et.slug AS election_type_slug,
                r.race_id,
                r.race_slug,
                r.election_year,
                r.state_code,
                r.state_name,
                r.state_slug,
                r.district_type,
                r.district_number,
                r.district_label,
                r.seat_label,
                r.is_special,
                o.name AS office_name,
                o.slug AS office_slug,
                cs.source_name,
                cs.source_url,
                cs.source_type
            FROM election_candidate_flags ecf
            INNER JOIN flags f
                ON f.flag_id = ecf.flag_id
            INNER JOIN elections e
                ON e.election_id = ecf.election_id
            INNER JOIN election_types et
                ON et.election_type_id = e.election_type_id
            INNER JOIN races r
                ON r.race_id = e.race_id
            INNER JOIN offices o
                ON o.office_id = r.office_id
            LEFT JOIN candidate_sources cs
                ON cs.source_id = ecf.source_id
            WHERE ecf.candidate_id = :candidate_id
              AND ecf.is_active = 1
              AND f.is_active = 1
            ORDER BY
                e.election_date DESC,
                e.round_number DESC,
                f.flag_color ASC,
                f.sort_order ASC,
                f.name ASC,
                ecf.election_candidate_flag_id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'candidate_id' => $candidateId,
        ]);

        return $stmt->fetchAll();
    }

    public function getElectionHistory(int $candidateId): array
    {
        $sql = "
            SELECT
                ec.election_candidate_id,
                ec.election_id,
                ec.candidate_id,
                ec.ballot_name,
                ec.party_code AS ballot_party_code,
                ec.filing_status,
                ec.ballot_status,
                ec.result_status,
                ec.is_incumbent,
                ec.is_major_candidate,
                ec.sort_order,
                ec.vote_count,
                ec.vote_percent,
                ec.notes_public,
                e.title AS election_title,
                e.slug AS election_slug,
                e.election_date,
                e.round_number,
                e.status AS election_status,
                et.name AS election_type_name,
                et.slug AS election_type_slug,
                r.race_id,
                r.race_slug,
                r.election_year,
                r.state_code,
                r.state_name,
                r.state_slug,
                r.district_type,
                r.district_number,
                r.district_label,
                r.seat_label,
                r.is_special,
                o.name AS office_name,
                o.slug AS office_slug
            FROM election_candidates ec
            INNER JOIN elections e
                ON e.election_id = ec.election_id
            INNER JOIN election_types et
                ON et.election_type_id = e.election_type_id
            INNER JOIN races r
                ON r.race_id = e.race_id
            INNER JOIN offices o
                ON o.office_id = r.office_id
            WHERE ec.candidate_id = :candidate_id
            ORDER BY
                e.election_date DESC,
                e.round_number DESC,
                r.election_year DESC,
                o.sort_order ASC,
                r.race_id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'candidate_id' => $candidateId,
        ]);

        return $stmt->fetchAll();
    }

    public function getCandidatePage(string $slug): ?array
    {
        $candidate = $this->findBySlug($slug);

        if (!$candidate) {
            return null;
        }

        $candidateId = (int)$candidate['candidate_id'];

        return [
            'candidate' => $candidate,
            'candidate_flags' => $this->getCandidateFlags($candidateId),
            'election_flags' => $this->getElectionSpecificFlags($candidateId),
            'history' => $this->getElectionHistory($candidateId),
        ];
    }

    public function getHomepageStats(): array
    {
        $sql = "
            SELECT
                (SELECT COUNT(*)
                 FROM races r
                 WHERE r.status = 'active') AS activeRaces,

                (SELECT COUNT(*)
                 FROM elections e
                 INNER JOIN races r
                     ON r.race_id = e.race_id
                 WHERE r.status = 'active'
                   AND e.status IN ('upcoming', 'ongoing')) AS upcomingElections,

                (SELECT COUNT(*)
                 FROM candidates c
                 WHERE c.status = 'active') AS trackedCandidates,

                (
                    (SELECT COUNT(*)
                     FROM candidate_flags cf
                     WHERE cf.is_active = 1)
                    +
                    (SELECT COUNT(*)
                     FROM election_candidate_flags ecf
                     WHERE ecf.is_active = 1)
                ) AS documentedFlags
        ";

        $stmt = $this->db->query($sql);
        $row = $stmt->fetch() ?: [];

        return [
            'activeRaces' => (int)($row['activeRaces'] ?? 0),
            'upcomingElections' => (int)($row['upcomingElections'] ?? 0),
            'trackedCandidates' => (int)($row['trackedCandidates'] ?? 0),
            'documentedFlags' => (int)($row['documentedFlags'] ?? 0),
        ];
    }

    public function getHomepageElectionCandidatePreview(int $electionId, int $limit = 3): array
    {
        $limit = max(1, $limit);

        $sql = "
            SELECT
                ec.election_candidate_id,
                ec.election_id,
                ec.candidate_id,
                ec.ballot_name,
                ec.filing_status,
                ec.ballot_status,
                ec.result_status,
                ec.is_incumbent,
                ec.is_major_candidate,
                ec.sort_order,
                c.full_name,
                c.slug,
                c.score_total,
                c.green_flag_count,
                c.red_flag_count
            FROM election_candidates ec
            INNER JOIN candidates c
                ON c.candidate_id = ec.candidate_id
            WHERE ec.election_id = :election_id
              AND c.status = 'active'
            ORDER BY
                ec.is_major_candidate DESC,
                ec.is_incumbent DESC,
                ec.sort_order ASC,
                c.full_name ASC,
                c.candidate_id ASC
            LIMIT {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'election_id' => $electionId,
        ]);

        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['candidate_url'] = '/candidate/' . rawurlencode((string)$row['slug']);
            $row['candidate_id'] = (int)$row['candidate_id'];
            $row['election_id'] = (int)$row['election_id'];
            $row['is_incumbent'] = (int)$row['is_incumbent'];
            $row['is_major_candidate'] = (int)$row['is_major_candidate'];
            $row['sort_order'] = (int)$row['sort_order'];
            $row['score_total'] = (float)$row['score_total'];
            $row['green_flag_count'] = (int)$row['green_flag_count'];
            $row['red_flag_count'] = (int)$row['red_flag_count'];
        }
        unset($row);

        return $rows;
    }

    public function getHomepageRaceCandidatePreview(int $raceId, int $limit = 5): array
    {
        $limit = max(1, $limit);

        $sql = "
            SELECT
                c.candidate_id,
                c.full_name,
                c.slug,
                c.score_total,
                c.green_flag_count,
                c.red_flag_count,
                MAX(ec.is_major_candidate) AS is_major_candidate,
                MAX(ec.is_incumbent) AS is_incumbent,
                MIN(ec.sort_order) AS best_sort_order
            FROM election_candidates ec
            INNER JOIN elections e
                ON e.election_id = ec.election_id
            INNER JOIN candidates c
                ON c.candidate_id = ec.candidate_id
            WHERE e.race_id = :race_id
              AND c.status = 'active'
            GROUP BY
                c.candidate_id,
                c.full_name,
                c.slug,
                c.score_total,
                c.green_flag_count,
                c.red_flag_count
            ORDER BY
                MAX(ec.is_major_candidate) DESC,
                MAX(ec.is_incumbent) DESC,
                MIN(ec.sort_order) ASC,
                c.full_name ASC,
                c.candidate_id ASC
            LIMIT {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'race_id' => $raceId,
        ]);

        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['candidate_url'] = '/candidate/' . rawurlencode((string)$row['slug']);
            $row['candidate_id'] = (int)$row['candidate_id'];
            $row['is_major_candidate'] = (int)$row['is_major_candidate'];
            $row['is_incumbent'] = (int)$row['is_incumbent'];
            $row['best_sort_order'] = (int)$row['best_sort_order'];
            $row['score_total'] = (float)$row['score_total'];
            $row['green_flag_count'] = (int)$row['green_flag_count'];
            $row['red_flag_count'] = (int)$row['red_flag_count'];
        }
        unset($row);

        return $rows;
    }

    public function getHomepageAccountabilitySignals(int $limit = 5): array
    {
        $limit = max(1, $limit);

        $baseSql = "
            SELECT
                c.candidate_id,
                c.full_name,
                c.slug,
                c.score_total,
                c.green_flag_count,
                c.red_flag_count,
                r.state_name,
                r.election_year,
                o.name AS office_name,
                r.district_type,
                r.district_number,
                r.district_label
            FROM candidates c
            INNER JOIN (
                SELECT
                    ec.candidate_id,
                    MIN(e.election_date) AS first_active_election_date
                FROM election_candidates ec
                INNER JOIN elections e
                    ON e.election_id = ec.election_id
                INNER JOIN races r
                    ON r.race_id = e.race_id
                WHERE r.status = 'active'
                GROUP BY ec.candidate_id
            ) active_candidates
                ON active_candidates.candidate_id = c.candidate_id
            LEFT JOIN election_candidates ec2
                ON ec2.candidate_id = c.candidate_id
            LEFT JOIN elections e2
                ON e2.election_id = ec2.election_id
            LEFT JOIN races r
                ON r.race_id = e2.race_id
            LEFT JOIN offices o
                ON o.office_id = r.office_id
            WHERE c.status = 'active'
            GROUP BY
                c.candidate_id,
                c.full_name,
                c.slug,
                c.score_total,
                c.green_flag_count,
                c.red_flag_count,
                r.state_name,
                r.election_year,
                o.name,
                r.district_type,
                r.district_number,
                r.district_label
        ";

        $negativeSql = $baseSql . "
            HAVING c.score_total < 0
            ORDER BY
                c.score_total ASC,
                c.red_flag_count DESC,
                c.green_flag_count ASC,
                c.full_name ASC
            LIMIT {$limit}
        ";

        $positiveSql = $baseSql . "
            HAVING c.score_total > 0
            ORDER BY
                c.score_total DESC,
                c.green_flag_count DESC,
                c.red_flag_count ASC,
                c.full_name ASC
            LIMIT {$limit}
        ";

        $negativeRows = $this->db->query($negativeSql)->fetchAll();
        $positiveRows = $this->db->query($positiveSql)->fetchAll();

        return [
            'negative' => array_map(fn (array $row): array => $this->mapHomepageSignalRow($row), $negativeRows),
            'positive' => array_map(fn (array $row): array => $this->mapHomepageSignalRow($row), $positiveRows),
        ];
    }

    public function getHomepageLatestUpdates(int $limit = 8): array
    {
        $limit = max(1, $limit);

        $sql = "
            SELECT
                cu.update_id,
                cu.candidate_id,
                cu.election_id,
                cu.source_id,
                cu.update_type,
                cu.headline,
                cu.summary,
                cu.source_date,
                cu.sort_date,
                cu.is_public,
                c.full_name AS candidate_name,
                c.slug AS candidate_slug,
                cs.source_name,
                cs.source_url,
                cs.source_type,
                e.title AS election_title,
                e.election_date,
                e.slug AS election_slug,
                r.race_id,
                r.race_slug,
                r.state_name,
                r.election_year,
                r.district_type,
                r.district_number,
                r.district_label,
                o.name AS office_name
            FROM candidate_updates cu
            INNER JOIN candidates c
                ON c.candidate_id = cu.candidate_id
            LEFT JOIN candidate_sources cs
                ON cs.source_id = cu.source_id
            LEFT JOIN elections e
                ON e.election_id = cu.election_id
            LEFT JOIN races r
                ON r.race_id = e.race_id
            LEFT JOIN offices o
                ON o.office_id = r.office_id
            WHERE cu.is_public = 1
            ORDER BY
                cu.sort_date DESC,
                cu.update_id DESC
            LIMIT {$limit}
        ";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['update_id'] = (int)$row['update_id'];
            $row['candidate_id'] = (int)$row['candidate_id'];
            $row['election_id'] = $row['election_id'] !== null ? (int)$row['election_id'] : null;
            $row['source_id'] = $row['source_id'] !== null ? (int)$row['source_id'] : null;
            $row['candidate_url'] = '/candidate/' . rawurlencode((string)$row['candidate_slug']);
            $row['race_label'] = $this->buildRaceLabel(
                $row['state_name'] ?? null,
                $row['office_name'] ?? null,
                isset($row['election_year']) ? (int)$row['election_year'] : null,
                $row['district_label'] ?? null,
                $row['district_type'] ?? null,
                isset($row['district_number']) ? (int)$row['district_number'] : null
            );
            $row['race_url'] = !empty($row['race_slug'])
                ? '/races/' . ltrim((string)$row['race_slug'], '/')
                : null;
        }
        unset($row);

        return $rows;
    }

    private function mapHomepageSignalRow(array $row): array
    {
        return [
            'candidate_id' => (int)$row['candidate_id'],
            'full_name' => (string)$row['full_name'],
            'slug' => (string)$row['slug'],
            'candidate_url' => '/candidate/' . rawurlencode((string)$row['slug']),
            'score_total' => (float)$row['score_total'],
            'green_flag_count' => (int)$row['green_flag_count'],
            'red_flag_count' => (int)$row['red_flag_count'],
            'top_race_label' => $this->buildRaceLabel(
                $row['state_name'] ?? null,
                $row['office_name'] ?? null,
                isset($row['election_year']) ? (int)$row['election_year'] : null,
                $row['district_label'] ?? null,
                $row['district_type'] ?? null,
                isset($row['district_number']) ? (int)$row['district_number'] : null
            ),
        ];
    }

    private function buildRaceLabel(
        ?string $stateName,
        ?string $officeName,
        ?int $electionYear,
        ?string $districtLabel,
        ?string $districtType,
        ?int $districtNumber
    ): string {
        $parts = [];

        if ($stateName) {
            $parts[] = trim($stateName);
        }

        if ($officeName) {
            $parts[] = trim($officeName);
        }

        if ($districtLabel) {
            $parts[] = trim($districtLabel);
        } elseif ($districtType === 'congressional_district' && $districtNumber !== null && $districtNumber > 0) {
            $parts[] = 'District ' . $districtNumber;
        }

        if ($electionYear !== null && $electionYear > 0) {
            $parts[] = (string)$electionYear;
        }

        return trim(implode(' ', $parts));
    }

    public function getCandidatePreviewFlags(
        int $candidateId,
        string $flagColor,
        int $limit = 3,
        ?int $electionId = null
    ): array {
        $limit = max(1, $limit);

        if ($electionId !== null && $electionId > 0) {
            $sql = "
        SELECT
            chosen.candidate_flag_id,
            chosen.candidate_id,
            chosen.note,
            chosen.flag_id,
            chosen.flag_slug,
            chosen.flag_name,
            chosen.flag_color,
            chosen.default_weight,
            chosen.effective_weight
        FROM (
            SELECT
                ecf.election_candidate_flag_id AS candidate_flag_id,
                ecf.candidate_id,
                ecf.note,
                f.flag_id,
                f.slug AS flag_slug,
                f.name AS flag_name,
                f.flag_color,
                f.default_weight,
                COALESCE(ecf.weight_override, f.default_weight) AS effective_weight
            FROM election_candidate_flags ecf
            INNER JOIN flags f
                ON f.flag_id = ecf.flag_id
            WHERE ecf.election_id = :election_id
              AND ecf.candidate_id = :candidate_id
              AND ecf.is_active = 1
              AND f.is_active = 1
              AND f.flag_color = :flag_color

            UNION ALL

            SELECT
                cf.candidate_flag_id AS candidate_flag_id,
                cf.candidate_id,
                cf.note,
                f.flag_id,
                f.slug AS flag_slug,
                f.name AS flag_name,
                f.flag_color,
                f.default_weight,
                COALESCE(cf.weight_override, f.default_weight) AS effective_weight
            FROM candidate_flags cf
            INNER JOIN flags f
                ON f.flag_id = cf.flag_id
            WHERE cf.candidate_id = :candidate_id
              AND cf.is_active = 1
              AND f.is_active = 1
              AND f.flag_color = :flag_color
              AND NOT EXISTS (
                  SELECT 1
                  FROM election_candidate_flags ecf2
                  WHERE ecf2.election_id = :election_id
                    AND ecf2.candidate_id = cf.candidate_id
                    AND ecf2.flag_id = cf.flag_id
                    AND ecf2.is_active = 1
              )
        ) AS chosen
        ORDER BY
            ABS(chosen.effective_weight) DESC,
            chosen.flag_name ASC,
            chosen.candidate_flag_id ASC
        LIMIT {$limit}
        ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':election_id' => $electionId,
                ':candidate_id' => $candidateId,
                ':flag_color' => $flagColor,
            ]);
        } else {
            $sql = "
        SELECT
            cf.candidate_flag_id,
            cf.candidate_id,
            cf.note,
            f.flag_id,
            f.slug AS flag_slug,
            f.name AS flag_name,
            f.flag_color,
            f.default_weight,
            COALESCE(cf.weight_override, f.default_weight) AS effective_weight
        FROM candidate_flags cf
        INNER JOIN flags f
            ON f.flag_id = cf.flag_id
        WHERE cf.candidate_id = :candidate_id
          AND cf.is_active = 1
          AND f.is_active = 1
          AND f.flag_color = :flag_color
        ORDER BY
            ABS(COALESCE(cf.weight_override, f.default_weight)) DESC,
            f.name ASC,
            cf.candidate_flag_id ASC
        LIMIT {$limit}
        ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':candidate_id' => $candidateId,
                ':flag_color' => $flagColor,
            ]);
        }

        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['candidate_flag_id'] = (int) ($row['candidate_flag_id'] ?? 0);
            $row['candidate_id'] = (int) ($row['candidate_id'] ?? 0);
            $row['flag_id'] = (int) ($row['flag_id'] ?? 0);
            $row['default_weight'] = (float) ($row['default_weight'] ?? 0);
            $row['effective_weight'] = (float) ($row['effective_weight'] ?? 0);
            $row['note'] = trim((string) ($row['note'] ?? ''));
        }

        unset($row);

        return $rows;
    }

    public function getCandidatePreviewReasonGroups(int $candidateId, ?int $electionId = null): array
    {
        return [
            'green' => $this->getCandidatePreviewFlags($candidateId, 'green', 3, $electionId),
            'red' => $this->getCandidatePreviewFlags($candidateId, 'red', 3, $electionId),
        ];
    }

    /**
     * @param array<int, array{candidate_id:int, election_id:int|null}> $candidateElectionPairs
     * @return array<string, array{green: array<int, array<string, mixed>>, red: array<int, array<string, mixed>>}>
     */
    public function getCandidatePreviewReasonGroupsMap(array $candidateElectionPairs, int $limitPerColor = 3): array
    {
        $limitPerColor = max(1, $limitPerColor);

        $normalizedPairs = [];
        $candidateIds = [];
        $electionIds = [];

        foreach ($candidateElectionPairs as $pair) {
            $candidateId = (int) ($pair['candidate_id'] ?? 0);
            $electionId = isset($pair['election_id']) ? (int) $pair['election_id'] : 0;

            if ($candidateId <= 0) {
                continue;
            }

            $key = $candidateId . ':' . ($electionId > 0 ? $electionId : 0);

            $normalizedPairs[$key] = [
                'candidate_id' => $candidateId,
                'election_id' => $electionId > 0 ? $electionId : null,
            ];

            $candidateIds[$candidateId] = $candidateId;

            if ($electionId > 0) {
                $electionIds[$electionId] = $electionId;
            }
        }

        if ($normalizedPairs === []) {
            return [];
        }

        $result = [];

        foreach ($normalizedPairs as $key => $pair) {
            $result[$key] = [
                'green' => [],
                'red' => [],
            ];
        }

        $candidateIdList = implode(',', array_map('intval', array_values($candidateIds)));
        $electionIdList = implode(',', array_map('intval', array_values($electionIds)));

        $eventSpecificByPair = [];
        $globalByCandidate = [];

        if ($electionIdList !== '') {
            $sql = "
        SELECT
            ecf.election_candidate_flag_id AS candidate_flag_id,
            ecf.election_id,
            ecf.candidate_id,
            ecf.note,
            f.flag_id,
            f.slug AS flag_slug,
            f.name AS flag_name,
            f.flag_color,
            f.default_weight,
            COALESCE(ecf.weight_override, f.default_weight) AS effective_weight
        FROM election_candidate_flags ecf
        INNER JOIN flags f
            ON f.flag_id = ecf.flag_id
        WHERE ecf.is_active = 1
          AND f.is_active = 1
          AND ecf.candidate_id IN ({$candidateIdList})
          AND ecf.election_id IN ({$electionIdList})
          AND f.flag_color IN ('green', 'red')
        ORDER BY
            ecf.election_id ASC,
            ecf.candidate_id ASC,
            f.flag_color ASC,
            ABS(COALESCE(ecf.weight_override, f.default_weight)) DESC,
            f.sort_order ASC,
            f.name ASC,
            ecf.election_candidate_flag_id ASC
        ";

            $rows = $this->db->query($sql)->fetchAll();

            foreach ($rows as $row) {
                $electionId = (int) ($row['election_id'] ?? 0);
                $candidateId = (int) ($row['candidate_id'] ?? 0);
                $flagColor = (string) ($row['flag_color'] ?? '');
                $flagId = (int) ($row['flag_id'] ?? 0);

                if ($electionId <= 0 || $candidateId <= 0 || $flagId <= 0 || ($flagColor !== 'green' && $flagColor !== 'red')) {
                    continue;
                }

                $pairKey = $candidateId . ':' . $electionId;

                if (!isset($normalizedPairs[$pairKey])) {
                    continue;
                }

                if (!isset($eventSpecificByPair[$pairKey])) {
                    $eventSpecificByPair[$pairKey] = [
                        'green' => [],
                        'red' => [],
                    ];
                }

                if (isset($eventSpecificByPair[$pairKey][$flagColor][$flagId])) {
                    continue;
                }

                $eventSpecificByPair[$pairKey][$flagColor][$flagId] = [
                    'candidate_flag_id' => (int) ($row['candidate_flag_id'] ?? 0),
                    'candidate_id' => $candidateId,
                    'note' => trim((string) ($row['note'] ?? '')),
                    'flag_id' => $flagId,
                    'flag_slug' => (string) ($row['flag_slug'] ?? ''),
                    'flag_name' => (string) ($row['flag_name'] ?? ''),
                    'flag_color' => $flagColor,
                    'default_weight' => (float) ($row['default_weight'] ?? 0),
                    'effective_weight' => (float) ($row['effective_weight'] ?? 0),
                ];
            }
        }

        $sql = "
    SELECT
        cf.candidate_flag_id,
        cf.candidate_id,
        cf.note,
        f.flag_id,
        f.slug AS flag_slug,
        f.name AS flag_name,
        f.flag_color,
        f.default_weight,
        COALESCE(cf.weight_override, f.default_weight) AS effective_weight
    FROM candidate_flags cf
    INNER JOIN flags f
        ON f.flag_id = cf.flag_id
    WHERE cf.is_active = 1
      AND f.is_active = 1
      AND cf.candidate_id IN ({$candidateIdList})
      AND f.flag_color IN ('green', 'red')
    ORDER BY
        cf.candidate_id ASC,
        f.flag_color ASC,
        ABS(COALESCE(cf.weight_override, f.default_weight)) DESC,
        f.sort_order ASC,
        f.name ASC,
        cf.candidate_flag_id ASC
    ";

        $rows = $this->db->query($sql)->fetchAll();

        foreach ($rows as $row) {
            $candidateId = (int) ($row['candidate_id'] ?? 0);
            $flagColor = (string) ($row['flag_color'] ?? '');
            $flagId = (int) ($row['flag_id'] ?? 0);

            if ($candidateId <= 0 || $flagId <= 0 || ($flagColor !== 'green' && $flagColor !== 'red')) {
                continue;
            }

            if (!isset($globalByCandidate[$candidateId])) {
                $globalByCandidate[$candidateId] = [
                    'green' => [],
                    'red' => [],
                ];
            }

            if (isset($globalByCandidate[$candidateId][$flagColor][$flagId])) {
                continue;
            }

            $globalByCandidate[$candidateId][$flagColor][$flagId] = [
                'candidate_flag_id' => (int) ($row['candidate_flag_id'] ?? 0),
                'candidate_id' => $candidateId,
                'note' => trim((string) ($row['note'] ?? '')),
                'flag_id' => $flagId,
                'flag_slug' => (string) ($row['flag_slug'] ?? ''),
                'flag_name' => (string) ($row['flag_name'] ?? ''),
                'flag_color' => $flagColor,
                'default_weight' => (float) ($row['default_weight'] ?? 0),
                'effective_weight' => (float) ($row['effective_weight'] ?? 0),
            ];
        }

        foreach ($normalizedPairs as $key => $pair) {
            $candidateId = (int) $pair['candidate_id'];
            $electionId = isset($pair['election_id']) ? (int) $pair['election_id'] : 0;
            $pairKey = $candidateId . ':' . ($electionId > 0 ? $electionId : 0);
            $eventPairKey = $candidateId . ':' . $electionId;

            foreach (['green', 'red'] as $flagColor) {
                $selected = [];
                $seenFlagIds = [];

                if ($electionId > 0 && isset($eventSpecificByPair[$eventPairKey][$flagColor])) {
                    foreach ($eventSpecificByPair[$eventPairKey][$flagColor] as $flagId => $row) {
                        $selected[] = $row;
                        $seenFlagIds[(int) $flagId] = true;

                        if (count($selected) >= $limitPerColor) {
                            break;
                        }
                    }
                }

                if (count($selected) < $limitPerColor && isset($globalByCandidate[$candidateId][$flagColor])) {
                    foreach ($globalByCandidate[$candidateId][$flagColor] as $flagId => $row) {
                        $flagId = (int) $flagId;

                        if (isset($seenFlagIds[$flagId])) {
                            continue;
                        }

                        $selected[] = $row;
                        $seenFlagIds[$flagId] = true;

                        if (count($selected) >= $limitPerColor) {
                            break;
                        }
                    }
                }

                $result[$pairKey][$flagColor] = $selected;
            }
        }

        return $result;
    }
}
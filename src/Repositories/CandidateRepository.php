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

        $candidateId = (int) $candidate['candidate_id'];

        return [
            'candidate' => $candidate,
            'candidate_flags' => $this->getCandidateFlags($candidateId),
            'history' => $this->getElectionHistory($candidateId),
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
                c.score_total DESC,
                c.green_flag_count DESC,
                c.red_flag_count ASC,
                ec.is_incumbent DESC,
                c.full_name ASC
            LIMIT {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'election_id' => $electionId,
        ]);

        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['candidate_url'] = '/candidate/' . rawurlencode((string) $row['slug']);
            $row['election_candidate_id'] = (int) ($row['election_candidate_id'] ?? 0);
            $row['election_id'] = (int) ($row['election_id'] ?? 0);
            $row['candidate_id'] = (int) ($row['candidate_id'] ?? 0);
            $row['is_incumbent'] = (int) ($row['is_incumbent'] ?? 0);
            $row['is_major_candidate'] = (int) ($row['is_major_candidate'] ?? 0);
            $row['sort_order'] = (int) ($row['sort_order'] ?? 0);
            $row['score_total'] = (float) ($row['score_total'] ?? 0);
            $row['green_flag_count'] = (int) ($row['green_flag_count'] ?? 0);
            $row['red_flag_count'] = (int) ($row['red_flag_count'] ?? 0);
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
                c.score_total DESC,
                c.green_flag_count DESC,
                c.red_flag_count ASC,
                c.full_name ASC
            LIMIT {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'race_id' => $raceId,
        ]);

        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['candidate_url'] = '/candidate/' . rawurlencode((string) $row['slug']);
            $row['candidate_id'] = (int) ($row['candidate_id'] ?? 0);
            $row['is_major_candidate'] = (int) ($row['is_major_candidate'] ?? 0);
            $row['is_incumbent'] = (int) ($row['is_incumbent'] ?? 0);
            $row['best_sort_order'] = (int) ($row['best_sort_order'] ?? 0);
            $row['score_total'] = (float) ($row['score_total'] ?? 0);
            $row['green_flag_count'] = (int) ($row['green_flag_count'] ?? 0);
            $row['red_flag_count'] = (int) ($row['red_flag_count'] ?? 0);
        }

        unset($row);

        return $rows;
    }

    /**
     * @param array<int, int> $candidateIds
     * @return array<int, array{green: array<int, array<string, mixed>>, red: array<int, array<string, mixed>>}>
     */
    public function getCandidatePreviewReasonGroupsMap(array $candidateIds, int $limitPerColor = 3): array
    {
        $limitPerColor = max(1, $limitPerColor);

        $normalizedCandidateIds = [];

        foreach ($candidateIds as $candidateId) {
            $candidateId = (int) $candidateId;

            if ($candidateId <= 0) {
                continue;
            }

            $normalizedCandidateIds[$candidateId] = $candidateId;
        }

        if ($normalizedCandidateIds === []) {
            return [];
        }

        $result = [];

        foreach ($normalizedCandidateIds as $candidateId) {
            $result[$candidateId] = [
                'green' => [],
                'red' => [],
            ];
        }

        $candidateIdList = implode(',', array_map('intval', array_values($normalizedCandidateIds)));

        $sql = "
        SELECT
            cf.candidate_flag_id,
            cf.candidate_id,
            cf.note,
            f.flag_id,
            f.slug AS flag_slug,
            f.name AS flag_name,
            f.description AS flag_description,
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

        $flagsByCandidate = [];

        foreach ($rows as $row) {
            $candidateId = (int) ($row['candidate_id'] ?? 0);
            $flagColor = (string) ($row['flag_color'] ?? '');
            $flagId = (int) ($row['flag_id'] ?? 0);

            if ($candidateId <= 0 || $flagId <= 0 || ($flagColor !== 'green' && $flagColor !== 'red')) {
                continue;
            }

            if (!isset($flagsByCandidate[$candidateId])) {
                $flagsByCandidate[$candidateId] = [
                    'green' => [],
                    'red' => [],
                ];
            }

            if (isset($flagsByCandidate[$candidateId][$flagColor][$flagId])) {
                continue;
            }

            $flagsByCandidate[$candidateId][$flagColor][$flagId] = [
                'candidate_flag_id' => (int) ($row['candidate_flag_id'] ?? 0),
                'candidate_id' => $candidateId,
                'note' => trim((string) ($row['note'] ?? '')),
                'flag_id' => $flagId,
                'flag_slug' => (string) ($row['flag_slug'] ?? ''),
                'flag_name' => (string) ($row['flag_name'] ?? ''),
                'flag_description' => trim((string) ($row['flag_description'] ?? '')),
                'flag_color' => $flagColor,
                'default_weight' => (float) ($row['default_weight'] ?? 0),
                'effective_weight' => (float) ($row['effective_weight'] ?? 0),
            ];
        }

        foreach ($normalizedCandidateIds as $candidateId) {
            foreach (['green', 'red'] as $flagColor) {
                $selected = [];

                if (isset($flagsByCandidate[$candidateId][$flagColor])) {
                    foreach ($flagsByCandidate[$candidateId][$flagColor] as $row) {
                        $selected[] = $row;

                        if (count($selected) >= $limitPerColor) {
                            break;
                        }
                    }
                }

                $result[$candidateId][$flagColor] = $selected;
            }
        }

        return $result;
    }
}
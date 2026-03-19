<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class RaceRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function findByRoute(
        string $stateSlug,
        string $officeSlug,
        int $year,
        ?int $district = null
    ): ?array {
        if ($district === null) {
            $sql = "
                SELECT
                    r.*,
                    o.slug AS office_slug,
                    o.name AS office_name
                FROM races r
                INNER JOIN offices o
                    ON o.office_id = r.office_id
                WHERE r.state_slug = :state_slug
                  AND o.slug = :office_slug
                  AND r.election_year = :election_year
                  AND r.status = 'active'
                  AND r.district_type = 'statewide'
                  AND r.district_number = 0
                LIMIT 1
            ";

            $params = [
                'state_slug' => trim($stateSlug),
                'office_slug' => trim($officeSlug),
                'election_year' => $year,
            ];
        } else {
            $sql = "
                SELECT
                    r.*,
                    o.slug AS office_slug,
                    o.name AS office_name
                FROM races r
                INNER JOIN offices o
                    ON o.office_id = r.office_id
                WHERE r.state_slug = :state_slug
                  AND o.slug = :office_slug
                  AND r.election_year = :election_year
                  AND r.status = 'active'
                  AND r.district_type = 'congressional_district'
                  AND r.district_number = :district_number
                LIMIT 1
            ";

            $params = [
                'state_slug' => trim($stateSlug),
                'office_slug' => trim($officeSlug),
                'election_year' => $year,
                'district_number' => $district,
            ];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function getElectionsForRace(int $raceId): array
    {
        $sql = "
            SELECT
                e.election_id,
                e.race_id,
                e.event_id,
                e.election_type_id,
                e.election_date,
                e.round_number,
                e.title,
                e.slug,
                e.status,
                e.filing_deadline,
                e.early_voting_start,
                e.early_voting_end,
                e.certification_date,
                e.notes_public,
                e.created_at,
                e.updated_at,
                et.slug AS election_type_slug,
                et.name AS election_type_name
            FROM elections e
            INNER JOIN election_types et
                ON et.election_type_id = e.election_type_id
            WHERE e.race_id = :race_id
            ORDER BY
                e.election_date ASC,
                e.round_number ASC,
                e.election_id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'race_id' => $raceId,
        ]);

        return $stmt->fetchAll();
    }

    public function getCandidatesForElection(int $electionId): array
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
                c.full_name,
                c.slug,
                c.first_name,
                c.middle_name,
                c.last_name,
                c.suffix,
                c.preferred_name,
                c.party_code,
                c.party_name,
                c.website_url,
                c.ballotpedia_url,
                c.wikipedia_url,
                c.x_url,
                c.instagram_url,
                c.facebook_url,
                c.youtube_url,
                c.image_url,
                c.short_bio,
                c.summary_public,
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
                ec.is_major_candidate DESC,
                ec.sort_order ASC,
                c.full_name ASC,
                c.candidate_id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'election_id' => $electionId,
        ]);

        $candidates = $stmt->fetchAll();

        if ($candidates === []) {
            return [];
        }

        $candidateIds = array_map(
            static fn(array $row): int => (int) $row['candidate_id'],
            $candidates
        );

        $previewFlagsMap = $this->getCandidatePreviewFlagsMap($candidateIds);

        foreach ($candidates as &$candidate) {
            $candidateId = (int) $candidate['candidate_id'];
            $candidate['preview_flags'] = $previewFlagsMap[$candidateId] ?? [
                'green' => [],
                'red' => [],
            ];
        }
        unset($candidate);

        return $candidates;
    }

    public function getRacePage(
        string $stateSlug,
        string $officeSlug,
        int $year,
        ?int $district = null
    ): ?array {
        $race = $this->findByRoute($stateSlug, $officeSlug, $year, $district);

        if (!$race) {
            return null;
        }

        $elections = $this->getElectionsForRace((int) $race['race_id']);

        foreach ($elections as &$election) {
            $election['candidates'] = $this->getCandidatesForElection((int) $election['election_id']);
        }
        unset($election);

        return [
            'race' => $this->mapRaceRow($race),
            'elections' => $elections,
        ];
    }

    private function mapRaceRow(array $race): array
    {
        $race['race_url'] = $this->buildRaceUrl(
            (string) $race['state_slug'],
            (string) $race['office_slug'],
            (int) $race['election_year'],
            (string) $race['district_type'],
            (int) $race['district_number']
        );

        $race['district_label'] = $this->buildDistrictLabel(
            (string) $race['district_type'],
            (int) $race['district_number']
        );

        return $race;
    }

    private function buildRaceUrl(
        string $stateSlug,
        string $officeSlug,
        int $year,
        string $districtType,
        int $districtNumber
    ): string {
        $url = '/races/' . rawurlencode($stateSlug) . '/' . rawurlencode($officeSlug) . '/' . $year;

        if ($districtType === 'congressional_district' && $districtNumber > 0) {
            $url .= '/district-' . $districtNumber;
        }

        return $url;
    }

    private function buildDistrictLabel(string $districtType, int $districtNumber): ?string
    {
        if ($districtType === 'congressional_district' && $districtNumber > 0) {
            return 'District ' . $districtNumber;
        }

        return null;
    }

    private function getCandidatePreviewFlagsMap(array $candidateIds): array
    {
        $candidateIds = array_values(array_unique(array_filter(array_map('intval', $candidateIds))));

        if ($candidateIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($candidateIds), '?'));

        $sql = "
            SELECT
                cf.candidate_id,
                f.flag_color,
                COALESCE(NULLIF(TRIM(f.name), ''), f.slug) AS flag_name,
                f.slug AS flag_slug,
                f.description AS flag_description,
                cf.note,
                COALESCE(cf.weight_override, f.default_weight) AS effective_weight
            FROM candidate_flags cf
            INNER JOIN flags f
                ON f.flag_id = cf.flag_id
            WHERE cf.candidate_id IN ($placeholders)
              AND cf.is_active = 1
              AND f.is_active = 1
              AND f.flag_color IN ('green', 'red')
            ORDER BY
                cf.candidate_id ASC,
                CASE
                    WHEN f.flag_color = 'green' THEN 1
                    WHEN f.flag_color = 'red' THEN 2
                    ELSE 3
                END ASC,
                ABS(COALESCE(cf.weight_override, f.default_weight)) DESC,
                COALESCE(NULLIF(TRIM(f.name), ''), f.slug) ASC,
                cf.candidate_flag_id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($candidateIds);

        $rows = $stmt->fetchAll();
        $grouped = [];

        foreach ($candidateIds as $candidateId) {
            $grouped[$candidateId] = [
                'green' => [],
                'red' => [],
            ];
        }

        foreach ($rows as $row) {
            $candidateId = (int) $row['candidate_id'];
            $flagColor = strtolower(trim((string) ($row['flag_color'] ?? '')));

            if ($flagColor !== 'green' && $flagColor !== 'red') {
                continue;
            }

            $grouped[$candidateId][$flagColor][] = [
                'flag_name' => (string) ($row['flag_name'] ?? ''),
                'flag_slug' => (string) ($row['flag_slug'] ?? ''),
                'flag_description' => (string) ($row['flag_description'] ?? ''),
                'note' => trim((string) ($row['note'] ?? '')),
                'effective_weight' => (float) ($row['effective_weight'] ?? 0),
            ];
        }

        return $grouped;
    }
}
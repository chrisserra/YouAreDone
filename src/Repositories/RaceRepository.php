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

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'state_slug' => trim($stateSlug),
                'office_slug' => trim($officeSlug),
                'election_year' => $year,
            ]);
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

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'state_slug' => trim($stateSlug),
                'office_slug' => trim($officeSlug),
                'election_year' => $year,
                'district_number' => $district,
            ]);
        }

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function getElectionsForRace(int $raceId): array
    {
        $sql = "
            SELECT
                e.election_id,
                e.race_id,
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
            ORDER BY e.election_date ASC, e.round_number ASC, e.election_id ASC
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

        return $stmt->fetchAll();
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

        $elections = $this->getElectionsForRace((int)$race['race_id']);

        foreach ($elections as &$election) {
            $election['candidates'] = $this->getCandidatesForElection((int)$election['election_id']);
        }
        unset($election);

        return [
            'race' => $this->mapRaceRow($race),
            'elections' => array_map(fn (array $row): array => $this->mapElectionRow($row), $elections),
        ];
    }

    public function getHomepageNextElection(): ?array
    {
        $sql = "
            SELECT
                e.election_id,
                e.race_id,
                e.title,
                e.slug AS election_slug,
                e.election_date,
                e.status,
                e.round_number,
                et.slug AS election_type_slug,
                et.name AS election_type_name,
                r.state_code,
                r.state_name,
                r.state_slug,
                r.election_year,
                r.district_type,
                r.district_number,
                r.notes_public,
                o.slug AS office_slug,
                o.name AS office_name,
                COUNT(ec.election_candidate_id) AS candidate_count
            FROM elections e
            INNER JOIN races r
                ON r.race_id = e.race_id
            INNER JOIN offices o
                ON o.office_id = r.office_id
            INNER JOIN election_types et
                ON et.election_type_id = e.election_type_id
            LEFT JOIN election_candidates ec
                ON ec.election_id = e.election_id
            WHERE r.status = 'active'
              AND e.status IN ('upcoming', 'ongoing')
              AND e.election_date >= CURDATE()
            GROUP BY
                e.election_id,
                e.race_id,
                e.title,
                e.slug,
                e.election_date,
                e.status,
                e.round_number,
                et.slug,
                et.name,
                r.state_code,
                r.state_name,
                r.state_slug,
                r.election_year,
                r.district_type,
                r.district_number,
                r.notes_public,
                o.slug,
                o.name
            ORDER BY
                e.election_date ASC,
                e.round_number ASC,
                e.election_id ASC
            LIMIT 1
        ";

        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->mapHomepageElectionCard($row);
    }

    public function getHomepageUpcomingElections(int $limit = 6): array
    {
        $limit = max(1, $limit);

        $sql = "
            SELECT
                e.election_id,
                e.race_id,
                e.title,
                e.slug AS election_slug,
                e.election_date,
                e.status,
                e.round_number,
                et.slug AS election_type_slug,
                et.name AS election_type_name,
                r.state_code,
                r.state_name,
                r.state_slug,
                r.election_year,
                r.district_type,
                r.district_number,
                r.notes_public,
                o.slug AS office_slug,
                o.name AS office_name,
                COUNT(ec.election_candidate_id) AS candidate_count
            FROM elections e
            INNER JOIN races r
                ON r.race_id = e.race_id
            INNER JOIN offices o
                ON o.office_id = r.office_id
            INNER JOIN election_types et
                ON et.election_type_id = e.election_type_id
            LEFT JOIN election_candidates ec
                ON ec.election_id = e.election_id
            WHERE r.status = 'active'
              AND e.status IN ('upcoming', 'ongoing')
              AND e.election_date >= CURDATE()
            GROUP BY
                e.election_id,
                e.race_id,
                e.title,
                e.slug,
                e.election_date,
                e.status,
                e.round_number,
                et.slug,
                et.name,
                r.state_code,
                r.state_name,
                r.state_slug,
                r.election_year,
                r.district_type,
                r.district_number,
                r.notes_public,
                o.slug,
                o.name
            ORDER BY
                e.election_date ASC,
                e.round_number ASC,
                e.election_id ASC
            LIMIT {$limit}
        ";

        $stmt = $this->db->query($sql);

        return array_map(
            fn (array $row): array => $this->mapHomepageElectionCard($row),
            $stmt->fetchAll()
        );
    }

    public function getHomepageMostWatchedRaces(int $limit = 4): array
    {
        $limit = max(1, $limit);

        $sql = "
            SELECT
                r.race_id,
                r.state_code,
                r.state_name,
                r.state_slug,
                r.election_year,
                r.district_type,
                r.district_number,
                r.notes_public,
                o.slug AS office_slug,
                o.name AS office_name,
                MIN(CASE WHEN e.status IN ('upcoming', 'ongoing') AND e.election_date >= CURDATE() THEN e.election_date END) AS next_election_date,
                MIN(CASE WHEN e.status IN ('upcoming', 'ongoing') AND e.election_date >= CURDATE() THEN et.name END) AS next_election_type_name,
                COUNT(DISTINCT ec.election_candidate_id) AS candidate_count,
                COUNT(DISTINCT cu.update_id) AS public_update_count,
                0 AS flag_count
            FROM races r
            INNER JOIN offices o
                ON o.office_id = r.office_id
            LEFT JOIN elections e
                ON e.race_id = r.race_id
            LEFT JOIN election_types et
                ON et.election_type_id = e.election_type_id
            LEFT JOIN election_candidates ec
                ON ec.election_id = e.election_id
            LEFT JOIN elections eu
                ON eu.race_id = r.race_id
            LEFT JOIN candidate_updates cu
                ON cu.election_id = eu.election_id
               AND cu.is_public = 1
            WHERE r.status = 'active'
            GROUP BY
                r.race_id,
                r.state_code,
                r.state_name,
                r.state_slug,
                r.election_year,
                r.district_type,
                r.district_number,
                r.notes_public,
                o.slug,
                o.name
            ORDER BY
                CASE WHEN MIN(CASE WHEN e.status IN ('upcoming', 'ongoing') AND e.election_date >= CURDATE() THEN e.election_date END) IS NULL THEN 1 ELSE 0 END ASC,
                MIN(CASE WHEN e.status IN ('upcoming', 'ongoing') AND e.election_date >= CURDATE() THEN e.election_date END) ASC,
                COUNT(DISTINCT ec.election_candidate_id) DESC,
                COUNT(DISTINCT cu.update_id) DESC,
                r.election_year DESC,
                r.race_id ASC
            LIMIT {$limit}
        ";

        $stmt = $this->db->query($sql);

        return array_map(
            fn (array $row): array => $this->mapHomepageWatchedRaceCard($row),
            $stmt->fetchAll()
        );
    }

    public function getHomepageFeaturedRace(): ?array
    {
        $rows = $this->getHomepageMostWatchedRaces(1);

        if (empty($rows)) {
            return null;
        }

        $race = $rows[0];

        $sql = "
            SELECT
                e.election_id,
                e.title,
                e.slug AS election_slug,
                e.election_date,
                e.status,
                e.round_number,
                et.slug AS election_type_slug,
                et.name AS election_type_name,
                COUNT(ec.election_candidate_id) AS candidate_count
            FROM elections e
            INNER JOIN election_types et
                ON et.election_type_id = e.election_type_id
            LEFT JOIN election_candidates ec
                ON ec.election_id = e.election_id
            WHERE e.race_id = :race_id
              AND e.status IN ('upcoming', 'ongoing')
              AND e.election_date >= CURDATE()
            GROUP BY
                e.election_id,
                e.title,
                e.slug,
                e.election_date,
                e.status,
                e.round_number,
                et.slug,
                et.name
            ORDER BY
                e.election_date ASC,
                e.round_number ASC,
                e.election_id ASC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'race_id' => (int)$race['race_id'],
        ]);

        $nextElection = $stmt->fetch();

        return [
            'race_id' => (int)$race['race_id'],
            'office_name' => (string)$race['office_name'],
            'office_slug' => (string)$race['office_slug'],
            'state_name' => (string)$race['state_name'],
            'state_slug' => (string)$race['state_slug'],
            'election_year' => (int)$race['election_year'],
            'district_label' => $race['district_label'],
            'notes_public' => $race['notes_public'] ?? null,
            'race_url' => (string)$race['race_url'],
            'next_election' => $nextElection ? [
                'election_id' => (int)$nextElection['election_id'],
                'title' => (string)$nextElection['title'],
                'election_slug' => (string)$nextElection['election_slug'],
                'election_date' => (string)$nextElection['election_date'],
                'status' => (string)$nextElection['status'],
                'round_number' => (int)$nextElection['round_number'],
                'election_type_slug' => (string)$nextElection['election_type_slug'],
                'election_type_name' => (string)$nextElection['election_type_name'],
                'candidate_count' => (int)$nextElection['candidate_count'],
            ] : null,
        ];
    }

    public function getHomepageBrowseOffices(): array
    {
        $sql = "
            SELECT
                o.slug AS office_slug,
                o.name AS office_name,
                COUNT(DISTINCT r.race_id) AS race_count
            FROM offices o
            LEFT JOIN races r
                ON r.office_id = o.office_id
               AND r.status = 'active'
            WHERE o.slug IN ('president', 'us-senate', 'us-house', 'governor')
            GROUP BY
                o.office_id,
                o.slug,
                o.name
            ORDER BY
                FIELD(o.slug, 'president', 'us-senate', 'us-house', 'governor')
        ";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        $items = [];

        foreach ($rows as $row) {
            $items[] = [
                'office_slug' => (string)$row['office_slug'],
                'office_name' => (string)$row['office_name'],
                'race_count' => (int)$row['race_count'],
                'url' => '/races?office=' . rawurlencode((string)$row['office_slug']),
            ];
        }

        return $items;
    }

    public function getHomepageBrowseStates(): array
    {
        $sql = "
            SELECT
                r.state_code,
                r.state_name,
                r.state_slug,
                COUNT(*) AS race_count
            FROM races r
            WHERE r.status = 'active'
            GROUP BY
                r.state_code,
                r.state_name,
                r.state_slug
            ORDER BY
                r.state_name ASC
        ";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        $items = [];

        foreach ($rows as $row) {
            $items[] = [
                'state_code' => (string)$row['state_code'],
                'state_name' => (string)$row['state_name'],
                'state_slug' => (string)$row['state_slug'],
                'race_count' => (int)$row['race_count'],
                'url' => '/races?state=' . rawurlencode((string)$row['state_slug']),
            ];
        }

        return $items;
    }

    private function mapRaceRow(array $race): array
    {
        $race['race_url'] = $this->buildRaceUrl(
            (string)$race['state_slug'],
            (string)$race['office_slug'],
            (int)$race['election_year'],
            (string)$race['district_type'],
            (int)$race['district_number']
        );

        $race['district_label'] = $this->buildDistrictLabel(
            (string)$race['district_type'],
            (int)$race['district_number']
        );

        return $race;
    }

    private function mapElectionRow(array $election): array
    {
        return $election;
    }

    private function mapHomepageElectionCard(array $row): array
    {
        $districtType = (string)$row['district_type'];
        $districtNumber = (int)$row['district_number'];

        return [
            'election_id' => (int)$row['election_id'],
            'race_id' => (int)$row['race_id'],
            'title' => (string)$row['title'],
            'election_slug' => (string)$row['election_slug'],
            'election_date' => (string)$row['election_date'],
            'status' => (string)$row['status'],
            'round_number' => (int)$row['round_number'],
            'election_type_slug' => (string)$row['election_type_slug'],
            'election_type_name' => (string)$row['election_type_name'],
            'office_name' => (string)$row['office_name'],
            'office_slug' => (string)$row['office_slug'],
            'state_code' => (string)$row['state_code'],
            'state_name' => (string)$row['state_name'],
            'state_slug' => (string)$row['state_slug'],
            'election_year' => (int)$row['election_year'],
            'district_type' => $districtType,
            'district_number' => $districtNumber,
            'district_label' => $this->buildDistrictLabel($districtType, $districtNumber),
            'candidate_count' => (int)$row['candidate_count'],
            'race_url' => $this->buildRaceUrl(
                (string)$row['state_slug'],
                (string)$row['office_slug'],
                (int)$row['election_year'],
                $districtType,
                $districtNumber
            ),
        ];
    }

    private function mapHomepageWatchedRaceCard(array $row): array
    {
        $districtType = (string)$row['district_type'];
        $districtNumber = (int)$row['district_number'];

        return [
            'race_id' => (int)$row['race_id'],
            'office_name' => (string)$row['office_name'],
            'office_slug' => (string)$row['office_slug'],
            'state_code' => (string)$row['state_code'],
            'state_name' => (string)$row['state_name'],
            'state_slug' => (string)$row['state_slug'],
            'election_year' => (int)$row['election_year'],
            'district_type' => $districtType,
            'district_number' => $districtNumber,
            'district_label' => $this->buildDistrictLabel($districtType, $districtNumber),
            'next_election_date' => $row['next_election_date'] ?: null,
            'next_election_type_name' => $row['next_election_type_name'] ?: null,
            'candidate_count' => (int)$row['candidate_count'],
            'public_update_count' => (int)$row['public_update_count'],
            'flag_count' => (int)$row['flag_count'],
            'notes_public' => $row['notes_public'] ?? null,
            'race_url' => $this->buildRaceUrl(
                (string)$row['state_slug'],
                (string)$row['office_slug'],
                (int)$row['election_year'],
                $districtType,
                $districtNumber
            ),
        ];
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
}
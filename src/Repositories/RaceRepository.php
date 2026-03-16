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
              AND (
                    (:district IS NULL AND r.district_type = 'statewide' AND r.district_number = 0)
                 OR (:district IS NOT NULL AND r.district_type = 'congressional_district' AND r.district_number = :district_exact)
              )
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':state_slug', trim($stateSlug));
        $stmt->bindValue(':office_slug', trim($officeSlug));
        $stmt->bindValue(':election_year', $year, PDO::PARAM_INT);

        if ($district === null) {
            $stmt->bindValue(':district', null, PDO::PARAM_NULL);
            $stmt->bindValue(':district_exact', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':district', $district, PDO::PARAM_INT);
            $stmt->bindValue(':district_exact', $district, PDO::PARAM_INT);
        }

        $stmt->execute();

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function getElectionsForRace(int $raceId): array
    {
        $sql = "
            SELECT
                e.*,
                et.slug AS election_type_slug,
                et.name AS election_type_name
            FROM elections e
            INNER JOIN election_types et
                ON et.election_type_id = e.election_type_id
            WHERE e.race_id = :race_id
            ORDER BY
                e.election_date ASC,
                e.round_number ASC
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
                c.party_code,
                c.party_name,
                c.short_bio,
                c.summary_public,
                c.image_url,
                c.score_total,
                c.green_flag_count,
                c.red_flag_count
            FROM election_candidates ec
            INNER JOIN candidates c
                ON c.candidate_id = ec.candidate_id
            WHERE ec.election_id = :election_id
              AND c.status = 'active'
            ORDER BY
                ec.sort_order ASC,
                c.score_total DESC,
                c.full_name ASC
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
            'race' => $race,
            'elections' => $elections,
        ];
    }
}
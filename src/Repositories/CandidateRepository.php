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
            SELECT
                c.*
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
                COALESCE(cf.weight_override, f.default_weight) AS effective_weight
            FROM candidate_flags cf
            INNER JOIN flags f
                ON f.flag_id = cf.flag_id
            WHERE cf.candidate_id = :candidate_id
              AND cf.is_active = 1
              AND f.is_active = 1
            ORDER BY
                f.flag_color ASC,
                f.sort_order ASC,
                f.name ASC
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
                o.name AS office_name,
                o.slug AS office_slug
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
            WHERE ecf.candidate_id = :candidate_id
              AND ecf.is_active = 1
              AND f.is_active = 1
            ORDER BY
                e.election_date DESC,
                e.round_number DESC,
                f.flag_color ASC,
                f.sort_order ASC,
                f.name ASC
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
                o.name ASC
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

        $candidateFlags = $this->getCandidateFlags($candidateId);
        $electionSpecificFlags = $this->getElectionSpecificFlags($candidateId);
        $history = $this->getElectionHistory($candidateId);

        return [
            'candidate' => $candidate,
            'candidate_flags' => $candidateFlags,
            'election_flags' => $electionSpecificFlags,
            'history' => $history,
        ];
    }
}
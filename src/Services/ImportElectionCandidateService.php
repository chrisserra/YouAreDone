<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Office;
use App\Models\ElectionType;
use App\Models\Race;
use App\Models\Election;
use App\Models\Candidate;
use App\Models\ElectionCandidate;
use PDO;
use Throwable;
use InvalidArgumentException;

final class ImportElectionCandidateService
{
    private PDO $db;

    private Office $officeModel;
    private ElectionType $electionTypeModel;
    private Race $raceModel;
    private Election $electionModel;
    private Candidate $candidateModel;
    private ElectionCandidate $electionCandidateModel;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();

        $this->officeModel = new Office($this->db);
        $this->electionTypeModel = new ElectionType($this->db);
        $this->raceModel = new Race($this->db);
        $this->electionModel = new Election($this->db);
        $this->candidateModel = new Candidate($this->db);
        $this->electionCandidateModel = new ElectionCandidate($this->db);
    }

    public function import(array $data): array
    {
        $officeSlug = trim((string)($data['office_slug'] ?? ''));
        $electionTypeSlug = trim((string)($data['election_type_slug'] ?? ''));

        if ($officeSlug === '' || $electionTypeSlug === '') {
            throw new InvalidArgumentException('office_slug and election_type_slug are required.');
        }

        try {
            $this->db->beginTransaction();

            /* ------------------------------
               Resolve office
            ------------------------------ */

            $office = $this->officeModel->requireBySlug($officeSlug);

            /* ------------------------------
               Resolve election type
            ------------------------------ */

            $electionType = $this->electionTypeModel->requireBySlug($electionTypeSlug);

            /* ------------------------------
               Create / find race
            ------------------------------ */

            $raceId = $this->raceModel->create([
                'office_id' => $office['office_id'],
                'office_slug' => $office['slug'],
                'election_year' => $data['election_year'],
                'state_code' => $data['state_code'],
                'state_name' => $data['state_name'],
                'state_slug' => $data['state_slug'],
                'district_type' => $data['district_type'] ?? 'statewide',
                'district_number' => $data['district_number'] ?? 0,
                'district_label' => $data['district_label'] ?? null,
                'seat_label' => $data['seat_label'] ?? null,
                'is_special' => $data['is_special'] ?? 0,
                'notes_public' => $data['race_notes'] ?? null
            ]);

            /* ------------------------------
               Fetch race slug
            ------------------------------ */

            $raceSlug = $this->raceModel->getSlugById($raceId);

            if (!$raceSlug) {
                throw new InvalidArgumentException('Race could not be loaded.');
            }

            /* ------------------------------
               Create / find election
            ------------------------------ */

            $electionId = $this->electionModel->create([
                'race_id' => $raceId,
                'race_slug' => $raceSlug,
                'election_type_id' => $electionType['election_type_id'],
                'election_type_slug' => $electionType['slug'],
                'election_date' => $data['election_date'],
                'round_number' => $data['round_number'] ?? 1,
                'title' => $data['election_title'],
                'status' => $data['status'] ?? 'upcoming',
                'filing_deadline' => $data['filing_deadline'] ?? null,
                'early_voting_start' => $data['early_voting_start'] ?? null,
                'early_voting_end' => $data['early_voting_end'] ?? null,
                'certification_date' => $data['certification_date'] ?? null,
                'notes_public' => $data['election_notes'] ?? null
            ]);

            /* ------------------------------
               Create / find candidate
            ------------------------------ */

            $candidateId = $this->candidateModel->create([
                'full_name' => $data['candidate_name'],
                'first_name' => $data['candidate_first_name'] ?? null,
                'middle_name' => $data['candidate_middle_name'] ?? null,
                'last_name' => $data['candidate_last_name'] ?? null,
                'suffix' => $data['candidate_suffix'] ?? null,
                'preferred_name' => $data['candidate_preferred_name'] ?? null,
                'party_code' => $data['party_code'] ?? null,
                'party_name' => $data['party_name'] ?? null,
                'website_url' => $data['website_url'] ?? null,
                'ballotpedia_url' => $data['ballotpedia_url'] ?? null,
                'wikipedia_url' => $data['wikipedia_url'] ?? null,
                'x_url' => $data['x_url'] ?? null,
                'instagram_url' => $data['instagram_url'] ?? null,
                'facebook_url' => $data['facebook_url'] ?? null,
                'youtube_url' => $data['youtube_url'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'short_bio' => $data['short_bio'] ?? null,
                'summary_public' => $data['summary_public'] ?? null
            ]);

            /* ------------------------------
               Link candidate to election
            ------------------------------ */

            $electionCandidateId = $this->electionCandidateModel->create([
                'election_id' => $electionId,
                'candidate_id' => $candidateId,
                'ballot_name' => $data['ballot_name'] ?? null,
                'party_code' => $data['party_code'] ?? null,
                'filing_status' => $data['filing_status'] ?? 'unknown',
                'ballot_status' => $data['ballot_status'] ?? 'unknown',
                'result_status' => $data['result_status'] ?? 'pending',
                'is_incumbent' => $data['is_incumbent'] ?? 0,
                'is_major_candidate' => $data['is_major_candidate'] ?? 0,
                'sort_order' => $data['sort_order'] ?? 0
            ]);

            $this->db->commit();

            return [
                'race_id' => $raceId,
                'election_id' => $electionId,
                'candidate_id' => $candidateId,
                'election_candidate_id' => $electionCandidateId
            ];
        } catch (Throwable $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}
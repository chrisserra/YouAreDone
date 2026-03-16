<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Candidate;
use App\Models\ElectionCandidate;
use PDO;

final class UpsertCandidateService
{
    private Candidate $candidates;
    private ElectionCandidate $electionCandidates;

    public function __construct(?PDO $db = null)
    {
        $this->candidates = new Candidate($db);
        $this->electionCandidates = new ElectionCandidate($db);
    }

    public function handle(array $candidateData, ?array $electionLinkData = null): array
    {
        $candidateId = $this->candidates->create($candidateData);
        $linkId = null;

        if ($electionLinkData !== null) {
            $electionLinkData['candidate_id'] = $candidateId;
            $linkId = $this->electionCandidates->create($electionLinkData);
        }

        return [
            'candidate_id' => $candidateId,
            'election_candidate_id' => $linkId,
        ];
    }
}
<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

final class ElectionCandidate extends Model
{
    protected string $table = 'election_candidates';

    public function findOne(int $electionId, int $candidateId): ?array
    {
        $sql = "
            SELECT *
            FROM election_candidates
            WHERE election_id = :election_id
              AND candidate_id = :candidate_id
            LIMIT 1
        ";

        return $this->fetchOne($sql, [
            'election_id'  => $electionId,
            'candidate_id' => $candidateId,
        ]);
    }

    public function create(array $data): int
    {
        $electionId      = (int)$data['election_id'];
        $candidateId     = (int)$data['candidate_id'];
        $ballotPartyCode = $data['ballot_party_code'] ?? null;
        $isIncumbent     = !empty($data['is_incumbent']) ? 1 : 0;
        $filingStatus    = $data['filing_status'] ?? null;
        $resultStatus    = $data['result_status'] ?? null;
        $displayOrder    = isset($data['display_order']) ? (int)$data['display_order'] : 0;

        if ($electionId <= 0 || $candidateId <= 0) {
            throw new InvalidArgumentException('election_id and candidate_id are required.');
        }

        $existing = $this->findOne($electionId, $candidateId);
        if ($existing) {
            return (int)$existing['id'];
        }

        $sql = "
            INSERT INTO election_candidates (
                election_id,
                candidate_id,
                ballot_party_code,
                is_incumbent,
                filing_status,
                result_status,
                display_order,
                created_at,
                updated_at
            ) VALUES (
                :election_id,
                :candidate_id,
                :ballot_party_code,
                :is_incumbent,
                :filing_status,
                :result_status,
                :display_order,
                NOW(),
                NOW()
            )
        ";

        return $this->insert($sql, [
            'election_id'       => $electionId,
            'candidate_id'      => $candidateId,
            'ballot_party_code' => $ballotPartyCode,
            'is_incumbent'      => $isIncumbent,
            'filing_status'     => $filingStatus,
            'result_status'     => $resultStatus,
            'display_order'     => $displayOrder,
        ]);
    }
}
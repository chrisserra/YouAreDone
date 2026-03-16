<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

final class ElectionCandidateFlag extends Model
{
    public function findOne(int $electionId, int $candidateId, int $flagId): ?array
    {
        $sql = "
            SELECT *
            FROM election_candidate_flags
            WHERE election_id = :election_id
              AND candidate_id = :candidate_id
              AND flag_id = :flag_id
            LIMIT 1
        ";

        return $this->fetchOne($sql, [
            'election_id' => $electionId,
            'candidate_id' => $candidateId,
            'flag_id' => $flagId,
        ]);
    }

    public function create(array $data): int
    {
        $electionId = (int)($data['election_id'] ?? 0);
        $candidateId = (int)($data['candidate_id'] ?? 0);
        $flagId = (int)($data['flag_id'] ?? 0);
        $sourceId = $data['source_id'] ?? null;
        $weightOverride = $data['weight_override'] ?? null;
        $note = $data['note'] ?? null;
        $isActive = !empty($data['is_active']) ? 1 : 0;

        if ($electionId <= 0 || $candidateId <= 0 || $flagId <= 0) {
            throw new InvalidArgumentException('election_id, candidate_id and flag_id are required.');
        }

        $existing = $this->findOne($electionId, $candidateId, $flagId);
        if ($existing) {
            return (int)$existing['election_candidate_flag_id'];
        }

        $sql = "
            INSERT INTO election_candidate_flags
            (
                election_id,
                candidate_id,
                flag_id,
                source_id,
                weight_override,
                note,
                is_active,
                created_at,
                updated_at
            )
            VALUES
            (
                :election_id,
                :candidate_id,
                :flag_id,
                :source_id,
                :weight_override,
                :note,
                :is_active,
                NOW(),
                NOW()
            )
        ";

        return $this->insert($sql, [
            'election_id' => $electionId,
            'candidate_id' => $candidateId,
            'flag_id' => $flagId,
            'source_id' => $sourceId,
            'weight_override' => $weightOverride,
            'note' => $note,
            'is_active' => $isActive,
        ]);
    }
}
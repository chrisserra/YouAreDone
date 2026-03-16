<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Services\CandidateScoreService;
use InvalidArgumentException;
use Throwable;

final class CandidateFlag extends Model
{
    public function findOne(int $candidateId, int $flagId): ?array
    {
        $sql = "
            SELECT *
            FROM candidate_flags
            WHERE candidate_id = :candidate_id
              AND flag_id = :flag_id
            LIMIT 1
        ";

        return $this->fetchOne($sql, [
            'candidate_id' => $candidateId,
            'flag_id' => $flagId,
        ]);
    }

    public function create(array $data): int
    {
        $candidateId = (int)($data['candidate_id'] ?? 0);
        $flagId = (int)($data['flag_id'] ?? 0);
        $sourceId = $data['source_id'] ?? null;
        $weightOverride = $data['weight_override'] ?? null;
        $note = $data['note'] ?? null;
        $isActive = array_key_exists('is_active', $data) ? (int)(bool)$data['is_active'] : 1;

        if ($candidateId <= 0 || $flagId <= 0) {
            throw new InvalidArgumentException('candidate_id and flag_id are required.');
        }

        $existing = $this->findOne($candidateId, $flagId);
        if ($existing) {
            return (int)$existing['candidate_flag_id'];
        }

        try {
            $this->db->beginTransaction();

            $sql = "
                INSERT INTO candidate_flags
                (
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

            $newId = $this->insert($sql, [
                'candidate_id' => $candidateId,
                'flag_id' => $flagId,
                'source_id' => $sourceId,
                'weight_override' => $weightOverride,
                'note' => $note,
                'is_active' => $isActive,
            ]);

            $scoreService = new CandidateScoreService($this->db);
            $scoreService->recalculateCandidate($candidateId);

            $this->db->commit();

            return $newId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function deactivate(int $candidateFlagId): bool
    {
        $row = $this->fetchOne(
            "SELECT candidate_flag_id, candidate_id FROM candidate_flags WHERE candidate_flag_id = :id LIMIT 1",
            ['id' => $candidateFlagId]
        );

        if (!$row) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $updated = $this->execute(
                "
                UPDATE candidate_flags
                SET is_active = 0,
                    updated_at = NOW()
                WHERE candidate_flag_id = :id
                ",
                ['id' => $candidateFlagId]
            );

            $scoreService = new CandidateScoreService($this->db);
            $scoreService->recalculateCandidate((int)$row['candidate_id']);

            $this->db->commit();

            return $updated;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}
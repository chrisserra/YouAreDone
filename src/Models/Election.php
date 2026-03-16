<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

final class Election extends Model
{
    protected string $table = 'elections';

    public function findOne(int $raceId, int $electionTypeId, string $electionDate): ?array
    {
        $sql = "
            SELECT *
            FROM elections
            WHERE race_id = :race_id
              AND election_type_id = :election_type_id
              AND election_date = :election_date
            LIMIT 1
        ";

        return $this->fetchOne($sql, [
            'race_id'          => $raceId,
            'election_type_id' => $electionTypeId,
            'election_date'    => $electionDate,
        ]);
    }

    public function create(array $data): int
    {
        $raceId         = (int)$data['race_id'];
        $electionTypeId = (int)$data['election_type_id'];
        $electionDate   = trim((string)$data['election_date']);
        $notes          = $data['notes'] ?? null;

        if ($raceId <= 0 || $electionTypeId <= 0 || $electionDate === '') {
            throw new InvalidArgumentException('Missing required election fields.');
        }

        $existing = $this->findOne($raceId, $electionTypeId, $electionDate);
        if ($existing) {
            return (int)$existing['id'];
        }

        $sql = "
            INSERT INTO elections (
                race_id,
                election_type_id,
                election_date,
                is_finalized,
                notes,
                created_at,
                updated_at
            ) VALUES (
                :race_id,
                :election_type_id,
                :election_date,
                0,
                :notes,
                NOW(),
                NOW()
            )
        ";

        return $this->insert($sql, [
            'race_id'          => $raceId,
            'election_type_id' => $electionTypeId,
            'election_date'    => $electionDate,
            'notes'            => $notes,
        ]);
    }
}
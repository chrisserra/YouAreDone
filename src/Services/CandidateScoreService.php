<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;

final class CandidateScoreService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function recalculateCandidate(int $candidateId): void
    {
        if ($candidateId <= 0) {
            throw new RuntimeException('Invalid candidate ID.');
        }

        $sql = "
            UPDATE candidates c
            LEFT JOIN (
                SELECT
                    cf.candidate_id,
                    COALESCE(SUM(
                        CASE
                            WHEN f.flag_color = 'green' THEN COALESCE(cf.weight_override, f.default_weight)
                            WHEN f.flag_color = 'red' THEN -1 * COALESCE(cf.weight_override, f.default_weight)
                            ELSE 0
                        END
                    ), 0) AS score_total,
                    COALESCE(SUM(
                        CASE
                            WHEN f.flag_color = 'green' THEN 1
                            ELSE 0
                        END
                    ), 0) AS green_flag_count,
                    COALESCE(SUM(
                        CASE
                            WHEN f.flag_color = 'red' THEN 1
                            ELSE 0
                        END
                    ), 0) AS red_flag_count
                FROM candidate_flags cf
                INNER JOIN flags f
                    ON f.flag_id = cf.flag_id
                WHERE cf.candidate_id = :candidate_id
                  AND cf.is_active = 1
                  AND f.is_active = 1
                GROUP BY cf.candidate_id
            ) x
                ON x.candidate_id = c.candidate_id
            SET
                c.score_total = COALESCE(x.score_total, 0),
                c.green_flag_count = COALESCE(x.green_flag_count, 0),
                c.red_flag_count = COALESCE(x.red_flag_count, 0),
                c.updated_at = NOW()
            WHERE c.candidate_id = :candidate_id_2
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'candidate_id' => $candidateId,
            'candidate_id_2' => $candidateId,
        ]);
    }

    public function recalculateAll(): void
    {
        $sql = "
            UPDATE candidates c
            LEFT JOIN (
                SELECT
                    cf.candidate_id,
                    COALESCE(SUM(
                        CASE
                            WHEN f.flag_color = 'green' THEN COALESCE(cf.weight_override, f.default_weight)
                            WHEN f.flag_color = 'red' THEN -1 * COALESCE(cf.weight_override, f.default_weight)
                            ELSE 0
                        END
                    ), 0) AS score_total,
                    COALESCE(SUM(
                        CASE
                            WHEN f.flag_color = 'green' THEN 1
                            ELSE 0
                        END
                    ), 0) AS green_flag_count,
                    COALESCE(SUM(
                        CASE
                            WHEN f.flag_color = 'red' THEN 1
                            ELSE 0
                        END
                    ), 0) AS red_flag_count
                FROM candidate_flags cf
                INNER JOIN flags f
                    ON f.flag_id = cf.flag_id
                WHERE cf.is_active = 1
                  AND f.is_active = 1
                GROUP BY cf.candidate_id
            ) x
                ON x.candidate_id = c.candidate_id
            SET
                c.score_total = COALESCE(x.score_total, 0),
                c.green_flag_count = COALESCE(x.green_flag_count, 0),
                c.red_flag_count = COALESCE(x.red_flag_count, 0),
                c.updated_at = NOW()
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Election;
use PDO;

final class UpsertElectionService
{
    private Election $elections;

    public function __construct(?PDO $db = null)
    {
        $this->elections = new Election($db);
    }

    public function handle(array $data): int
    {
        return $this->elections->create($data);
    }
}
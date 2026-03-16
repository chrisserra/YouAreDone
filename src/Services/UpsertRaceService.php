<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Race;
use PDO;

final class UpsertRaceService
{
    private Race $races;

    public function __construct(?PDO $db = null)
    {
        $this->races = new Race($db);
    }

    public function handle(array $data): int
    {
        return $this->races->create($data);
    }
}
<?php

declare(strict_types=1);

/** @var array $methodology */

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$data = is_array($methodology ?? null) ? $methodology : [];
$trackedOffices = is_array($data['trackedOffices'] ?? null) ? $data['trackedOffices'] : [];
$trackedElectionTypes = is_array($data['trackedElectionTypes'] ?? null) ? $data['trackedElectionTypes'] : [];
$sourcePolicy = (string)($data['sourcePolicy'] ?? '');

$formatList = static function (array $items): string {
    $items = array_values(array_filter(array_map(
        static fn ($value) => trim((string)$value),
        $items
    )));

    return implode(', ', $items);
};

$officesText = $formatList($trackedOffices);
$electionTypesText = $formatList($trackedElectionTypes);
?>

<section class="dashboard-section methodology">
    <div class="card methodology-card">
        <div class="methodology-card__header">
            <h2 class="dashboard-section__title">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                Methodology
            </h2>
        </div>

        <div class="methodology-card__content">
            <?php if ($officesText !== ''): ?>
                <p class="methodology-card__text">
                    <strong>Tracked offices:</strong>
                    <?= h($officesText) ?>.
                </p>
            <?php endif; ?>

            <?php if ($electionTypesText !== ''): ?>
                <p class="methodology-card__text">
                    <strong>Tracked election types:</strong>
                    <?= h($electionTypesText) ?>.
                </p>
            <?php endif; ?>

            <?php if ($sourcePolicy !== ''): ?>
                <p class="methodology-card__text">
                    <strong>Documentation standard:</strong>
                    <?= h($sourcePolicy) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>
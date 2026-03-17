<?php

declare(strict_types=1);

/** @var array $browseStates */

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$items = is_array($browseStates ?? null) ? $browseStates : [];

if (empty($items)) {
    return;
}
?>

<section class="dashboard-section browse-states">
    <div class="dashboard-section__header">
        <h2 class="dashboard-section__title">
            <i class="fa-solid fa-map" aria-hidden="true"></i>
            Browse by State
        </h2>
    </div>

    <div class="browse-states__grid">
        <?php foreach ($items as $item): ?>
            <?php
            $stateName = (string)($item['state_name'] ?? '');
            $stateCode = (string)($item['state_code'] ?? '');
            $url = (string)($item['url'] ?? '#');
            $raceCount = (int)($item['race_count'] ?? 0);
            ?>
            <a href="<?= h($url) ?>" class="card browse-state-card">
                <div class="browse-state-card__code">
                    <?= h($stateCode) ?>
                </div>

                <div class="browse-state-card__content">
                    <div class="browse-state-card__name">
                        <?= h($stateName) ?>
                    </div>

                    <?php if ($raceCount > 0): ?>
                        <div class="browse-state-card__count">
                            <?= number_format($raceCount) ?> race<?= $raceCount === 1 ? '' : 's' ?>
                        </div>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
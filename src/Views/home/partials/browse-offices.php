<?php

declare(strict_types=1);

/** @var array $browseOffices */

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$items = is_array($browseOffices ?? null) ? $browseOffices : [];

?>

<section class="dashboard-section browse-offices">
    <div class="dashboard-section__header">
        <h2 class="dashboard-section__title">
            <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
            Browse by Office
        </h2>
    </div>

    <div class="browse-offices__grid">
        <?php foreach ($items as $item): ?>
            <?php
            $officeName = (string)($item['office_name'] ?? '');
            $officeSlug = (string)($item['office_slug'] ?? '');
            $url = (string)($item['url'] ?? '#');
            $raceCount = (int)($item['race_count'] ?? 0);
            ?>
            <a href="<?= h($url) ?>" class="card browse-office-card">
                <div class="browse-office-card__icon">
                    <?php
                    // Simple icon mapping (kept local to view)
                    $icon = match ($officeSlug) {
                        'president' => 'fa-flag-usa',
                        'us-senate' => 'fa-landmark',
                        'us-house' => 'fa-building',
                        'governor' => 'fa-map',
                        default => 'fa-building-columns',
                    };
                    ?>
                    <i class="fa-solid <?= h($icon) ?>" aria-hidden="true"></i>
                </div>

                <div class="browse-office-card__content">
                    <div class="browse-office-card__name">
                        <?= h($officeName) ?>
                    </div>

                    <?php if ($raceCount > 0): ?>
                        <div class="browse-office-card__count">
                            <?= number_format($raceCount) ?> race<?= $raceCount === 1 ? '' : 's' ?>
                        </div>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
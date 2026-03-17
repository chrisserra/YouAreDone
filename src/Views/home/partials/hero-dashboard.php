<?php

declare(strict_types=1);

/** @var array $hero */

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$hero = $hero ?? [];
$title = (string)($hero['title'] ?? 'Election Watch Dashboard');
$subtitle = (string)($hero['subtitle'] ?? '');
$stats = is_array($hero['stats'] ?? null) ? $hero['stats'] : [];
?>

<section class="dashboard-section dashboard-hero">
    <div class="dashboard-hero__main card">
        <div class="dashboard-hero__eyebrow">
            <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
            Election Watch
        </div>

        <h1 class="dashboard-hero__title"><?= h($title) ?></h1>

        <?php if ($subtitle !== ''): ?>
            <p class="dashboard-hero__subtitle"><?= h($subtitle) ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($stats)): ?>
        <div class="dashboard-hero__stats">
            <?php foreach ($stats as $stat): ?>
                <?php
                $label = (string)($stat['label'] ?? '');
                $value = (int)($stat['value'] ?? 0);
                $icon = trim((string)($stat['icon'] ?? 'fa-circle-info'));
                ?>
                <article class="dashboard-stat card">
                    <div class="dashboard-stat__icon" aria-hidden="true">
                        <i class="fa-solid <?= h($icon) ?>"></i>
                    </div>

                    <div class="dashboard-stat__content">
                        <div class="dashboard-stat__value"><?= number_format($value) ?></div>
                        <div class="dashboard-stat__label"><?= h($label) ?></div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
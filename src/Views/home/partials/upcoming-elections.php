<?php

declare(strict_types=1);

/** @var array $upcomingEvents */

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$items = is_array($upcomingEvents ?? null) ? $upcomingEvents : [];

if (empty($items)) {
    return;
}
?>

<section class="dashboard-section upcoming-elections">
    <div class="dashboard-section__header">
        <h2 class="dashboard-section__title">
            <i class="fa-solid fa-clock" aria-hidden="true"></i>
            Upcoming Elections
        </h2>
    </div>

    <div class="upcoming-elections__grid">
        <?php foreach ($items as $item): ?>
            <?php
            $title = (string)($item['title'] ?? '');
            $eventDate = (string)($item['event_date'] ?? '');
            $contestCount = (int)($item['contest_count'] ?? 0);
            $candidateCount = (int)($item['candidate_count'] ?? 0);
            ?>
            <article class="card upcoming-election-card">
                <div class="upcoming-election-card__date">
                    <?= h(election_date($eventDate)) ?>
                </div>

                <h3 class="upcoming-election-card__title">
                    <?= h($title) ?>
                </h3>

                <div class="upcoming-election-card__meta">
                    <?php if ($contestCount > 0): ?>
                        <span class="upcoming-election-card__meta-item">
                            <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                            <?= number_format($contestCount) ?> contest<?= $contestCount === 1 ? '' : 's' ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($candidateCount > 0): ?>
                        <span class="upcoming-election-card__meta-item">
                            <i class="fa-solid fa-users" aria-hidden="true"></i>
                            <?= number_format($candidateCount) ?> candidate<?= $candidateCount === 1 ? '' : 's' ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="upcoming-election-card__actions">
                    <span class="btn btn-secondary is-disabled" aria-disabled="true">
                        Election details coming soon
                    </span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
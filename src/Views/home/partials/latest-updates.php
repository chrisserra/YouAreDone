<?php

declare(strict_types=1);

/** @var array $latestUpdates */

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('format_date_short')) {
    function format_date_short(?string $date): string
    {
        if (!$date) {
            return '';
        }

        $ts = strtotime($date);
        if (!$ts) {
            return '';
        }

        return date('M j, Y', $ts);
    }
}

$items = is_array($latestUpdates ?? null) ? $latestUpdates : [];

if (empty($items)) {
    return;
}
?>

<section class="dashboard-section latest-updates">
    <div class="dashboard-section__header">
        <h2 class="dashboard-section__title">
            <i class="fa-solid fa-newspaper" aria-hidden="true"></i>
            Latest Candidate Updates
        </h2>
    </div>

    <div class="latest-updates__list">
        <?php foreach ($items as $item): ?>
            <?php
            $headline = (string)($item['headline'] ?? '');
            $summary = (string)($item['summary'] ?? '');
            $sortDate = (string)($item['sort_date'] ?? '');
            $candidateName = (string)($item['candidate_name'] ?? '');
            $candidateUrl = (string)($item['candidate_url'] ?? '#');
            $raceLabel = (string)($item['race_label'] ?? '');
            $raceUrl = (string)($item['race_url'] ?? '');
            $sourceName = (string)($item['source_name'] ?? '');
            $sourceUrl = (string)($item['source_url'] ?? '');
            ?>
            <article class="card latest-update-card">
                <div class="latest-update-card__date">
                    <?= h(format_date_short($sortDate)) ?>
                </div>

                <div class="latest-update-card__content">
                    <?php if ($headline !== ''): ?>
                        <h3 class="latest-update-card__headline"><?= h($headline) ?></h3>
                    <?php endif; ?>

                    <div class="latest-update-card__meta">
                        <?php if ($candidateName !== ''): ?>
                            <span class="latest-update-card__meta-item">
                                <i class="fa-solid fa-user" aria-hidden="true"></i>
                                <a href="<?= h($candidateUrl) ?>" class="latest-update-card__link">
                                    <?= h($candidateName) ?>
                                </a>
                            </span>
                        <?php endif; ?>

                        <?php if ($raceLabel !== ''): ?>
                            <span class="latest-update-card__meta-item">
                                <i class="fa-solid fa-landmark" aria-hidden="true"></i>
                                <?php if ($raceUrl !== ''): ?>
                                    <a href="<?= h($raceUrl) ?>" class="latest-update-card__link">
                                        <?= h($raceLabel) ?>
                                    </a>
                                <?php else: ?>
                                    <?= h($raceLabel) ?>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($summary !== ''): ?>
                        <p class="latest-update-card__summary"><?= h($summary) ?></p>
                    <?php endif; ?>

                    <?php if ($sourceName !== ''): ?>
                        <div class="latest-update-card__source">
                            <span class="latest-update-card__source-label">Source:</span>
                            <?php if ($sourceUrl !== ''): ?>
                                <a
                                    href="<?= h($sourceUrl) ?>"
                                    class="latest-update-card__link"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <?= h($sourceName) ?>
                                </a>
                            <?php else: ?>
                                <?= h($sourceName) ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
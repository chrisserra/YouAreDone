<?php

declare(strict_types=1);

/** @var array $upcomingElections */

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

$items = is_array($upcomingElections ?? null) ? $upcomingElections : [];

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
            $stateName = (string)($item['state_name'] ?? '');
            $officeName = (string)($item['office_name'] ?? '');
            $districtLabel = (string)($item['district_label'] ?? '');
            $electionType = (string)($item['election_type_name'] ?? '');
            $electionDate = (string)($item['election_date'] ?? '');
            $candidateCount = (int)($item['candidate_count'] ?? 0);
            $raceUrl = (string)($item['race_url'] ?? '#');
            ?>
            <article class="card upcoming-election-card">
                <div class="upcoming-election-card__date">
                    <?= h(format_date_short($electionDate)) ?>
                </div>

                <h3 class="upcoming-election-card__title">
                    <a href="<?= h($raceUrl) ?>" class="upcoming-election-card__title-link">
                        <?= h($stateName) ?> <?= h($officeName) ?>
                        <?php if ($districtLabel !== ''): ?>
                            • <?= h($districtLabel) ?>
                        <?php endif; ?>
                    </a>
                </h3>

                <div class="upcoming-election-card__meta">
                    <span class="upcoming-election-card__type"><?= h($electionType) ?></span>

                    <?php if ($candidateCount > 0): ?>
                        <span class="upcoming-election-card__count">
                            <?= number_format($candidateCount) ?> candidate<?= $candidateCount === 1 ? '' : 's' ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="upcoming-election-card__actions">
                    <a href="<?= h($raceUrl) ?>" class="btn btn-secondary">
                        View Race
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
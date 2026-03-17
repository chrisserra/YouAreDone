<?php

declare(strict_types=1);

/** @var array $mostWatchedRaces */

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

$items = is_array($mostWatchedRaces ?? null) ? $mostWatchedRaces : [];

if (empty($items)) {
    return;
}
?>

<section class="dashboard-section watched-races">
    <div class="dashboard-section__header">
        <h2 class="dashboard-section__title">
            <i class="fa-solid fa-eye" aria-hidden="true"></i>
            Most Watched Races
        </h2>
    </div>

    <div class="watched-races__grid">
        <?php foreach ($items as $item): ?>
            <?php
            $stateName = (string)($item['state_name'] ?? '');
            $officeName = (string)($item['office_name'] ?? '');
            $districtLabel = (string)($item['district_label'] ?? '');
            $year = (int)($item['election_year'] ?? 0);
            $nextElectionDate = (string)($item['next_election_date'] ?? '');
            $nextElectionType = (string)($item['next_election_type_name'] ?? '');
            $candidateCount = (int)($item['candidate_count'] ?? 0);
            $updateCount = (int)($item['public_update_count'] ?? 0);
            $flagCount = (int)($item['flag_count'] ?? 0);
            $raceUrl = (string)($item['race_url'] ?? '#');
            ?>
            <article class="card watched-race-card">
                <div class="watched-race-card__eyebrow">
                    <i class="fa-solid fa-landmark" aria-hidden="true"></i>
                    Race Watch
                </div>

                <h3 class="watched-race-card__title">
                    <a href="<?= h($raceUrl) ?>" class="watched-race-card__title-link">
                        <?= h($stateName) ?> <?= h($officeName) ?>
                        <?php if ($districtLabel !== ''): ?>
                            • <?= h($districtLabel) ?>
                        <?php endif; ?>
                        <?php if ($year > 0): ?>
                            <?= ' ' . h((string)$year) ?>
                        <?php endif; ?>
                    </a>
                </h3>

                <div class="watched-race-card__meta">
                    <?php if ($nextElectionType !== ''): ?>
                        <div class="watched-race-card__meta-item">
                            <span class="watched-race-card__meta-label">Next election</span>
                            <span class="watched-race-card__meta-value">
                                <?= h($nextElectionType) ?>
                                <?php if ($nextElectionDate !== ''): ?>
                                    • <?= h(format_date_short($nextElectionDate)) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php elseif ($nextElectionDate !== ''): ?>
                        <div class="watched-race-card__meta-item">
                            <span class="watched-race-card__meta-label">Next election</span>
                            <span class="watched-race-card__meta-value"><?= h(format_date_short($nextElectionDate)) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="watched-race-card__stats">
                        <span class="watched-race-card__stat">
                            <i class="fa-solid fa-users" aria-hidden="true"></i>
                            <?= number_format($candidateCount) ?> candidate<?= $candidateCount === 1 ? '' : 's' ?>
                        </span>

                        <span class="watched-race-card__stat">
                            <i class="fa-solid fa-newspaper" aria-hidden="true"></i>
                            <?= number_format($updateCount) ?> update<?= $updateCount === 1 ? '' : 's' ?>
                        </span>

                        <span class="watched-race-card__stat">
                            <i class="fa-solid fa-flag" aria-hidden="true"></i>
                            <?= number_format($flagCount) ?> flag<?= $flagCount === 1 ? '' : 's' ?>
                        </span>
                    </div>
                </div>

                <div class="watched-race-card__actions">
                    <a href="<?= h($raceUrl) ?>" class="btn btn-secondary">
                        View Race
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
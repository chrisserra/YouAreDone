<?php

declare(strict_types=1);

/** @var array $featuredRace */

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date): string
    {
        if (!$date) {
            return '';
        }

        $ts = strtotime($date);
        if (!$ts) {
            return '';
        }

        return date('F j, Y', $ts);
    }
}

$r = $featuredRace ?? null;

if (!$r) {
    return;
}

$stateName = (string)($r['state_name'] ?? '');
$officeName = (string)($r['office_name'] ?? '');
$districtLabel = (string)($r['district_label'] ?? '');
$year = (int)($r['election_year'] ?? 0);
$raceUrl = (string)($r['race_url'] ?? '#');
$notes = (string)($r['notes_public'] ?? '');

$nextElection = $r['next_election'] ?? null;
$candidates = is_array($r['candidate_preview'] ?? null) ? $r['candidate_preview'] : [];

?>

<section class="dashboard-section featured-race">
    <div class="card featured-race__card">
        <div class="featured-race__header">
            <div class="featured-race__eyebrow">
                <i class="fa-solid fa-star" aria-hidden="true"></i>
                Featured Race
            </div>

            <h2 class="featured-race__title">
                <?= h($stateName) ?> <?= h($officeName) ?>
                <?php if ($districtLabel !== ''): ?>
                    • <?= h($districtLabel) ?>
                <?php endif; ?>
                <?php if ($year > 0): ?>
                    <?= ' ' . h((string)$year) ?>
                <?php endif; ?>
            </h2>
        </div>

        <?php if ($nextElection): ?>
            <div class="featured-race__next-election">
                <div class="featured-race__next-label">
                    <i class="fa-solid fa-calendar-day" aria-hidden="true"></i>
                    Next Election
                </div>

                <div class="featured-race__next-meta">
                    <span class="featured-race__next-type">
                        <?= h((string)($nextElection['election_type_name'] ?? '')) ?>
                    </span>

                    <?php if (!empty($nextElection['election_date'])): ?>
                        <span class="featured-race__next-date">
                            <?= h(format_date((string)$nextElection['election_date'])) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($nextElection['candidate_count'])): ?>
                    <div class="featured-race__next-count">
                        <?= number_format((int)$nextElection['candidate_count']) ?>
                        candidate<?= ((int)$nextElection['candidate_count'] === 1 ? '' : 's') ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($candidates)): ?>
            <div class="featured-race__candidates">
                <div class="featured-race__candidates-title">
                    <i class="fa-solid fa-users" aria-hidden="true"></i>
                    Candidate Field
                </div>

                <ul class="featured-race__candidate-list">
                    <?php foreach ($candidates as $c): ?>
                        <?php
                        $name = (string)($c['full_name'] ?? '');
                        $url = (string)($c['candidate_url'] ?? '#');
                        ?>
                        <li class="featured-race__candidate">
                            <a href="<?= h($url) ?>" class="featured-race__candidate-link">
                                <?= h($name) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($notes !== ''): ?>
            <div class="featured-race__notes">
                <?= h($notes) ?>
            </div>
        <?php endif; ?>

        <div class="featured-race__actions">
            <a href="<?= h($raceUrl) ?>" class="btn btn-primary">
                View Full Race
            </a>
        </div>
    </div>
</section>
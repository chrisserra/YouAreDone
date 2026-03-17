<?php

declare(strict_types=1);

/** @var array $nextElection */

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date): string
    {
        if (!$date) return '';
        $ts = strtotime($date);
        if (!$ts) return '';
        return date('F j, Y', $ts);
    }
}

$e = $nextElection ?? null;

if (!$e) {
    return;
}

$title = (string)($e['title'] ?? '');
$stateName = (string)($e['state_name'] ?? '');
$officeName = (string)($e['office_name'] ?? '');
$electionType = (string)($e['election_type_name'] ?? '');
$electionDate = (string)($e['election_date'] ?? '');
$candidateCount = (int)($e['candidate_count'] ?? 0);
$raceUrl = (string)($e['race_url'] ?? '#');
$districtLabel = (string)($e['district_label'] ?? '');

$candidates = is_array($e['candidate_preview'] ?? null) ? $e['candidate_preview'] : [];

?>

<section class="dashboard-section next-election">
    <div class="card next-election__card">
        <div class="next-election__header">
            <div class="next-election__eyebrow">
                <i class="fa-solid fa-calendar-day" aria-hidden="true"></i>
                Next Election
            </div>

            <div class="next-election__meta">
                <span class="next-election__type"><?= h($electionType) ?></span>
                <?php if ($electionDate): ?>
                    <span class="next-election__date"><?= h(format_date($electionDate)) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="next-election__body">
            <h2 class="next-election__title">
                <?= h($stateName) ?> <?= h($officeName) ?>
                <?php if ($districtLabel): ?>
                    • <?= h($districtLabel) ?>
                <?php endif; ?>
            </h2>

            <?php if ($candidateCount > 0): ?>
                <div class="next-election__candidates-count">
                    <?= number_format($candidateCount) ?> candidate<?= $candidateCount === 1 ? '' : 's' ?> running
                </div>
            <?php endif; ?>

            <div class="next-election__actions">
                <a href="<?= h($raceUrl) ?>" class="btn btn-primary">
                    View Race
                </a>
            </div>
        </div>

        <?php if (!empty($candidates)): ?>
            <div class="next-election__sidebar">
                <div class="next-election__sidebar-title">
                    <i class="fa-solid fa-users" aria-hidden="true"></i>
                    Candidate Preview
                </div>

                <ul class="next-election__candidate-list">
                    <?php foreach ($candidates as $c): ?>
                        <?php
                        $name = (string)($c['full_name'] ?? '');
                        $url = (string)($c['candidate_url'] ?? '#');
                        $isIncumbent = (int)($c['is_incumbent'] ?? 0);
                        ?>
                        <li class="next-election__candidate">
                            <a href="<?= h($url) ?>" class="next-election__candidate-link">
                                <span class="next-election__candidate-name">
                                    <?= h($name) ?>
                                </span>

                                <?php if ($isIncumbent): ?>
                                    <span class="badge badge-incumbent">Incumbent</span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</section>
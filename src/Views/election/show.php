<?php

declare(strict_types=1);

/** @var array $event */
/** @var array $racesByOffice */

$event = $event ?? [];
$racesByOffice = $racesByOffice ?? [];

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('format_event_date')) {
    function format_event_date(?string $date): string
    {
        if (!$date) {
            return '—';
        }

        if ($date === date('Y-m-d')) {
            return 'Today';
        }

        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return '—';
        }

        return date('F j, Y', $timestamp);
    }
}

if (!function_exists('format_candidate_score')) {
    function format_candidate_score(mixed $score): string
    {
        if ($score === null || $score === '') {
            return '0';
        }

        if (is_numeric($score) && (int) $score == (float) $score) {
            return (string) (int) $score;
        }

        return (string) $score;
    }
}

if (!function_exists('format_event_race_title')) {
    function format_event_race_title(string $officeName, string $stateName, string $fallbackLabel): string
    {
        $officeName = trim($officeName);
        $stateName = trim($stateName);
        $fallbackLabel = trim($fallbackLabel);

        if ($officeName === 'U.S. Senate' && $stateName !== '') {
            return $stateName . ' U.S. Senate';
        }

        if ($officeName === 'Governor' && $stateName !== '') {
            return $stateName . ' Governor';
        }

        if ($officeName === 'President' && $stateName !== '') {
            return $stateName . ' President';
        }

        return $fallbackLabel !== '' ? $fallbackLabel : 'Race';
    }
}

$eventLabel = trim((string) ($event['event_label'] ?? 'Election'));
$electionDate = (string) ($event['election_date'] ?? '');
$stateName = trim((string) ($event['state_name'] ?? ''));
$electionType = trim((string) ($event['election_type'] ?? ''));

$officeOrder = [
    'President' => 1,
    'Governor' => 2,
    'U.S. Senate' => 3,
    'U.S. House' => 4,
];

if ($racesByOffice !== []) {
    uksort($racesByOffice, static function (string $a, string $b) use ($officeOrder): int {
        $aRank = $officeOrder[$a] ?? 999;
        $bRank = $officeOrder[$b] ?? 999;

        if ($aRank === $bRank) {
            return strcasecmp($a, $b);
        }

        return $aRank <=> $bRank;
    });
}
?>

<section class="event-page">
    <div class="event-page__hero card">
        <p class="event-page__eyebrow">Election Event</p>
        <h1 class="event-page__title"><?= h($eventLabel) ?></h1>
        <p class="event-page__summary">
            This page groups all tracked races in this election event by office and shows candidates ranked by documented green and red flags.
        </p>

        <div class="event-page__meta">
            <div class="event-page__meta-item">
                <span class="event-page__meta-label">Date</span>
                <span class="event-page__meta-value"><?= h(format_event_date($electionDate)) ?></span>
            </div>

            <div class="event-page__meta-item">
                <span class="event-page__meta-label">State</span>
                <span class="event-page__meta-value"><?= h($stateName !== '' ? $stateName : '—') ?></span>
            </div>

            <div class="event-page__meta-item">
                <span class="event-page__meta-label">Election Type</span>
                <span class="event-page__meta-value"><?= h($electionType !== '' ? ucwords(str_replace('-', ' ', $electionType)) : '—') ?></span>
            </div>
        </div>
    </div>

    <div class="event-page__explainer card">
        <h2 class="event-page__section-title">How to read this page</h2>
        <p class="event-page__section-copy">
            Candidates are ranked by score, with green flags helping and red flags hurting. Click any candidate card to open their full page.
        </p>
    </div>

    <div class="event-races card">
        <div class="event-races__header">
            <h2 class="event-page__section-title">Races</h2>
            <p class="event-page__section-copy">All races included in this election event, grouped by office.</p>
        </div>

        <?php if ($racesByOffice !== []): ?>
            <div class="event-office-groups">
                <?php foreach ($racesByOffice as $officeName => $races): ?>
                    <section class="event-office-group">
                        <div class="event-office-group__header">
                            <h3 class="event-office-group__title"><?= h((string) $officeName) ?></h3>
                            <span class="event-office-group__count">
                                <?= count($races) ?> <?= count($races) === 1 ? 'race' : 'races' ?>
                            </span>
                        </div>

                        <div class="event-office-group__races">
                            <?php foreach ($races as $race): ?>
                                <?php
                                $raceLabel = trim((string) ($race['label'] ?? 'Race'));
                                $displayRaceTitle = format_event_race_title((string) $officeName, $stateName, $raceLabel);
                                $raceUrl = trim((string) ($race['url'] ?? ''));
                                $candidates = is_array($race['candidates'] ?? null) ? $race['candidates'] : [];
                                $hiddenCount = count($candidates) > 1 ? count($candidates) - 1 : 0;
                                $toggleId = 'race-toggle-' . md5((string) (($race['race_id'] ?? '') . '-' . $raceLabel));
                                ?>
                                <article class="event-race">
                                    <div class="event-race__header">
                                        <div class="event-race__title-wrap">
                                            <h4 class="event-race__title"><?= h($displayRaceTitle) ?></h4>
                                            <div class="event-race__meta">
                                                <span class="event-race__count">
                                                    <?= count($candidates) ?> <?= count($candidates) === 1 ? 'candidate' : 'candidates' ?>
                                                </span>
                                            </div>
                                        </div>

                                        <?php if ($raceUrl !== ''): ?>
                                            <a class="event-action-button event-action-button--race" href="<?= h($raceUrl) ?>">
                                                View Race
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($candidates !== []): ?>
                                        <div class="event-candidates" id="<?= h($toggleId) ?>">
                                            <?php foreach ($candidates as $index => $candidate): ?>
                                                <?php
                                                $rank = $index + 1;
                                                $previewFlags = is_array($candidate['preview_flags'] ?? null)
                                                    ? $candidate['preview_flags']
                                                    : ['green' => [], 'red' => []];

                                                $greenReasons = is_array($previewFlags['green'] ?? null) ? $previewFlags['green'] : [];
                                                $redReasons = is_array($previewFlags['red'] ?? null) ? $previewFlags['red'] : [];
                                                $greenCount = count($greenReasons);
                                                $redCount = count($redReasons);
                                                $candidateUrl = trim((string) ($candidate['url'] ?? ''));
                                                $candidateName = trim((string) ($candidate['full_name'] ?? $candidate['name'] ?? 'Candidate'));

                                                $rankLabel = match ($rank) {
                                                    1 => 'Top ranked',
                                                    2 => 'Second',
                                                    3 => 'Third',
                                                    default => 'Ranked',
                                                };

                                                $rankClass = ' event-candidate--rank-' . ($rank <= 3 ? $rank : 3);
                                                if ($rank === 1) {
                                                    $rankClass .= ' event-candidate--top';
                                                }

                                                $hiddenAttribute = $rank > 1 ? ' data-hidden-candidate="true"' : '';
                                                $clickableClass = $candidateUrl !== '' ? ' event-candidate--clickable' : '';
                                                ?>
                                                <article
                                                        class="event-candidate event-candidate--compact<?= h($rankClass . $clickableClass) ?>"
                                                    <?= $hiddenAttribute ?>
                                                    <?php if ($candidateUrl !== ''): ?>
                                                        data-href="<?= h($candidateUrl) ?>"
                                                        tabindex="0"
                                                        role="link"
                                                        aria-label="View candidate <?= h($candidateName) ?>"
                                                    <?php endif; ?>
                                                >
                                                    <div class="event-candidate__compact-top">
                                                        <div class="event-candidate__compact-main">
                                                            <h5 class="event-candidate__name"><?= h($candidateName) ?></h5>
                                                            <div class="event-candidate__rank-row">
                                                                <span class="event-candidate__rank-label"><?= h($rankLabel) ?></span>
                                                            </div>
                                                        </div>

                                                        <div class="event-candidate__score-block">
                                                            <span class="event-candidate__score-block-label">Score</span>
                                                            <span class="event-candidate__score-block-value">
                                                                <?= h(format_candidate_score($candidate['score_total'] ?? 0)) ?>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="event-candidate__compact-stats">
                                                        <span class="event-candidate__total event-candidate__total--green">
                                                            <?= $greenCount ?> Green
                                                        </span>
                                                        <span class="event-candidate__total event-candidate__total--red">
                                                            <?= $redCount ?> Red
                                                        </span>
                                                    </div>
                                                </article>
                                            <?php endforeach; ?>
                                        </div>

                                        <?php if ($hiddenCount > 0): ?>
                                            <div class="event-race__toggle">
                                                <button
                                                        class="event-race__toggle-button"
                                                        type="button"
                                                        data-race-toggle
                                                        data-show-text="View <?= $hiddenCount ?> other candidate<?= $hiddenCount === 1 ? '' : 's' ?>"
                                                        data-hide-text="Hide other candidate<?= $hiddenCount === 1 ? '' : 's' ?>"
                                                        aria-expanded="false"
                                                        aria-controls="<?= h($toggleId) ?>"
                                                >
                                                    View <?= $hiddenCount ?> other candidate<?= $hiddenCount === 1 ? '' : 's' ?>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="event-race__empty">No candidate previews are available for this race yet.</p>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="event-races__empty">No races are available for this event yet.</p>
        <?php endif; ?>
    </div>
</section>
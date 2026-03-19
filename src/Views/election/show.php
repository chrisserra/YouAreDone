<?php

declare(strict_types=1);

/** @var array<string, mixed> $event */
/** @var array<string, array<int, array<string, mixed>>> $racesByOffice */

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

$eventLabel = trim((string) ($event['event_label'] ?? 'Election'));
$electionDate = (string) ($event['election_date'] ?? '');

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

<section class="event-page-hero card">
    <p class="event-page-hero__eyebrow">Election Event</p>
    <h1 class="event-page-hero__title"><?= h($eventLabel) ?></h1>
    <p class="event-page-hero__meta"><?= h(format_event_date($electionDate)) ?></p>
    <p class="event-page-hero__text">
        This page groups all tracked races in this election event by office and shows candidates ranked by documented
        green and red flags.
    </p>
</section>

<section class="event-page-section">
    <div class="event-page-explainer card">
        <h2 class="event-page-explainer__title">How to read this page</h2>
        <p class="event-page-explainer__text">
            This page groups all tracked races in this election event by office and shows candidates ranked by documented
            green and red flags. A candidate’s score is calculated from weighted green flags minus weighted red flags.
            If candidates are tied for first, the incumbent shown in that tie should appear first.
        </p>
        <div class="event-page-explainer__legend">
            <span class="flag-badge flag-badge--green">Green flags add points</span>
            <span class="flag-badge flag-badge--red">Red flags subtract points</span>
        </div>
    </div>
</section>

<section class="event-page-section">
    <div class="section-heading">
        <h2>Races</h2>
        <p>All races included in this election event, grouped by office.</p>
    </div>

    <?php if ($racesByOffice !== []): ?>
        <div class="event-races">
            <?php foreach ($racesByOffice as $office => $races): ?>
                <section class="event-races__group card">
                    <div class="event-races__group-header">
                        <h3 class="event-races__title"><?= h((string) $office) ?></h3>
                    </div>

                    <div class="event-races__group-body">
                        <?php foreach ($races as $race): ?>
                            <?php
                            $raceLabel = trim((string) ($race['label'] ?? 'Race'));
                            $raceUrl = trim((string) ($race['url'] ?? ''));
                            $candidates = is_array($race['candidates'] ?? null)
                                ? array_values($race['candidates'])
                                : [];

                            $otherCount = count($candidates) > 1 ? count($candidates) - 1 : 0;
                            $isHouseRace = str_contains(strtolower($raceLabel), 'district');
                            ?>
                            <article class="event-race">
                                <?php if ($isHouseRace): ?>
                                    <div class="event-race__header">
                                        <?php if ($raceUrl !== ''): ?>
                                            <h4 class="event-race__title">
                                                <a href="<?= h($raceUrl) ?>" class="event-race__link">
                                                    <?= h($raceLabel) ?>
                                                </a>
                                            </h4>
                                        <?php else: ?>
                                            <h4 class="event-race__title"><?= h($raceLabel) ?></h4>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($candidates !== []): ?>
                                    <div class="event-race__candidates">
                                        <?php foreach ($candidates as $index => $candidate): ?>
                                            <?php
                                            $rank = $index + 1;
                                            $candidateName = trim((string) ($candidate['full_name'] ?? 'Candidate'));
                                            $candidateUrl = trim((string) ($candidate['candidate_url'] ?? ''));
                                            $score = format_candidate_score($candidate['score_total'] ?? 0);
                                            $greenFlags = (int) ($candidate['green_flag_count'] ?? 0);
                                            $redFlags = (int) ($candidate['red_flag_count'] ?? 0);

                                            $previewFlags = is_array($candidate['preview_flags'] ?? null)
                                                ? $candidate['preview_flags']
                                                : ['green' => [], 'red' => []];

                                            $greenReasons = is_array($previewFlags['green'] ?? null)
                                                ? $previewFlags['green']
                                                : [];

                                            $redReasons = is_array($previewFlags['red'] ?? null)
                                                ? $previewFlags['red']
                                                : [];

                                            $isIncumbent = !empty($candidate['is_incumbent']);

                                            $rankLabel = match ($rank) {
                                                1 => 'Top ranked',
                                                2 => 'Second',
                                                3 => 'Third',
                                                default => 'Ranked',
                                            };

                                            $rankClass = $rank === 1 ? ' event-candidate--top' : '';
                                            $hiddenAttribute = $rank > 1 ? ' data-hidden-candidate="true"' : '';

                                            $greenPoints = array_sum(array_map(
                                                static fn(array $reason): float => (float) ($reason['effective_weight'] ?? 0),
                                                $greenReasons
                                            ));

                                            $redPoints = array_sum(array_map(
                                                static fn(array $reason): float => (float) ($reason['effective_weight'] ?? 0),
                                                $redReasons
                                            ));
                                            ?>
                                            <div class="event-candidate<?= $rankClass ?>"<?= $hiddenAttribute ?>>
                                                <div class="event-candidate__main">
                                                    <div class="event-candidate__identity">
                                                        <div class="event-candidate__topline">
                                                            <span class="event-candidate__rank"><?= h($rankLabel) ?></span>

                                                            <?php if ($isIncumbent): ?>
                                                                <span class="event-candidate__badge">Incumbent</span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <span class="event-candidate__name"><?= h($candidateName) ?></span>

                                                        <?php if ($greenReasons !== [] || $redReasons !== []): ?>
                                                            <div class="event-candidate__badges">
                                                                <?php foreach ($greenReasons as $reason): ?>
                                                                    <?php
                                                                    $tooltip = (string) (
                                                                        $reason['description']
                                                                        ?? $reason['flag_description']
                                                                        ?? $reason['note']
                                                                        ?? ''
                                                                    );
                                                                    ?>
                                                                    <span
                                                                            class="flag-badge flag-badge--green"
                                                                            title="<?= h($tooltip) ?>"
                                                                    >
                                                                        <span class="flag-badge__text">
                                                                            <?= h((string) ($reason['flag_name'] ?? '')) ?>
                                                                        </span>
                                                                        <span class="flag-badge__points">
                                                                            <?= h(format_candidate_score($reason['effective_weight'] ?? 0)) ?>
                                                                        </span>
                                                                    </span>
                                                                <?php endforeach; ?>

                                                                <?php foreach ($redReasons as $reason): ?>
                                                                    <?php
                                                                    $tooltip = (string) (
                                                                        $reason['description']
                                                                        ?? $reason['flag_description']
                                                                        ?? $reason['note']
                                                                        ?? ''
                                                                    );
                                                                    ?>
                                                                    <span
                                                                            class="flag-badge flag-badge--red"
                                                                            title="<?= h($tooltip) ?>"
                                                                    >
                                                                        <span class="flag-badge__text">
                                                                            <?= h((string) ($reason['flag_name'] ?? '')) ?>
                                                                        </span>
                                                                        <span class="flag-badge__points">
                                                                            <?= h(format_candidate_score($reason['effective_weight'] ?? 0)) ?>
                                                                        </span>
                                                                    </span>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>

                                                        <div class="event-candidate__meta">
                                                            <div class="event-candidate__score-line">
                                                                <span class="event-candidate__score-label">Score:</span>
                                                                <span class="event-candidate__score-main">
                                                                    <?= h($score) ?>
                                                                </span>
                                                                <span class="event-candidate__score-breakdown">
                                                                    (
                                                                    <span class="score-good"><?= h(format_candidate_score($greenPoints)) ?></span>
                                                                    -
                                                                    <span class="score-bad"><?= h(format_candidate_score($redPoints)) ?></span>
                                                                    )
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="event-candidate__flags">
                                                        <?php if ($greenFlags > 0): ?>
                                                            <span class="event-candidate__flag event-candidate__flag--good">
                                                                Green <?= h((string) $greenFlags) ?>
                                                            </span>
                                                        <?php endif; ?>

                                                        <?php if ($redFlags > 0): ?>
                                                            <span class="event-candidate__flag event-candidate__flag--bad">
                                                                Red <?= h((string) $redFlags) ?>
                                                            </span>
                                                        <?php endif; ?>

                                                        <?php if ($greenFlags === 0 && $redFlags === 0): ?>
                                                            <span class="event-candidate__flag event-candidate__flag--neutral">
                                                                No documented flags yet
                                                            </span>
                                                        <?php endif; ?>

                                                        <?php if ($candidateUrl !== ''): ?>
                                                            <a href="<?= h($candidateUrl) ?>" class="event-candidate__details-button">
                                                                View candidate details
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <?php if ($otherCount > 0): ?>
                                            <button
                                                    type="button"
                                                    class="event-race__toggle"
                                                    data-toggle-race
                                                    aria-expanded="false"
                                                    data-show-label="View <?= h((string) $otherCount) ?> other candidate<?= $otherCount === 1 ? '' : 's' ?>"
                                                    data-hide-label="Hide other candidates"
                                            >
                                                View <?= h((string) $otherCount) ?> other candidate<?= $otherCount === 1 ? '' : 's' ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
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
        <div class="empty-state card">
            <p>No races are available for this event yet.</p>
        </div>
    <?php endif; ?>
</section>
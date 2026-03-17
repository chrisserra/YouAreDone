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
$stateName = trim((string) ($event['state_name'] ?? ''));
$electionType = trim((string) ($event['election_type'] ?? ''));
?>

<section class="event-page-hero card">
    <p class="event-page-hero__eyebrow">Election Event</p>
    <h1 class="event-page-hero__title"><?= h($eventLabel) ?></h1>
    <p class="event-page-hero__text">
        Event-level overview for this tracked election date.
    </p>
</section>

<section class="event-page-section">
    <div class="section-heading">
        <h2>Event Details</h2>
        <p>High-level information for this election event.</p>
    </div>

    <div class="event-details card">
        <div class="event-details__grid">
            <div class="event-details__item">
                <p class="event-details__label">Date</p>
                <p class="event-details__value"><?= h(format_event_date($electionDate)) ?></p>
            </div>

            <div class="event-details__item">
                <p class="event-details__label">State</p>
                <p class="event-details__value"><?= h($stateName !== '' ? $stateName : '—') ?></p>
            </div>

            <div class="event-details__item">
                <p class="event-details__label">Election Type</p>
                <p class="event-details__value"><?= h($electionType !== '' ? $electionType : '—') ?></p>
            </div>
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
                            $candidates = is_array($race['candidates'] ?? null) ? array_values($race['candidates']) : [];
                            ?>
                            <article class="event-race">
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

                                <?php if ($candidates !== []): ?>
                                    <div class="event-race__candidates">
                                        <?php $rank = 0; ?>
                                        <?php foreach ($candidates as $candidate): ?>
                                            <?php
                                            $rank++;
                                            $candidateName = trim((string) ($candidate['display_name'] ?? $candidate['candidate_name'] ?? 'Candidate'));
                                            $candidateUrl = trim((string) ($candidate['url'] ?? ''));
                                            $score = format_candidate_score($candidate['score'] ?? 0);
                                            $greenFlags = (int) ($candidate['green_flags'] ?? 0);
                                            $redFlags = (int) ($candidate['red_flags'] ?? 0);
                                            $isIncumbent = !empty($candidate['is_incumbent']);

                                            $rankLabel = match ($rank) {
                                                1 => 'Top ranked',
                                                2 => 'Second',
                                                3 => 'Third',
                                                default => 'Ranked',
                                            };

                                            $rankClass = match ($rank) {
                                                1 => ' event-candidate--rank-1',
                                                2 => ' event-candidate--rank-2',
                                                3 => ' event-candidate--rank-3',
                                                default => '',
                                            };
                                            ?>
                                            <div class="event-candidate<?= $rankClass ?>">
                                                <div class="event-candidate__main">
                                                    <div class="event-candidate__identity">
                                                        <div class="event-candidate__topline">
                                                            <span class="event-candidate__rank"><?= h($rankLabel) ?></span>

                                                            <?php if ($isIncumbent): ?>
                                                                <span class="event-candidate__badge">Incumbent</span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <?php if ($candidateUrl !== ''): ?>
                                                            <a href="<?= h($candidateUrl) ?>" class="event-candidate__name">
                                                                <?= h($candidateName) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="event-candidate__name"><?= h($candidateName) ?></span>
                                                        <?php endif; ?>

                                                        <div class="event-candidate__meta">
                                                            <span class="event-candidate__score">Score <?= h($score) ?></span>
                                                        </div>
                                                    </div>

                                                    <div class="event-candidate__flags">
                                                        <span class="event-candidate__flag event-candidate__flag--good">
                                                            Green <?= h((string) $greenFlags) ?>
                                                        </span>
                                                        <span class="event-candidate__flag event-candidate__flag--bad">
                                                            Red <?= h((string) $redFlags) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
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
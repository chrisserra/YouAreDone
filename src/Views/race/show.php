<?php

declare(strict_types=1);

/** @var array $race */
/** @var array $elections */

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('format_race_title')) {
    function format_race_title(array $race): string
    {
        $title = trim(
            (string)($race['state_name'] ?? '') . ' ' .
            (string)($race['office_name'] ?? '') . ' ' .
            (string)($race['election_year'] ?? '')
        );

        if (
            ($race['district_type'] ?? '') === 'congressional_district' &&
            (int)($race['district_number'] ?? 0) > 0
        ) {
            $title .= ' District ' . (int)$race['district_number'];
        }

        if (!empty($race['is_special'])) {
            $title .= ' Special';
        }

        return trim($title);
    }
}

if (!function_exists('format_election_date')) {
    function format_election_date(?string $date): string
    {
        if (!$date) {
            return '';
        }

        $ts = strtotime($date);

        return $ts ? date('F j, Y', $ts) : '';
    }
}

if (!function_exists('candidate_display_name')) {
    function candidate_display_name(array $candidate): string
    {
        $ballotName = trim((string)($candidate['ballot_name'] ?? ''));
        $fullName = trim((string)($candidate['full_name'] ?? ''));

        return $ballotName !== '' ? $ballotName : $fullName;
    }
}

if (!function_exists('score_class')) {
    function score_class($score): string
    {
        $score = (float)$score;

        if ($score > 0) {
            return 'score--positive';
        }

        if ($score < 0) {
            return 'score--negative';
        }

        return 'score--neutral';
    }
}

if (!function_exists('race_path')) {
    function race_path(array $race): string
    {
        $path = '/races/'
            . rawurlencode((string)($race['state_slug'] ?? ''))
            . '/'
            . rawurlencode((string)($race['office_slug'] ?? ''))
            . '/'
            . rawurlencode((string)($race['election_year'] ?? ''));

        if (
            ($race['district_type'] ?? '') === 'congressional_district' &&
            (int)($race['district_number'] ?? 0) > 0
        ) {
            $path .= '/district-' . (int)$race['district_number'];
        }

        return $path;
    }
}

$raceTitle = format_race_title($race);
?>

<section class="page-section race-page">
    <div class="race-hero card">
        <p class="eyebrow">Race</p>
        <h1><?= h($raceTitle) ?></h1>

        <?php if (!empty($race['notes_public'])): ?>
            <p class="race-hero__notes"><?= nl2br(h((string)$race['notes_public'])) ?></p>
        <?php endif; ?>

        <div class="race-meta">
            <?php if (!empty($race['office_name'])): ?>
                <span class="badge"><?= h((string)$race['office_name']) ?></span>
            <?php endif; ?>

            <?php if (!empty($race['state_code'])): ?>
                <span class="badge"><?= h((string)$race['state_code']) ?></span>
            <?php endif; ?>

            <span class="badge"><?= h((string)($race['status'] ?? 'active')) ?></span>

            <?php if (!empty($race['seat_label'])): ?>
                <span class="badge"><?= h((string)$race['seat_label']) ?></span>
            <?php endif; ?>

            <?php if (
                ($race['district_type'] ?? '') === 'congressional_district' &&
                (int)($race['district_number'] ?? 0) > 0
            ): ?>
                <span class="badge">District <?= (int)$race['district_number'] ?></span>
            <?php endif; ?>

            <?php if (!empty($race['is_special'])): ?>
                <span class="badge">Special</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($elections)): ?>
        <div class="card empty-state">
            <h2>No elections found</h2>
            <p>This race exists, but no election records are attached yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($elections as $election): ?>
            <section class="election-section card">
                <div class="election-section__header">
                    <div>
                        <p class="eyebrow"><?= h((string)($election['election_type_name'] ?? 'Election')) ?></p>
                        <h2><?= h((string)($election['title'] ?? 'Election')) ?></h2>
                    </div>

                    <div class="election-section__meta">
                        <?php if (!empty($election['election_date'])): ?>
                            <span class="badge"><?= h(format_election_date((string)$election['election_date'])) ?></span>
                        <?php endif; ?>

                        <?php if (!empty($election['status'])): ?>
                            <span class="badge"><?= h((string)$election['status']) ?></span>
                        <?php endif; ?>

                        <?php if ((int)($election['round_number'] ?? 1) > 1): ?>
                            <span class="badge">Round <?= (int)$election['round_number'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($election['notes_public'])): ?>
                    <p class="election-section__notes"><?= nl2br(h((string)$election['notes_public'])) ?></p>
                <?php endif; ?>

                <?php if (empty($election['candidates'])): ?>
                    <div class="empty-state">
                        <p>No candidates attached to this election yet.</p>
                    </div>
                <?php else: ?>
                    <div class="candidate-list">
                        <?php foreach ($election['candidates'] as $index => $candidate): ?>
                            <?php
                            $candidateSlug = (string)($candidate['slug'] ?? '');
                            $partyLabel = '';

                            if (!empty($candidate['ballot_party_code'])) {
                                $partyLabel = (string)$candidate['ballot_party_code'];
                            } elseif (!empty($candidate['party_code'])) {
                                $partyLabel = (string)$candidate['party_code'];
                            }
                            ?>
                            <article class="candidate-card">
                                <div class="candidate-card__main">
                                    <div class="candidate-card__rank">
                                        #<?= $index + 1 ?>
                                    </div>

                                    <div class="candidate-card__content">
                                        <h3 class="candidate-card__name">
                                            <?php if ($candidateSlug !== ''): ?>
                                                <a href="/candidate/<?= h($candidateSlug) ?>">
                                                    <?= h(candidate_display_name($candidate)) ?>
                                                </a>
                                            <?php else: ?>
                                                <?= h(candidate_display_name($candidate)) ?>
                                            <?php endif; ?>
                                        </h3>

                                        <div class="candidate-card__meta">
                                            <?php if ($partyLabel !== ''): ?>
                                                <span class="badge"><?= h($partyLabel) ?></span>
                                            <?php endif; ?>

                                            <?php if (!empty($candidate['is_incumbent'])): ?>
                                                <span class="badge">Incumbent</span>
                                            <?php endif; ?>

                                            <?php if (!empty($candidate['is_major_candidate'])): ?>
                                                <span class="badge">Major Candidate</span>
                                            <?php endif; ?>

                                            <?php if (!empty($candidate['filing_status']) && $candidate['filing_status'] !== 'unknown'): ?>
                                                <span class="badge"><?= h((string)$candidate['filing_status']) ?></span>
                                            <?php endif; ?>

                                            <?php if (!empty($candidate['ballot_status']) && $candidate['ballot_status'] !== 'unknown'): ?>
                                                <span class="badge"><?= h((string)$candidate['ballot_status']) ?></span>
                                            <?php endif; ?>

                                            <?php if (!empty($candidate['result_status']) && $candidate['result_status'] !== 'unknown'): ?>
                                                <span class="badge"><?= h((string)$candidate['result_status']) ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($candidate['short_bio'])): ?>
                                            <p class="candidate-card__bio"><?= nl2br(h((string)$candidate['short_bio'])) ?></p>
                                        <?php endif; ?>

                                        <?php if (!empty($candidate['notes_public'])): ?>
                                            <p class="candidate-card__notes"><?= nl2br(h((string)$candidate['notes_public'])) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="candidate-card__stats">
                                    <div class="stat">
                                        <span class="stat__label">Score</span>
                                        <span class="stat__value <?= h(score_class($candidate['score_total'] ?? 0)) ?>">
                                            <?= h(number_format((float)($candidate['score_total'] ?? 0), 2)) ?>
                                        </span>
                                    </div>

                                    <div class="stat">
                                        <span class="stat__label">Green Flags</span>
                                        <span class="stat__value">
                                            <?= (int)($candidate['green_flag_count'] ?? 0) ?>
                                        </span>
                                    </div>

                                    <div class="stat">
                                        <span class="stat__label">Red Flags</span>
                                        <span class="stat__value">
                                            <?= (int)($candidate['red_flag_count'] ?? 0) ?>
                                        </span>
                                    </div>

                                    <?php if (array_key_exists('vote_percent', $candidate) && $candidate['vote_percent'] !== null): ?>
                                        <div class="stat">
                                            <span class="stat__label">Vote %</span>
                                            <span class="stat__value">
                                                <?= h(number_format((float)$candidate['vote_percent'], 3)) ?>%
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (array_key_exists('vote_count', $candidate) && $candidate['vote_count'] !== null): ?>
                                        <div class="stat">
                                            <span class="stat__label">Votes</span>
                                            <span class="stat__value">
                                                <?= h(number_format((int)$candidate['vote_count'])) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
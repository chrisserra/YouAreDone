<?php

declare(strict_types=1);

/** @var array $candidate */
/** @var array $candidateFlags */
/** @var array $electionFlags */
/** @var array $history */

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('format_candidate_score_class')) {
    function format_candidate_score_class($score): string
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

if (!function_exists('format_date_pretty')) {
    function format_date_pretty(?string $date): string
    {
        if (!$date) {
            return '';
        }

        $ts = strtotime($date);
        return $ts ? date('F j, Y', $ts) : '';
    }
}

if (!function_exists('format_race_link')) {
    function format_race_link(array $row): string
    {
        $url = '/races/' . ($row['state_slug'] ?? '') . '/' . ($row['office_slug'] ?? '') . '/' . ($row['election_year'] ?? '');

        if (
            ($row['district_type'] ?? '') === 'congressional_district' &&
            (int)($row['district_number'] ?? 0) > 0
        ) {
            $url .= '/district-' . (int)$row['district_number'];
        }

        return $url;
    }
}

if (!function_exists('format_race_label')) {
    function format_race_label(array $row): string
    {
        $label = trim(($row['state_name'] ?? '') . ' ' . ($row['office_name'] ?? '') . ' ' . ($row['election_year'] ?? ''));

        if (
            ($row['district_type'] ?? '') === 'congressional_district' &&
            (int)($row['district_number'] ?? 0) > 0
        ) {
            $label .= ' District ' . (int)$row['district_number'];
        }

        if (!empty($row['is_special'])) {
            $label .= ' Special';
        }

        return trim($label);
    }
}

if (!function_exists('flag_color_class')) {
    function flag_color_class(array $flag): string
    {
        return (($flag['flag_color'] ?? '') === 'green') ? 'flag--green' : 'flag--red';
    }
}
?>

<div class="page-back">
    <button
            type="button"
            class="button button--ghost page-back__button"
            onclick="if (document.referrer && window.history.length > 1) { history.back(); } else { window.location.href = '/'; }"
    >
        ← Back
    </button>
</div>

<section class="page-section candidate-page">
    <div class="candidate-hero card">
        <div class="candidate-hero__top">
            <div class="candidate-hero__identity">
                <p class="eyebrow">Candidate</p>
                <h1><?= h((string)($candidate['full_name'] ?? 'Candidate')) ?></h1>

                <div class="candidate-hero__meta">
                    <?php if (!empty($candidate['party_name']) || !empty($candidate['party_code'])): ?>
                        <span class="badge">
                        <?= h((string)($candidate['party_name'] ?: $candidate['party_code'])) ?>
                    </span>
                    <?php endif; ?>

                    <?php if (!empty($candidate['status'])): ?>
                        <span class="badge"><?= h((string)$candidate['status']) ?></span>
                    <?php endif; ?>

                    <?php if (!empty($candidate['preferred_name'])): ?>
                        <span class="badge">Preferred: <?= h((string)$candidate['preferred_name']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($candidate['image_url'])): ?>
                <div class="candidate-hero__image-wrap">
                    <img
                            class="candidate-hero__image"
                            src="<?= h((string)$candidate['image_url']) ?>"
                            alt="<?= h((string)($candidate['full_name'] ?? 'Candidate')) ?>"
                    >
                </div>
            <?php endif; ?>
        </div>

        <div class="candidate-score-grid">
            <div class="stat">
                <span class="stat__label">Score</span>
                <span class="stat__value">
                <?= h(number_format((float)($candidate['score_total'] ?? 0), 2)) ?>
            </span>
            </div>

            <div class="stat stat--green">
                <span class="stat__label">Green Flags</span>
                <span class="stat__value">
                    <?= (int)($candidate['green_flag_count'] ?? 0) ?>
                </span>
            </div>

            <div class="stat stat--red">
                <span class="stat__label">Red Flags</span>
                <span class="stat__value">
                    <?= (int)($candidate['red_flag_count'] ?? 0) ?>
                </span>
            </div>
        </div>

        <?php if (!empty($candidate['short_bio']) || !empty($candidate['summary_public'])): ?>
            <div class="candidate-hero__summary">
                <p class="candidate-hero__summary-label">Summary</p>
                <p class="candidate-hero__bio">
                    <?= nl2br(h((string)($candidate['short_bio'] ?: $candidate['summary_public']))) ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="candidate-links">
            <?php if (!empty($candidate['website_url'])): ?>
                <a class="button-link" href="<?= h((string)$candidate['website_url']) ?>" target="_blank" rel="noopener noreferrer">Website</a>
            <?php endif; ?>

            <?php if (!empty($candidate['ballotpedia_url'])): ?>
                <a class="button-link" href="<?= h((string)$candidate['ballotpedia_url']) ?>" target="_blank" rel="noopener noreferrer">Ballotpedia</a>
            <?php endif; ?>

            <?php if (!empty($candidate['wikipedia_url'])): ?>
                <a class="button-link" href="<?= h((string)$candidate['wikipedia_url']) ?>" target="_blank" rel="noopener noreferrer">Wikipedia</a>
            <?php endif; ?>

            <?php if (!empty($candidate['x_url'])): ?>
                <a class="button-link" href="<?= h((string)$candidate['x_url']) ?>" target="_blank" rel="noopener noreferrer">X</a>
            <?php endif; ?>

            <?php if (!empty($candidate['instagram_url'])): ?>
                <a class="button-link" href="<?= h((string)$candidate['instagram_url']) ?>" target="_blank" rel="noopener noreferrer">Instagram</a>
            <?php endif; ?>

            <?php if (!empty($candidate['facebook_url'])): ?>
                <a class="button-link" href="<?= h((string)$candidate['facebook_url']) ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
            <?php endif; ?>

            <?php if (!empty($candidate['youtube_url'])): ?>
                <a class="button-link" href="<?= h((string)$candidate['youtube_url']) ?>" target="_blank" rel="noopener noreferrer">YouTube</a>
            <?php endif; ?>
        </div>
    </div>

    <section class="card">
        <div class="section-header">
            <div>
                <p class="eyebrow">Candidate Flags</p>
                <h2>General Score Factors</h2>
            </div>
        </div>

        <?php if (empty($candidateFlags)): ?>
            <div class="empty-state">
                <p>No candidate-wide flags have been added yet.</p>
            </div>
        <?php else: ?>
            <div class="flag-list">
                <?php foreach ($candidateFlags as $flag): ?>
                    <article class="flag-card <?= h(flag_color_class($flag)) ?>">
                        <div class="flag-card__header">
                            <h3><?= h((string)$flag['flag_name']) ?></h3>
                            <span class="badge">
                                <?= h(ucfirst((string)$flag['flag_color'])) ?> •
                                <?= h(number_format((float)$flag['effective_weight'], 2)) ?>
                            </span>
                        </div>

                        <?php if (!empty($flag['flag_description'])): ?>
                            <p class="flag-card__description"><?= nl2br(h((string)$flag['flag_description'])) ?></p>
                        <?php endif; ?>

<!--                        --><?php //if (!empty($flag['note'])): ?>
<!--                            <p class="flag-card__note">--><?php //= nl2br(h((string)$flag['note'])) ?><!--</p>-->
<!--                        --><?php //endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="card">
        <div class="section-header">
            <div>
                <p class="eyebrow">Election Flags</p>
                <h2>Election-Specific Factors</h2>
            </div>
        </div>

        <?php if (empty($electionFlags)): ?>
            <div class="empty-state">
                <p>No election-specific flags have been added yet.</p>
            </div>
        <?php else: ?>
            <div class="flag-list">
                <?php foreach ($electionFlags as $flag): ?>
                    <article class="flag-card <?= h(flag_color_class($flag)) ?>">
                        <div class="flag-card__header">
                            <div>
                                <h3><?= h((string)$flag['flag_name']) ?></h3>
                                <p class="flag-card__context">
                                    <a href="<?= h(format_race_link($flag)) ?>">
                                        <?= h(format_race_label($flag)) ?>
                                    </a>
                                    ·
                                    <?= h((string)$flag['election_type_name']) ?>
                                    <?php if (!empty($flag['election_date'])): ?>
                                        · <?= h(format_date_pretty((string)$flag['election_date'])) ?>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <span class="badge">
                                <?= h(ucfirst((string)$flag['flag_color'])) ?> •
                                <?= h(number_format((float)$flag['effective_weight'], 2)) ?>
                            </span>
                        </div>

                        <?php if (!empty($flag['flag_description'])): ?>
                            <p class="flag-card__description"><?= nl2br(h((string)$flag['flag_description'])) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($flag['note'])): ?>
                            <p class="flag-card__note"><?= nl2br(h((string)$flag['note'])) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="card">
        <div class="section-header">
            <div>
                <p class="eyebrow">Election History</p>
                <h2>Races and Results</h2>
            </div>
        </div>

        <?php if (empty($history)): ?>
            <div class="empty-state">
                <p>No election history has been added yet.</p>
            </div>
        <?php else: ?>
            <div class="history-list">
                <?php foreach ($history as $row): ?>
                    <article class="history-card">
                        <div class="history-card__main">
                            <h3 class="history-card__title">
                                <a href="<?= h(format_race_link($row)) ?>">
                                    <?= h(format_race_label($row)) ?>
                                </a>
                            </h3>

                            <div class="history-card__meta">
                                <span class="badge"><?= h((string)$row['election_type_name']) ?></span>

                                <?php if (!empty($row['election_date'])): ?>
                                    <span class="badge"><?= h(format_date_pretty((string)$row['election_date'])) ?></span>
                                <?php endif; ?>

                                <?php if (!empty($row['ballot_party_code'])): ?>
                                    <span class="badge"><?= h((string)$row['ballot_party_code']) ?></span>
                                <?php endif; ?>

                                <?php if (!empty($row['is_incumbent'])): ?>
                                    <span class="badge">Incumbent</span>
                                <?php endif; ?>

                                <?php if (!empty($row['result_status'])): ?>
                                    <span class="badge"><?= h((string)$row['result_status']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="history-card__stats">
                            <?php if ($row['vote_percent'] !== null): ?>
                                <div class="stat">
                                    <span class="stat__label">Vote %</span>
                                    <span class="stat__value"><?= h(number_format((float)$row['vote_percent'], 3)) ?>%</span>
                                </div>
                            <?php endif; ?>

                            <?php if ($row['vote_count'] !== null): ?>
                                <div class="stat">
                                    <span class="stat__label">Votes</span>
                                    <span class="stat__value"><?= h(number_format((float)$row['vote_count'])) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($row['notes_public'])): ?>
                            <p class="history-card__notes"><?= nl2br(h((string)$row['notes_public'])) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
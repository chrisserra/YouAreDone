<?php

declare(strict_types=1);

/** @var array $race */
/** @var array $elections */

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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

if (!function_exists('candidate_display_name')) {
    function candidate_display_name(array $candidate): string
    {
        return trim((string) ($candidate['ballot_name'] ?? ''))
            ?: trim((string) ($candidate['full_name'] ?? ''));
    }
}

if (!function_exists('status_label')) {
    function status_label(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === 'unknown') {
            return '';
        }

        return ucwords(str_replace('_', ' ', $value));
    }
}

if (!function_exists('extract_candidate_flag_groups')) {
    function extract_candidate_flag_groups(array $candidate): array
    {
        $groups = [
            'green' => [],
            'red' => [],
        ];

        $previewFlags = $candidate['preview_flags'] ?? null;

        if (is_array($previewFlags)) {
            $green = is_array($previewFlags['green'] ?? null) ? $previewFlags['green'] : [];
            $red = is_array($previewFlags['red'] ?? null) ? $previewFlags['red'] : [];

            if ($green !== [] || $red !== []) {
                return [
                    'green' => array_values($green),
                    'red' => array_values($red),
                ];
            }
        }

        $candidateFlags = $candidate['candidate_flags'] ?? $candidate['flags'] ?? null;

        if (!is_array($candidateFlags)) {
            return $groups;
        }

        foreach ($candidateFlags as $flag) {
            if (!is_array($flag)) {
                continue;
            }

            $color = strtolower(trim((string) ($flag['flag_color'] ?? $flag['color'] ?? '')));
            $slug = trim((string) ($flag['flag_name'] ?? $flag['name'] ?? $flag['slug'] ?? ''));
            $weight = $flag['effective_weight'] ?? $flag['weight'] ?? $flag['default_weight'] ?? 0;
            $description = (string) ($flag['flag_description'] ?? $flag['note'] ?? '');

            if ($slug === '' || ($color !== 'green' && $color !== 'red')) {
                continue;
            }

            $groups[$color][] = [
                'flag_name' => $slug,
                'effective_weight' => $weight,
                'description' => $description,
                'flag_description' => $description,
                'note' => $description,
            ];
        }

        return $groups;
    }
}

$title = trim((string) ($race['seat_label'] ?? 'Race'));
$description = trim((string) ($race['notes_public'] ?? 'Track candidates and election stages for this race.'));
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

<section class="page-section race-page">

    <div class="race-hero">
        <h1 class="race-title"><?= h($title) ?></h1>

        <p class="race-description">
            <?= h($description) ?>
        </p>

        <div class="race-meta">
            <?php if (!empty($race['election_year'])): ?>
                <span class="badge"><?= h((string) $race['election_year']) ?></span>
            <?php endif; ?>

            <?php $status = status_label((string) ($race['status'] ?? 'active')); ?>
            <?php if ($status !== ''): ?>
                <span class="badge"><?= h($status) ?></span>
            <?php endif; ?>

            <?php if (!empty($race['district_label'])): ?>
                <span class="badge"><?= h((string) $race['district_label']) ?></span>
            <?php endif; ?>

            <?php if (!empty($race['is_special'])): ?>
                <span class="badge">Special</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($elections)): ?>
        <div class="card empty-state">
            <p>No elections available yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($elections as $election): ?>
            <section class="election card">

                <div class="election-header">
                    <h2><?= h((string) ($election['election_type_name'] ?? 'Election')) ?></h2>

                    <div class="election-meta">
                        <?php if (!empty($election['election_date'])): ?>
                            <span><?= h(format_election_date($election['election_date'])) ?></span>
                        <?php endif; ?>

                        <?php $estatus = status_label((string) ($election['status'] ?? '')); ?>
                        <?php if ($estatus !== ''): ?>
                            <span><?= h($estatus) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (empty($election['candidates'])): ?>
                    <p class="empty-state">No candidates yet.</p>
                <?php else: ?>
                    <div class="candidate-list">
                        <?php foreach ($election['candidates'] as $index => $candidate): ?>
                            <?php
                            $scoreTotal = (float) ($candidate['score_total'] ?? 0);
                            $greenCount = (int) ($candidate['green_flag_count'] ?? 0);
                            $redCount = (int) ($candidate['red_flag_count'] ?? 0);
                            $candidateName = candidate_display_name($candidate);
                            $candidateUrl = !empty($candidate['slug'])
                                ? '/candidate/' . rawurlencode((string) $candidate['slug'])
                                : '';
                            $isIncumbent = !empty($candidate['is_incumbent']);

                            $flagGroups = extract_candidate_flag_groups($candidate);
                            $greenReasons = $flagGroups['green'];
                            $redReasons = $flagGroups['red'];

                            $greenPoints = array_sum(array_map(
                                static fn(array $reason): float => (float) ($reason['effective_weight'] ?? 0),
                                $greenReasons
                            ));

                            $redPoints = array_sum(array_map(
                                static fn(array $reason): float => (float) ($reason['effective_weight'] ?? 0),
                                $redReasons
                            ));
                            ?>
                            <div class="event-candidate<?= $index === 0 ? ' event-candidate--top' : '' ?>">
                                <div class="event-candidate__main">
                                    <div class="event-candidate__identity">
                                        <div class="event-candidate__topline">
                                            <span class="candidate-rank">#<?= $index + 1 ?></span>

                                            <?php if (!empty($candidate['party_name']) || !empty($candidate['party_code'])): ?>
                                                <span class="candidate-party">
                                                    <?= h((string) ($candidate['party_name'] ?? $candidate['party_code'])) ?>
                                                </span>
                                            <?php endif; ?>

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
                                                    <?= h(format_candidate_score($scoreTotal)) ?>
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
                                        <?php if ($greenCount > 0): ?>
                                            <span class="event-candidate__flag event-candidate__flag--good">
                                                Green <?= h((string) $greenCount) ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ($redCount > 0): ?>
                                            <span class="event-candidate__flag event-candidate__flag--bad">
                                                Red <?= h((string) $redCount) ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ($greenCount === 0 && $redCount === 0): ?>
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
                    </div>
                <?php endif; ?>

            </section>
        <?php endforeach; ?>
    <?php endif; ?>

</section>
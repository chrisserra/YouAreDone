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
        <div class="event-office-groups">
            <?php foreach ($elections as $election): ?>
                <?php
                $candidates = is_array($election['candidates'] ?? null) ? $election['candidates'] : [];
                $electionStatus = status_label((string) ($election['status'] ?? ''));
                $electionTypeName = trim((string) ($election['election_type_name'] ?? 'Election'));
                $electionDate = format_election_date((string) ($election['election_date'] ?? ''));
                ?>

                <section class="event-office-group">
                    <div class="event-office-group__header">
                        <div class="event-race__title-wrap">
                            <h2 class="event-office-group__title"><?= h($electionTypeName) ?></h2>

                            <div class="event-race__meta">
                                <?php if ($electionDate !== ''): ?>
                                    <span class="event-race__count"><?= h($electionDate) ?></span>
                                <?php endif; ?>

                                <?php if ($electionStatus !== ''): ?>
                                    <span class="event-race__count"><?= h($electionStatus) ?></span>
                                <?php endif; ?>

                                <span class="event-race__count">
                                    <?= count($candidates) ?> <?= count($candidates) === 1 ? 'candidate' : 'candidates' ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="event-office-group__races">
                        <article class="event-race">
                            <?php if ($candidates === []): ?>
                                <p class="event-race__empty">No candidates yet.</p>
                            <?php else: ?>
                                <div class="event-candidates">
                                    <?php foreach ($candidates as $index => $candidate): ?>
                                        <?php
                                        $previewFlags = extract_candidate_flag_groups($candidate);
                                        $greenReasons = is_array($previewFlags['green'] ?? null) ? $previewFlags['green'] : [];
                                        $redReasons = is_array($previewFlags['red'] ?? null) ? $previewFlags['red'] : [];
                                        $greenCount = count($greenReasons);
                                        $redCount = count($redReasons);

                                        $candidateName = candidate_display_name($candidate);
                                        $candidateSlug = trim((string) ($candidate['slug'] ?? $candidate['candidate_slug'] ?? ''));
                                        $candidateUrl = trim((string) ($candidate['url'] ?? ''));

                                        if ($candidateUrl === '' && $candidateSlug !== '') {
                                            $candidateUrl = '/candidate/' . rawurlencode($candidateSlug);
                                        }

                                        $rank = $index + 1;
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
                                        ?>
                                        <?php if ($candidateUrl !== ''): ?>
                                            <a
                                                    class="event-candidate event-candidate--compact event-candidate--clickable<?= h($rankClass) ?>"
                                                    href="<?= h($candidateUrl) ?>"
                                                    aria-label="View candidate <?= h($candidateName) ?>"
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
                                            </a>
                                        <?php else: ?>
                                            <article class="event-candidate event-candidate--compact<?= h($rankClass) ?>">
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
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
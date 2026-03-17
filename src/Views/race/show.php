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

if (!function_exists('format_election_date')) {
    function format_election_date(?string $date): string
    {
        if (!$date) return '';
        $ts = strtotime($date);
        return $ts ? date('F j, Y', $ts) : '';
    }
}

if (!function_exists('candidate_display_name')) {
    function candidate_display_name(array $candidate): string
    {
        return trim((string)($candidate['ballot_name'] ?? '')) ?: trim((string)($candidate['full_name'] ?? ''));
    }
}

if (!function_exists('status_label')) {
    function status_label(?string $value): string
    {
        $value = trim((string)$value);
        if ($value === '' || $value === 'unknown') return '';
        return ucwords(str_replace('_', ' ', $value));
    }
}

$title = trim((string)($race['seat_label'] ?? 'Race'));
$description = trim((string)($race['notes_public'] ?? 'Track candidates and election stages for this race.'));
?>

<section class="page-section race-page">

    <!-- HERO -->
    <div class="race-hero">
        <h1 class="race-title"><?= h($title) ?></h1>

        <p class="race-description">
            <?= h($description) ?>
        </p>

        <div class="race-meta">
            <?php if (!empty($race['election_year'])): ?>
                <span class="badge"><?= h((string)$race['election_year']) ?></span>
            <?php endif; ?>

            <?php
            $status = status_label((string)($race['status'] ?? 'active'));
            ?>
            <?php if ($status !== ''): ?>
                <span class="badge"><?= h($status) ?></span>
            <?php endif; ?>

            <?php if (!empty($race['district_label'])): ?>
                <span class="badge"><?= h((string)$race['district_label']) ?></span>
            <?php endif; ?>

            <?php if (!empty($race['is_special'])): ?>
                <span class="badge">Special</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- ELECTIONS -->
    <?php if (empty($elections)): ?>
        <div class="card empty-state">
            <p>No elections available yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($elections as $election): ?>
            <section class="election card">

                <div class="election-header">
                    <h2>
                        <?= h((string)($election['election_type_name'] ?? 'Election')) ?>
                    </h2>

                    <div class="election-meta">
                        <?php if (!empty($election['election_date'])): ?>
                            <span><?= h(format_election_date($election['election_date'])) ?></span>
                        <?php endif; ?>

                        <?php
                        $estatus = status_label((string)($election['status'] ?? ''));
                        ?>
                        <?php if ($estatus !== ''): ?>
                            <span><?= h($estatus) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- CANDIDATES -->
                <?php if (empty($election['candidates'])): ?>
                    <p class="empty-state">No candidates yet.</p>
                <?php else: ?>
                    <div class="candidate-list">
                        <?php foreach ($election['candidates'] as $index => $candidate): ?>
                            <div class="candidate-row">
                                <div class="candidate-left">
                                    <span class="candidate-rank">#<?= $index + 1 ?></span>

                                    <span class="candidate-name">
                                        <?php if (!empty($candidate['slug'])): ?>
                                            <a href="/candidate/<?= h($candidate['slug']) ?>">
                                                <?= h(candidate_display_name($candidate)) ?>
                                            </a>
                                        <?php else: ?>
                                            <?= h(candidate_display_name($candidate)) ?>
                                        <?php endif; ?>
                                    </span>

                                    <?php if (!empty($candidate['party_name']) || !empty($candidate['party_code'])): ?>
                                        <span class="candidate-party">
                                            <?= h($candidate['party_name'] ?? $candidate['party_code']) ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($candidate['is_incumbent'])): ?>
                                        <span class="candidate-incumbent">Incumbent</span>
                                    <?php endif; ?>
                                </div>

                                <div class="candidate-right">
                                    <span class="candidate-score <?= (float)($candidate['score_total'] ?? 0) > 0 ? 'score--positive' : ((float)($candidate['score_total'] ?? 0) < 0 ? 'score--negative' : 'score--neutral') ?>">
                                        <?= number_format((float)($candidate['score_total'] ?? 0), 2) ?>
                                    </span>

                                    <span class="candidate-flags">
                                        🟢 <?= (int)($candidate['green_flag_count'] ?? 0) ?>
                                        🔴 <?= (int)($candidate['red_flag_count'] ?? 0) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </section>
        <?php endforeach; ?>
    <?php endif; ?>

</section>
<?php

declare(strict_types=1);

/** @var array $nextEvents */

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$items = is_array($nextEvents ?? null) ? $nextEvents : [];

if (empty($items)) {
    return;
}

if (!function_exists('homepage_event_preview_label')) {
    function homepage_event_preview_label(array $contest): string
    {
        $officeSlug = trim((string)($contest['office_slug'] ?? ''));
        $electionTypeName = trim((string)($contest['election_type_name'] ?? ''));

        if ($electionTypeName === '') {
            return '';
        }

        return match ($officeSlug) {
            'us-house' => 'U.S. House ' . $electionTypeName,
            'us-senate' => 'U.S. Senate ' . $electionTypeName,
            'governor' => 'Governor ' . $electionTypeName,
            'president' => 'Presidential ' . $electionTypeName,
            default => trim((string)($contest['office_name'] ?? '')) . ' ' . $electionTypeName,
        };
    }
}

if (!function_exists('homepage_event_preview_groups')) {
    function homepage_event_preview_groups(array $contests): array
    {
        $groups = [];

        foreach ($contests as $contest) {
            $label = homepage_event_preview_label($contest);

            if ($label === '') {
                continue;
            }

            $key = mb_strtolower($label);

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'label' => $label,
                    'race_url' => (string)($contest['race_url'] ?? '#'),
                ];
            }
        }

        $preferredOrder = [
            'u.s. house primary',
            'u.s. senate primary',
            'governor primary',
            'presidential primary',
            'u.s. house general',
            'u.s. senate general',
            'governor general',
            'presidential general',
        ];

        uksort($groups, static function (string $a, string $b) use ($preferredOrder): int {
            $posA = array_search($a, $preferredOrder, true);
            $posB = array_search($b, $preferredOrder, true);

            $posA = $posA === false ? 999 : $posA;
            $posB = $posB === false ? 999 : $posB;

            if ($posA === $posB) {
                return strcmp($a, $b);
            }

            return $posA <=> $posB;
        });

        return array_values($groups);
    }
}
?>

<section class="dashboard-section next-election" id="next-elections">
    <div class="dashboard-section__header">
        <h2 class="dashboard-section__title">
            <i class="fa-solid fa-calendar-day" aria-hidden="true"></i>
            Next Elections
        </h2>
    </div>

    <div class="next-election__grid">
        <?php foreach ($items as $event): ?>
            <?php
            $title = (string)($event['title'] ?? '');
            $eventDate = (string)($event['event_date'] ?? '');
            $contestCount = (int)($event['contest_count'] ?? 0);
            $candidateCount = (int)($event['candidate_count'] ?? 0);
            $contestPreview = is_array($event['contest_preview'] ?? null) ? $event['contest_preview'] : [];
            $previewGroups = homepage_event_preview_groups($contestPreview);
            ?>
            <article class="card next-election__card">
                <div class="next-election__header-row">
                    <div class="next-election__eyebrow">
                        <i class="fa-solid fa-landmark" aria-hidden="true"></i>
                        Election Event
                    </div>

                    <?php if ($eventDate !== ''): ?>
                        <div class="next-election__date">
                            <?= h(election_date($eventDate)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <h3 class="next-election__title">
                    <?= h($title) ?>
                </h3>

                <div class="next-election__meta">
                    <?php if ($contestCount > 0): ?>
                        <span class="next-election__meta-item">
                            <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                            <?= number_format($contestCount) ?> contest<?= $contestCount === 1 ? '' : 's' ?> tracked
                        </span>
                    <?php endif; ?>

                    <?php if ($candidateCount > 0): ?>
                        <span class="next-election__meta-item">
                            <i class="fa-solid fa-users" aria-hidden="true"></i>
                            <?= number_format($candidateCount) ?> candidate<?= $candidateCount === 1 ? '' : 's' ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="next-election__signal">
                    Incumbent replacement signals for this event will appear here once event-level scoring is wired in.
                </div>

                <?php if (!empty($previewGroups)): ?>
                    <div class="next-election__preview">
                        <div class="next-election__preview-title">
                            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                            Tracked Contests
                        </div>

                        <ul class="next-election__preview-list">
                            <?php foreach ($previewGroups as $group): ?>
                                <li class="next-election__preview-item">
                                    <a href="<?= h((string)$group['race_url']) ?>" class="next-election__preview-link">
                                        <?= h((string)$group['label']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="next-election__actions">
                    <span class="btn btn-secondary is-disabled" aria-disabled="true">
                        Election details coming soon
                    </span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
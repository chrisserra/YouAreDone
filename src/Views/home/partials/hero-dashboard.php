<?php

declare(strict_types=1);

/** @var array $hero */

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$hero = $hero ?? [];

$title = (string)($hero['title'] ?? 'Track Elections. Compare Candidates. Replace Weak Incumbents.');
$subtitle = (string)($hero['subtitle'] ?? 'Follow upcoming primaries, evaluate candidates using real signals, and see where incumbents are vulnerable.');
?>

<section class="dashboard-section dashboard-hero">
    <div class="dashboard-hero__main card">

        <div class="dashboard-hero__eyebrow">
            <i class="fa-solid fa-bolt" aria-hidden="true"></i>
            Election Tracker
        </div>

        <h1 class="dashboard-hero__title">
            <?= h($title) ?>
        </h1>

        <?php if ($subtitle !== ''): ?>
            <p class="dashboard-hero__subtitle">
                <?= h($subtitle) ?>
            </p>
        <?php endif; ?>

        <div class="dashboard-hero__actions">
            <a href="#next-elections" class="btn btn-primary">
                <i class="fa-solid fa-calendar-day"></i>
                View Upcoming Elections
            </a>

            <a href="/races" class="btn btn-secondary">
                <i class="fa-solid fa-landmark"></i>
                Browse All Races
            </a>
        </div>

    </div>
</section>
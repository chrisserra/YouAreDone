<?php

declare(strict_types=1);

/** @var string|null $pageTitle */
/** @var string|null $metaDescription */
/** @var array $hero */
/** @var array $nextEvents */
/** @var array $upcomingEvents */
/** @var array $browseOffices */
/** @var array $browseStates */

$pageTitle = $pageTitle ?? 'YouAreDone.org';
$metaDescription = $metaDescription ?? 'Track upcoming election events, compare candidates, and hold incumbents accountable.';
$hero = $hero ?? [];
$nextEvents = $nextEvents ?? [];
$upcomingEvents = $upcomingEvents ?? [];
$browseOffices = $browseOffices ?? [];
$browseStates = $browseStates ?? [];
?>

<div class="home-dashboard">
    <?php require __DIR__ . '/partials/hero-dashboard.php'; ?>

    <?php if (!empty($nextEvents)): ?>
        <?php require __DIR__ . '/partials/next-election-spotlight.php'; ?>
    <?php endif; ?>

    <?php if (!empty($upcomingEvents)): ?>
        <?php require __DIR__ . '/partials/upcoming-elections.php'; ?>
    <?php endif; ?>

    <?php require __DIR__ . '/partials/browse-offices.php'; ?>

    <?php if (!empty($browseStates)): ?>
        <?php require __DIR__ . '/partials/browse-states.php'; ?>
    <?php endif; ?>
</div>
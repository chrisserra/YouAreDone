<?php

declare(strict_types=1);

/** @var string|null $pageTitle */
/** @var string|null $metaDescription */
/** @var array $hero */
/** @var array|null $nextElection */
/** @var array $upcomingElections */
/** @var array $mostWatchedRaces */
/** @var array $accountabilitySignals */
/** @var array|null $featuredRace */
/** @var array $latestUpdates */
/** @var array $browseOffices */
/** @var array $browseStates */
/** @var array $methodology */

$pageTitle = $pageTitle ?? 'YouAreDone.org';
$metaDescription = $metaDescription ?? 'Track upcoming primaries, watched races, and candidate accountability.';
$hero = $hero ?? [];
$nextElection = $nextElection ?? null;
$upcomingElections = $upcomingElections ?? [];
$mostWatchedRaces = $mostWatchedRaces ?? [];
$accountabilitySignals = $accountabilitySignals ?? ['negative' => [], 'positive' => []];
$featuredRace = $featuredRace ?? null;
$latestUpdates = $latestUpdates ?? [];
$browseOffices = $browseOffices ?? [];
$browseStates = $browseStates ?? [];
$methodology = $methodology ?? [];

$hasNegativeSignals = !empty($accountabilitySignals['negative']);
$hasPositiveSignals = !empty($accountabilitySignals['positive']);
?>

<div class="home-dashboard">
    <?php require __DIR__ . '/partials/hero-dashboard.php'; ?>

    <?php if ($nextElection !== null): ?>
        <?php require __DIR__ . '/partials/next-election-spotlight.php'; ?>
    <?php endif; ?>

    <?php if (!empty($upcomingElections)): ?>
        <?php require __DIR__ . '/partials/upcoming-elections.php'; ?>
    <?php endif; ?>

    <?php if (!empty($mostWatchedRaces)): ?>
        <?php require __DIR__ . '/partials/most-watched-races.php'; ?>
    <?php endif; ?>

    <?php if ($hasNegativeSignals || $hasPositiveSignals): ?>
        <?php require __DIR__ . '/partials/accountability-signals.php'; ?>
    <?php endif; ?>

    <?php if ($featuredRace !== null): ?>
        <?php require __DIR__ . '/partials/featured-race.php'; ?>
    <?php endif; ?>

    <?php if (!empty($latestUpdates)): ?>
        <?php require __DIR__ . '/partials/latest-updates.php'; ?>
    <?php endif; ?>

    <?php require __DIR__ . '/partials/browse-offices.php'; ?>

    <?php if (!empty($browseStates)): ?>
        <?php require __DIR__ . '/partials/browse-states.php'; ?>
    <?php endif; ?>

    <?php require __DIR__ . '/partials/methodology.php'; ?>
</div>
<?php

declare(strict_types=1);

/** @var array $accountabilitySignals */

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$signals = is_array($accountabilitySignals ?? null) ? $accountabilitySignals : [];
$negative = is_array($signals['negative'] ?? null) ? $signals['negative'] : [];
$positive = is_array($signals['positive'] ?? null) ? $signals['positive'] : [];

if (empty($negative) && empty($positive)) {
    return;
}
?>

<section class="dashboard-section accountability-signals">
    <div class="dashboard-section__header">
        <h2 class="dashboard-section__title">
            <i class="fa-solid fa-scale-balanced" aria-hidden="true"></i>
            Accountability Signals
        </h2>
    </div>

    <div class="accountability-signals__grid">
        <div class="card accountability-panel accountability-panel--negative">
            <div class="accountability-panel__header">
                <div class="accountability-panel__title">
                    <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                    Highest Red Flag Totals
                </div>
            </div>

            <?php if (!empty($negative)): ?>
                <ul class="accountability-panel__list">
                    <?php foreach ($negative as $item): ?>
                        <?php
                        $name = (string)($item['full_name'] ?? '');
                        $url = (string)($item['candidate_url'] ?? '#');
                        $score = (float)($item['score_total'] ?? 0);
                        $greenCount = (int)($item['green_flag_count'] ?? 0);
                        $redCount = (int)($item['red_flag_count'] ?? 0);
                        $raceLabel = (string)($item['top_race_label'] ?? '');
                        ?>
                        <li class="accountability-panel__item">
                            <div class="accountability-panel__item-main">
                                <a href="<?= h($url) ?>" class="accountability-panel__candidate-link">
                                    <?= h($name) ?>
                                </a>

                                <?php if ($raceLabel !== ''): ?>
                                    <div class="accountability-panel__race">
                                        <?= h($raceLabel) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="accountability-panel__item-side">
                                <div class="accountability-score accountability-score--negative">
                                    <?= h(number_format($score, 1)) ?>
                                </div>

                                <div class="accountability-counts">
                                    <span class="accountability-count accountability-count--red">
                                        <i class="fa-solid fa-flag" aria-hidden="true"></i>
                                        <?= number_format($redCount) ?>
                                    </span>
                                    <span class="accountability-count accountability-count--green">
                                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                                        <?= number_format($greenCount) ?>
                                    </span>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="accountability-panel__empty">
                    No negative accountability signals available yet.
                </div>
            <?php endif; ?>
        </div>

        <div class="card accountability-panel accountability-panel--positive">
            <div class="accountability-panel__header">
                <div class="accountability-panel__title">
                    <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                    Strongest Positive Signals
                </div>
            </div>

            <?php if (!empty($positive)): ?>
                <ul class="accountability-panel__list">
                    <?php foreach ($positive as $item): ?>
                        <?php
                        $name = (string)($item['full_name'] ?? '');
                        $url = (string)($item['candidate_url'] ?? '#');
                        $score = (float)($item['score_total'] ?? 0);
                        $greenCount = (int)($item['green_flag_count'] ?? 0);
                        $redCount = (int)($item['red_flag_count'] ?? 0);
                        $raceLabel = (string)($item['top_race_label'] ?? '');
                        ?>
                        <li class="accountability-panel__item">
                            <div class="accountability-panel__item-main">
                                <a href="<?= h($url) ?>" class="accountability-panel__candidate-link">
                                    <?= h($name) ?>
                                </a>

                                <?php if ($raceLabel !== ''): ?>
                                    <div class="accountability-panel__race">
                                        <?= h($raceLabel) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="accountability-panel__item-side">
                                <div class="accountability-score accountability-score--positive">
                                    +<?= h(number_format($score, 1)) ?>
                                </div>

                                <div class="accountability-counts">
                                    <span class="accountability-count accountability-count--green">
                                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                                        <?= number_format($greenCount) ?>
                                    </span>
                                    <span class="accountability-count accountability-count--red">
                                        <i class="fa-solid fa-flag" aria-hidden="true"></i>
                                        <?= number_format($redCount) ?>
                                    </span>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="accountability-panel__empty">
                    No positive accountability signals available yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
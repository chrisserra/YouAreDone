<?php

declare(strict_types=1);

/** @var array $calendarWeeks */
/** @var string $calendarMonthLabel */
/** @var bool $calendarHasPrev */
/** @var bool $calendarHasNext */
/** @var string|null $calendarPrevUrl */
/** @var string|null $calendarNextUrl */
/** @var array $states */
/** @var array $browseOffices */

$calendarWeeks = $calendarWeeks ?? [];
$calendarMonthLabel = $calendarMonthLabel ?? '';
$calendarHasPrev = (bool)($calendarHasPrev ?? false);
$calendarHasNext = (bool)($calendarHasNext ?? false);
$calendarPrevUrl = $calendarPrevUrl ?? null;
$calendarNextUrl = $calendarNextUrl ?? null;
$states = $states ?? [];
$browseOffices = $browseOffices ?? [];

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('format_display_date')) {
    function format_display_date(?string $date): string
    {
        if (!$date) {
            return '—';
        }

        if ($date === date('Y-m-d')) {
            return 'Today';
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return '—';
        }

        return date('F j, Y', $timestamp);
    }
}

if (!function_exists('format_office_list')) {
    function format_office_list(array $offices): string
    {
        $clean = [];

        foreach ($offices as $office) {
            $office = trim((string)$office);
            if ($office !== '') {
                $clean[] = $office;
            }
        }

        return implode(' • ', $clean);
    }
}

if (!function_exists('office_bullet_items')) {
    function office_bullet_items(array $offices): string
    {
        $html = '';

        foreach ($offices as $office) {
            $office = trim((string)$office);
            if ($office === '') {
                continue;
            }

            $html .= '<li>' . h($office) . '</li>';
        }

        return $html;
    }
}

$electionTypeOptions = [];
$officeOptions = [];

foreach ($states as $state) {
    $typeLabel = trim((string)($state['election_type_label'] ?? ''));
    if ($typeLabel !== '') {
        $electionTypeOptions[$typeLabel] = $typeLabel;
    }

    $stateOffices = is_array($state['offices'] ?? null) ? $state['offices'] : [];
    foreach ($stateOffices as $office) {
        $office = trim((string)$office);
        if ($office !== '') {
            $officeOptions[$office] = $office;
        }
    }
}

ksort($electionTypeOptions, SORT_NATURAL | SORT_FLAG_CASE);
ksort($officeOptions, SORT_NATURAL | SORT_FLAG_CASE);

$weekdayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

$calendarHasEvents = false;
foreach ($calendarWeeks as $week) {
    foreach ($week as $day) {
        if (!empty($day['events'])) {
            $calendarHasEvents = true;
            break 2;
        }
    }
}
?>

<section class="hero-section card">
    <p class="eyebrow">Election Watch</p>
    <h1>See who’s leading—and who needs to go</h1>
    <p class="hero-copy">
        Track Democratic primaries and general elections across president, governor, U.S. Senate, and U.S. House.
        Browse the calendar, open election event pages, and explore the candidates on the ballot.
    </p>

    <div class="hero-actions">
        <a class="button button-primary" href="#election-calendar">See election calendar</a>
        <a class="button button-secondary" href="#browse-by-state">Find your state</a>
    </div>
</section>

<section id="election-calendar" class="calendar-section card">
    <div class="section-header calendar-header">
        <div>
            <p class="eyebrow">Election Calendar</p>
            <h2><?= h($calendarMonthLabel) ?></h2>
            <p class="section-copy">
                Browse election events month by month. Click an event to open that election’s page.
            </p>
        </div>

        <div class="calendar-nav" aria-label="Calendar month navigation">
            <?php if ($calendarHasPrev && $calendarPrevUrl): ?>
                <a class="calendar-nav-link" href="<?= h($calendarPrevUrl) ?>">&larr; Previous</a>
            <?php else: ?>
                <span class="calendar-nav-link is-disabled" aria-disabled="true">&larr; Previous</span>
            <?php endif; ?>

            <span class="calendar-nav-label"><?= h($calendarMonthLabel) ?></span>

            <?php if ($calendarHasNext && $calendarNextUrl): ?>
                <a class="calendar-nav-link" href="<?= h($calendarNextUrl) ?>">Next &rarr;</a>
            <?php else: ?>
                <span class="calendar-nav-link is-disabled" aria-disabled="true">Next &rarr;</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="calendar-grid-wrap">
        <div class="calendar-weekdays" aria-hidden="true">
            <?php foreach ($weekdayLabels as $label): ?>
                <div class="calendar-weekday"><?= h($label) ?></div>
            <?php endforeach; ?>
        </div>

        <div class="calendar-grid">
            <?php foreach ($calendarWeeks as $week): ?>
                <?php foreach ($week as $day): ?>
                    <?php
                    $events = is_array($day['events'] ?? null) ? $day['events'] : [];
                    $visibleEvents = array_slice($events, 0, 3);
                    $extraCount = max(0, count($events) - 3);

                    $dayClasses = ['calendar-day'];
                    if (empty($day['isCurrentMonth'])) {
                        $dayClasses[] = 'is-other-month';
                    }
                    if (!empty($day['isToday'])) {
                        $dayClasses[] = 'is-today';
                    }
                    if (!empty($day['isPast'])) {
                        $dayClasses[] = 'is-past';
                    }
                    ?>
                    <div class="<?= h(implode(' ', $dayClasses)) ?>">
                        <div class="calendar-day-top">
                            <span class="calendar-day-number"><?= (int)($day['dayNumber'] ?? 0) ?></span>
                        </div>

                        <div class="calendar-day-events">
                            <?php foreach ($visibleEvents as $index => $event): ?>
                                <?php
                                $title = trim((string)($event['title'] ?? ''));
                                $url = $event['url'] ?? null;
                                if ($title === '') {
                                    continue;
                                }
                                ?>
                                <?php if ($url): ?>
                                    <a class="calendar-event-link" href="<?= h((string)$url) ?>">
                                        <?= h($title) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="calendar-event-link is-disabled"><?= h($title) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <?php if ($extraCount > 0): ?>
                                <details class="calendar-overflow">
                                    <summary class="calendar-more-link">+<?= (int)$extraCount ?> more</summary>
                                    <div class="calendar-popover">
                                        <ul class="calendar-popover-list">
                                            <?php foreach ($events as $event): ?>
                                                <?php
                                                $title = trim((string)($event['title'] ?? ''));
                                                $url = $event['url'] ?? null;
                                                $offices = is_array($event['offices'] ?? null) ? $event['offices'] : [];
                                                if ($title === '') {
                                                    continue;
                                                }
                                                ?>
                                                <li class="calendar-popover-item">
                                                    <?php if ($url): ?>
                                                        <a class="calendar-popover-link" href="<?= h((string)$url) ?>">
                                                            <?= h($title) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="calendar-popover-link is-disabled"><?= h($title) ?></span>
                                                    <?php endif; ?>

                                                    <?php if ($offices !== []): ?>
                                                        <div class="calendar-popover-offices">
                                                            <?= h(format_office_list($offices)) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </details>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!$calendarHasEvents): ?>
        <div class="empty-state">
            No election events are available for this month.
        </div>
    <?php endif; ?>
</section>

<section id="browse-by-state" class="card">
    <div class="section-header">
        <div>
            <p class="eyebrow">Browse by State</p>
            <h2>Find the next tracked election in each state</h2>
            <p class="section-copy">
                Use the table below to jump into states with tracked election activity.
            </p>
        </div>
    </div>

    <form class="state-filters" method="get" action="#browse-by-state" onsubmit="return false;">
        <label>
            <span>Election Type</span>
            <select id="state-election-type-filter">
                <option value="">All election types</option>
                <?php foreach ($electionTypeOptions as $label): ?>
                    <option value="<?= h($label) ?>"><?= h($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span>Office</span>
            <select id="state-office-filter">
                <option value="">All offices</option>
                <?php foreach ($officeOptions as $office): ?>
                    <option value="<?= h($office) ?>"><?= h($office) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>

    <div class="table-wrap">
        <table class="state-table">
            <thead>
            <tr>
                <th>State</th>
                <th>Next Election</th>
                <th>Type</th>
                <th>Tracked Offices</th>
                <th>View</th>
            </tr>
            </thead>
            <tbody id="state-table-body">
            <?php if ($states === []): ?>
                <tr>
                    <td colspan="5">No state election data is available yet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($states as $state): ?>
                    <?php
                    $stateName = trim((string)($state['state_name'] ?? ''));
                    $eventUrl = $state['event_url'] ?? null;
                    $electionTypeLabel = trim((string)($state['election_type_label'] ?? ''));
                    $offices = is_array($state['offices'] ?? null) ? $state['offices'] : [];
                    ?>
                    <tr
                            data-election-type="<?= h($electionTypeLabel) ?>"
                            data-offices="<?= h(strtolower(implode('|', $offices))) ?>"
                    >
                        <td><?= h($stateName) ?></td>
                        <td><?= h(format_display_date($state['next_election_date'] ?? null)) ?></td>
                        <td><?= h($electionTypeLabel !== '' ? $electionTypeLabel : '—') ?></td>
                        <td>
                            <?php if ($offices !== []): ?>
                                <?= h(format_office_list($offices)) ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($eventUrl): ?>
                                <a class="table-link" href="<?= h((string)$eventUrl) ?>">View races</a>
                            <?php else: ?>
                                <span class="table-link is-disabled">Unavailable</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const electionTypeFilter = document.getElementById('state-election-type-filter');
        const officeFilter = document.getElementById('state-office-filter');
        const rows = Array.from(document.querySelectorAll('#state-table-body tr[data-election-type]'));

        function applyStateFilters() {
            const selectedType = (electionTypeFilter?.value || '').trim().toLowerCase();
            const selectedOffice = (officeFilter?.value || '').trim().toLowerCase();

            rows.forEach((row) => {
                const rowType = (row.getAttribute('data-election-type') || '').trim().toLowerCase();
                const rowOffices = (row.getAttribute('data-offices') || '').trim().toLowerCase();

                const typeMatch = selectedType === '' || rowType === selectedType;
                const officeMatch = selectedOffice === '' || rowOffices.includes(selectedOffice);

                row.style.display = typeMatch && officeMatch ? '' : 'none';
            });
        }

        electionTypeFilter?.addEventListener('change', applyStateFilters);
        officeFilter?.addEventListener('change', applyStateFilters);
    });
</script>
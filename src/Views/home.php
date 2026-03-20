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
                Search, sort, and filter states to jump into tracked election events.
            </p>
        </div>
    </div>

    <?php
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
    ?>

    <div class="state-table-wrap">
        <div class="state-table-tools">
            <label class="state-table-tools__search">
                <span>Search states</span>
                <input
                        type="search"
                        id="state-table-search"
                        class="state-table-tools__input"
                        placeholder="Search by state, election type, or office"
                >
            </label>

            <label class="state-table-tools__filter">
                <span>Election type</span>
                <select id="state-table-type-filter" class="state-table-tools__select">
                    <option value="">All election types</option>
                    <?php foreach ($electionTypeOptions as $label): ?>
                        <option value="<?= h($label) ?>"><?= h($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="state-table-tools__filter">
                <span>Office</span>
                <select id="state-table-office-filter" class="state-table-tools__select">
                    <option value="">All offices</option>
                    <?php foreach ($officeOptions as $office): ?>
                        <option value="<?= h($office) ?>"><?= h($office) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div class="table-scroll">
            <table id="state-table" class="state-table">
                <thead>
                <tr>
                    <th>
                        <button type="button" class="state-table__sort" data-sort-key="state" aria-pressed="false">
                            State
                        </button>
                    </th>
                    <th>
                        <button type="button" class="state-table__sort" data-sort-key="date" aria-pressed="true">
                            Next Election
                        </button>
                    </th>
                    <th>
                        <button type="button" class="state-table__sort" data-sort-key="type" aria-pressed="false">
                            Type
                        </button>
                    </th>
                    <th>
                        <button type="button" class="state-table__sort" data-sort-key="offices" aria-pressed="false">
                            Tracked Offices
                        </button>
                    </th>
                    <th>View</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($states === []): ?>
                    <tr>
                        <td colspan="5">No state election data is available yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($states as $state): ?>
                        <?php
                        $stateName = trim((string)($state['state_name'] ?? ''));
                        $electionTypeLabel = trim((string)($state['election_type_label'] ?? ''));
                        $nextElectionDate = $state['next_election_date'] ?? null;
                        $eventUrl = $state['event_url'] ?? null;
                        $offices = is_array($state['offices'] ?? null) ? $state['offices'] : [];
                        $officeList = format_office_list($offices);
                        $dateSortValue = $nextElectionDate ? (string)$nextElectionDate : '9999-12-31';
                        $officesSortValue = strtolower(implode('|', $offices));
                        ?>
                        <tr
                                data-href="<?= h((string)($eventUrl ?? '')) ?>"
                                data-state="<?= h($stateName) ?>"
                                data-date="<?= h($dateSortValue) ?>"
                                data-type="<?= h($electionTypeLabel) ?>"
                                data-offices="<?= h($officesSortValue) ?>"
                        >
                            <td class="state-table__state"><?= h($stateName) ?></td>
                            <td><?= h(format_display_date($nextElectionDate)) ?></td>
                            <td><?= h($electionTypeLabel !== '' ? $electionTypeLabel : '—') ?></td>
                            <td><?= h($officeList !== '' ? $officeList : '—') ?></td>
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
    </div>
</section>
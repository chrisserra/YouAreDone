<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $nextElections */
/** @var array<int, array<string, mixed>> $upcomingElections */
/** @var array<int, array<string, mixed>> $states */
/** @var array<int, array<string, string>> $browseOffices */

$nextElections = $nextElections ?? [];
$upcomingElections = $upcomingElections ?? [];
$states = $states ?? [];
$browseOffices = $browseOffices ?? [];

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
            $office = trim((string) $office);

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
            $office = trim((string) $office);

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
    $typeLabel = trim((string) ($state['election_type_label'] ?? ''));

    if ($typeLabel !== '') {
        $electionTypeOptions[$typeLabel] = $typeLabel;
    }

    $stateOffices = is_array($state['offices'] ?? null) ? $state['offices'] : [];

    foreach ($stateOffices as $office) {
        $office = trim((string) $office);

        if ($office !== '') {
            $officeOptions[$office] = $office;
        }
    }
}

ksort($electionTypeOptions, SORT_NATURAL | SORT_FLAG_CASE);
ksort($officeOptions, SORT_NATURAL | SORT_FLAG_CASE);
?>

<section class="home-hero card">
    <p class="home-hero__eyebrow">Election Watch</p>

    <h1 class="home-hero__title">
        See who’s leading—and who needs to go
    </h1>

    <p class="home-hero__text">
        Track upcoming Democratic primaries across president, governor, U.S. Senate, and U.S. House.
        Candidates are ranked using weighted green and red flags based on documented public records.
    </p>

    <div class="home-hero__actions">
        <a href="#next-elections" class="button button--primary">
            See upcoming elections
        </a>

        <a href="#browse-by-state" class="button button--secondary">
            Find your state
        </a>
    </div>
</section>

<section class="home-section" id="next-elections">
    <div class="section-heading">
        <h2>Upcoming Elections</h2>
        <p>The next election events on the calendar.</p>
    </div>

    <?php
    $allUpcomingElections = array_merge($nextElections, $upcomingElections);
    ?>

    <?php if ($allUpcomingElections !== []): ?>
        <div class="event-grid">
        <?php foreach ($allUpcomingElections as $index => $event): ?>
            <?php
            $eventUrl = $event['event_url'] ?? null;
            $tag = $eventUrl ? 'a' : 'article';
            $isPrimaryCard = $index < 3;
            $eventDate = $event['election_date'] ?? null;
            $isTodayCard = $eventDate === date('Y-m-d');
            $isTomorrowCard = $eventDate === date('Y-m-d', strtotime('+1 day'));
            ?>
            <<?= $tag ?>
            class="event-card card
            <?= $eventUrl ? ' event-card--link' : '' ?>
            <?= $isPrimaryCard ? ' event-card--primary' : '' ?>
            <?= $isTodayCard ? ' event-card--today' : '' ?>
            <?= $isTomorrowCard ? ' event-card--tomorrow' : '' ?>"<?= $eventUrl ? ' event-card--link' : '' ?><?= $isPrimaryCard ? ' event-card--primary' : '' ?><?= $isTodayCard ? ' event-card--today' : '' ?>"
            <?php if ($eventUrl): ?>
                href="<?= h((string) $eventUrl) ?>"
            <?php endif; ?>
            >
            <p class="event-card__date"><?= h(format_display_date($event['election_date'] ?? null)) ?></p>
            <h3 class="event-card__title"><?= h((string) ($event['event_label'] ?? 'Upcoming Election')) ?></h3>

            <?php if (!empty($event['offices']) && is_array($event['offices'])): ?>
                <div class="event-card__meta">
                    <p class="event-card__meta-label">Tracked offices</p>
                    <ul class="event-card__list">
                        <?= office_bullet_items($event['offices']) ?>
                    </ul>
                </div>
            <?php else: ?>
                <p class="event-card__empty">No tracked offices listed.</p>
            <?php endif; ?>
            </<?= $tag ?>>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state card">
            <p>No upcoming election events are available yet.</p>
        </div>
    <?php endif; ?>
</section>

<section class="home-section" id="browse-by-state">
    <div class="section-heading">
        <h2>Browse by State</h2>
        <p>Find the next tracked election event in each state and jump straight into the races.</p>
    </div>

    <div class="state-table-wrap card">
        <div class="state-table-tools">
            <label class="state-table-tools__search">
                <span>Search</span>
                <input
                        type="search"
                        id="state-table-search"
                        class="state-table-tools__input"
                        placeholder="Search by state, election type, or office"
                        autocomplete="off"
                >
            </label>

            <label class="state-table-tools__filter">
                <span>Election Type</span>
                <select id="state-table-type-filter" class="state-table-tools__select">
                    <option value="">All election types</option>
                    <?php foreach ($electionTypeOptions as $typeLabel): ?>
                        <option value="<?= h($typeLabel) ?>"><?= h($typeLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="state-table-tools__filter">
                <span>Office</span>
                <select id="state-table-office-filter" class="state-table-tools__select">
                    <option value="">All offices</option>
                    <?php foreach ($officeOptions as $officeLabel): ?>
                        <option value="<?= h($officeLabel) ?>"><?= h($officeLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <?php if ($states !== []): ?>
            <div class="table-scroll">
                <table class="state-table" id="state-table">
                    <thead>
                    <tr>
                        <th scope="col">
                            <button type="button" class="state-table__sort" data-sort-key="state">
                                State
                            </button>
                        </th>
                        <th scope="col">
                            <button type="button" class="state-table__sort" data-sort-key="date">
                                Next Election
                            </button>
                        </th>
                        <th scope="col">
                            <button type="button" class="state-table__sort" data-sort-key="type">
                                Type
                            </button>
                        </th>
                        <th scope="col">
                            <button type="button" class="state-table__sort" data-sort-key="offices">
                                Tracked Offices
                            </button>
                        </th>
                        <th scope="col" class="state-table__action-col">
                            View
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($states as $state): ?>
                        <?php
                        $stateName = trim((string) ($state['state_name'] ?? ''));
                        $nextDate = $state['next_election_date'] ?? null;
                        $electionTypeLabel = trim((string) ($state['election_type_label'] ?? ''));
                        $offices = is_array($state['offices'] ?? null) ? $state['offices'] : [];
                        $officesText = $offices !== [] ? format_office_list($offices) : '—';
                        $dateSortValue = $nextDate ?: '9999-12-31';
                        $typeValue = $electionTypeLabel !== '' ? $electionTypeLabel : '—';
                        $rowUrl = trim((string) ($state['event_url'] ?? $state['state_url'] ?? ''));
                        ?>
                        <tr
                                data-state="<?= h($stateName) ?>"
                                data-date="<?= h($dateSortValue) ?>"
                                data-type="<?= h($typeValue) ?>"
                                data-offices="<?= h($officesText) ?>"
                            <?= $rowUrl !== '' ? 'data-href="' . h($rowUrl) . '"' : '' ?>
                            <?= $rowUrl !== '' ? 'class="state-table__row--link"' : '' ?>
                        >
                            <td>
                                <span class="state-table__state"><?= h($stateName) ?></span>
                            </td>
                            <td>
                                <span class="state-table__date"><?= h(format_display_date($nextDate)) ?></span>
                            </td>
                            <td>
                                <span class="state-table__type"><?= h($typeValue) ?></span>
                            </td>
                            <td>
                                <span class="state-table__offices"><?= h($officesText) ?></span>
                            </td>
                            <td class="state-table__action">
                                <?php if ($rowUrl !== ''): ?>
                                    <a href="<?= h($rowUrl) ?>" class="state-table__link-button">
                                        View races
                                    </a>
                                <?php else: ?>
                                    <span class="state-table__link-button state-table__link-button--disabled">
                                        Unavailable
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No state election data is available yet.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
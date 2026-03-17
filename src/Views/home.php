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
    <h1 class="home-hero__title">Hold Democrats Accountable</h1>
    <p class="home-hero__text">
        Track the next elections, upcoming contests, and where to watch next across presidential,
        U.S. Senate, U.S. House, and governor races.
    </p>
</section>

<section class="home-section">
    <div class="section-heading">
        <h2>Next Elections</h2>
        <p>The next upcoming election events on the calendar.</p>
    </div>

    <?php if ($nextElections !== []): ?>
        <div class="event-grid">
        <?php foreach ($nextElections as $event): ?>
            <?php
            $eventUrl = $event['event_url'] ?? null;
            $tag = $eventUrl ? 'a' : 'article';
            ?>
            <<?= $tag ?>
            class="event-card card<?= $eventUrl ? ' event-card--link' : '' ?>"
            <?php if ($eventUrl): ?>
                href="<?= h((string) $eventUrl) ?>"
            <?php endif; ?>
            >
            <p class="event-card__date"><?= h(format_display_date($event['election_date'] ?? null)) ?></p>
            <h3 class="event-card__title"><?= h((string) ($event['event_label'] ?? 'Upcoming Election')) ?></h3>

            <?php if (!empty($event['offices']) && is_array($event['offices'])): ?>
                <ul class="event-card__list">
                    <?= office_bullet_items($event['offices']) ?>
                </ul>
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

<section class="home-section">
    <div class="section-heading">
        <h2>Upcoming Elections</h2>
        <p>Additional election events coming up after the next date on the calendar.</p>
    </div>

    <?php if ($upcomingElections !== []): ?>
        <div class="event-grid event-grid--compact">
            <?php foreach ($upcomingElections as $event): ?>
                <article class="event-card event-card--compact card">
                    <p class="event-card__date"><?= h(format_display_date($event['election_date'] ?? null)) ?></p>
                    <h3 class="event-card__title"><?= h((string) ($event['event_label'] ?? 'Upcoming Election')) ?></h3>

                    <?php if (!empty($event['offices']) && is_array($event['offices'])): ?>
                        <ul class="event-card__list event-card__list--compact">
                            <?= office_bullet_items($event['offices']) ?>
                        </ul>
                    <?php else: ?>
                        <p class="event-card__empty">No tracked offices listed.</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state card">
            <p>No additional upcoming election events are available yet.</p>
        </div>
    <?php endif; ?>
</section>

<section class="home-section">
    <div class="section-heading">
        <h2>Browse by State</h2>
        <p>See the next tracked election event in each state.</p>
    </div>

    <div class="state-table-wrap card">
        <div class="state-table-tools">
            <label class="state-table-tools__search">
                <span>Search</span>
                <input
                        type="search"
                        id="state-table-search"
                        class="state-table-tools__input"
                        placeholder="Search state, election type, or office"
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
                            Next Election Date
                        </button>
                    </th>
                    <th scope="col">
                        <button type="button" class="state-table__sort" data-sort-key="type">
                            Election Type
                        </button>
                    </th>
                    <th scope="col">
                        <button type="button" class="state-table__sort" data-sort-key="offices">
                            Offices
                        </button>
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
                    ?>
                    <tr
                            data-state="<?= h($stateName) ?>"
                            data-date="<?= h($dateSortValue) ?>"
                            data-type="<?= h($typeValue) ?>"
                            data-offices="<?= h($officesText) ?>"
                    >
                        <td>
                            <span class="state-table__state"><?= h($stateName) ?></span>
                        </td>
                        <td>
                            <?= h(format_display_date($nextDate)) ?>
                        </td>
                        <td>
                            <?= h($typeValue) ?>
                        </td>
                        <td>
                            <?= h($officesText) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Repositories\ElectionRepository;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

class HomeController
{
    private ElectionRepository $elections;

    public function __construct(?ElectionRepository $elections = null)
    {
        $this->elections = $elections ?? new ElectionRepository();
    }

    public function index(): void
    {
        $bounds = $this->elections->getCalendarBounds();

        $selectedMonth = $this->resolveSelectedMonth(
            $_GET['month'] ?? null,
            $bounds['min_month'] ?? null,
            $bounds['max_month'] ?? null
        );

        $monthStart = $selectedMonth . '-01';
        $monthStartDate = new DateTimeImmutable($monthStart);
        $monthEndDate = $monthStartDate->modify('last day of this month');
        $monthEnd = $monthEndDate->format('Y-m-d');

        $rows = $this->elections->getCalendarEventsForMonth($monthStart, $monthEnd);

        $eventsByDate = [];
        foreach ($rows as $row) {
            $eventDate = (string) ($row['election_date'] ?? '');
            if ($eventDate === '') {
                continue;
            }

            if (!isset($eventsByDate[$eventDate])) {
                $eventsByDate[$eventDate] = [];
            }

            $eventsByDate[$eventDate][] = [
                'title' => $this->buildEventLabel(
                    (string) ($row['state_name'] ?? ''),
                    (string) ($row['election_type'] ?? '')
                ),
                'url' => $this->buildEventUrl([
                    'state_slug' => (string) ($row['state_slug'] ?? ''),
                    'election_type_slug' => (string) ($row['election_type_slug'] ?? ''),
                    'election_date' => $eventDate,
                ]),
                'offices' => $this->parseOffices((string) ($row['offices'] ?? '')),
            ];
        }

        $calendarWeeks = $this->buildCalendarWeeks(
            $monthStartDate,
            $monthEndDate,
            $eventsByDate
        );

        $minMonth = $bounds['min_month'] ?? null;
        $maxMonth = $bounds['max_month'] ?? null;

        $prevMonth = $monthStartDate->modify('-1 month')->format('Y-m');
        $nextMonth = $monthStartDate->modify('+1 month')->format('Y-m');

        $calendarHasPrev = $minMonth !== null && $prevMonth >= $minMonth;
        $calendarHasNext = $maxMonth !== null && $nextMonth <= $maxMonth;

        $states = $this->normalizeStateCards(
            $this->elections->getStatesWithNextElection()
        );

        View::render('home', [
            'calendarMonth' => $selectedMonth,
            'calendarMonthLabel' => $monthStartDate->format('F Y'),
            'calendarHasPrev' => $calendarHasPrev,
            'calendarHasNext' => $calendarHasNext,
            'calendarPrevUrl' => $calendarHasPrev ? '/?month=' . $prevMonth : null,
            'calendarNextUrl' => $calendarHasNext ? '/?month=' . $nextMonth : null,
            'calendarWeeks' => $calendarWeeks,
            'states' => $states,
            'browseOffices' => $this->browseOffices(),
        ]);
    }

    private function resolveSelectedMonth(
        mixed $requestedMonth,
        ?string $minMonth,
        ?string $maxMonth
    ): string {
        $currentMonth = (new DateTimeImmutable('today'))->format('Y-m');

        $month = is_string($requestedMonth) && preg_match('/^\d{4}-\d{2}$/', $requestedMonth)
            ? $requestedMonth
            : $currentMonth;

        if ($minMonth !== null && $month < $minMonth) {
            return $minMonth;
        }

        if ($maxMonth !== null && $month > $maxMonth) {
            return $maxMonth;
        }

        return $month;
    }

    private function buildCalendarWeeks(
        DateTimeImmutable $monthStartDate,
        DateTimeImmutable $monthEndDate,
        array $eventsByDate
    ): array {
        $gridStart = $monthStartDate->modify('-' . ((int) $monthStartDate->format('w')) . ' days');
        $gridEnd = $monthEndDate->modify('+' . (6 - (int) $monthEndDate->format('w')) . ' days');

        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        $selectedMonth = $monthStartDate->format('Y-m');

        $period = new DatePeriod(
            $gridStart,
            new DateInterval('P1D'),
            $gridEnd->modify('+1 day')
        );

        $weeks = [];
        $week = [];

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');

            $week[] = [
                'date' => $dateString,
                'dayNumber' => (int) $date->format('j'),
                'isCurrentMonth' => $date->format('Y-m') === $selectedMonth,
                'isToday' => $dateString === $today,
                'isPast' => $dateString < $today,
                'events' => $eventsByDate[$dateString] ?? [],
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }

        if ($week !== []) {
            $weeks[] = $week;
        }

        return $weeks;
    }

    private function normalizeStateCards(array $rows): array
    {
        $cards = [];

        foreach ($rows as $row) {
            $cards[] = [
                'state_name' => (string) ($row['state_name'] ?? ''),
                'state_slug' => (string) ($row['state_slug'] ?? ''),
                'state_url' => '/races/' . rawurlencode((string) ($row['state_slug'] ?? '')) . '/senate/' . date('Y'),
                'next_election_date' => $row['next_election_date'] ?: null,
                'next_election_label' => $this->buildStateElectionLabel($row),
                'offices' => $this->parseOffices((string) ($row['offices'] ?? '')),
            ];
        }

        return $cards;
    }

    private function buildStateElectionLabel(array $row): ?string
    {
        $date = $row['next_election_date'] ?? null;
        $type = $row['election_type'] ?? null;

        if (!$date || !$type) {
            return null;
        }

        $formattedDate = $this->formatDate((string) $date);
        $formattedType = $this->formatElectionType((string) $type);

        return trim($formattedDate . ' · ' . $formattedType);
    }

    private function buildEventLabel(string $stateName, string $electionType): string
    {
        $stateName = trim($stateName);
        $typeLabel = $this->formatElectionType($electionType);

        if ($stateName === '') {
            return $typeLabel;
        }

        if ($typeLabel === '') {
            return $stateName;
        }

        return $stateName . ' ' . $typeLabel;
    }

    private function formatElectionType(string $value): string
    {
        return match (strtolower(trim($value))) {
            'primary' => 'Primary',
            'general' => 'General',
            'runoff' => 'Runoff',
            'special' => 'Special',
            'special-primary' => 'Special Primary',
            'jungle primary' => 'Jungle Primary',
            'ranked-choice general' => 'Ranked Choice General',
            default => trim($value),
        };
    }

    private function buildEventUrl(array $row): string
    {
        return sprintf(
            '/elections/%s/%s/%s',
            rawurlencode((string) ($row['state_slug'] ?? '')),
            rawurlencode((string) ($row['election_type_slug'] ?? '')),
            rawurlencode((string) ($row['election_date'] ?? ''))
        );
    }

    private function parseOffices(string $value): array
    {
        if ($value === '') {
            return [];
        }

        $parts = array_filter(array_map('trim', explode('||', $value)));

        return array_values(array_unique($parts));
    }

    private function formatDate(string $value): string
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

        if (!$date) {
            return $value;
        }

        return $date->format('M j, Y');
    }

    private function browseOffices(): array
    {
        return [
            'president' => 'President',
            'senate' => 'U.S. Senate',
            'house' => 'U.S. House',
            'governor' => 'Governor',
        ];
    }
}
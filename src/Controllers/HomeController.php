<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ElectionRepository;

final class HomeController
{
    private ElectionRepository $electionRepository;

    public function __construct(?ElectionRepository $electionRepository = null)
    {
        $this->electionRepository = $electionRepository ?? new ElectionRepository();
    }

    public function index(): void
    {
        $nextElections = $this->normalizeElectionEvents(
            $this->electionRepository->getNextElectionEvents()
        );

        $upcomingElections = $this->normalizeElectionEvents(
            $this->electionRepository->getUpcomingElectionEvents(12)
        );

        $states = $this->normalizeStateRows(
            $this->electionRepository->getStatesWithNextElection()
        );

        render_view('home', [
            'pageTitle' => 'YouAreDone.org',
            'metaDescription' => 'Track the next elections, upcoming elections, and where to watch next across presidential, U.S. Senate, U.S. House, and governor contests.',
            'canonicalUrl' => absolute_url('/'),
            'nextElections' => $nextElections,
            'upcomingElections' => $upcomingElections,
            'states' => $states,
            'browseOffices' => $this->getBrowseOffices(),
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalizeElectionEvents(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            $stateName = trim((string) ($row['state_name'] ?? ''));
            $stateSlug = trim((string) ($row['state_slug'] ?? ''));
            $electionType = trim((string) ($row['election_type'] ?? ''));
            $electionTypeSlug = trim((string) ($row['election_type_slug'] ?? ''));
            $electionDate = $this->normalizeDate($row['election_date'] ?? null);

            $normalized[] = [
                'state_name' => $stateName,
                'state_slug' => $stateSlug,
                'election_type' => $electionType,
                'election_type_slug' => $electionTypeSlug,
                'election_type_label' => $this->formatElectionType($electionType),
                'election_date' => $electionDate,
                'event_label' => $this->buildEventLabel($stateName, $electionType),
                'event_url' => $this->buildEventUrl($stateSlug, $electionTypeSlug, $electionDate),
                'offices' => $this->normalizeOfficeList($row['offices'] ?? []),
            ];
        }

        return $normalized;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalizeStateRows(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            $stateName = trim((string) ($row['state_name'] ?? ''));
            $stateSlug = trim((string) ($row['state_slug'] ?? ''));
            $electionType = trim((string) ($row['election_type'] ?? ''));
            $electionTypeSlug = trim((string) ($row['election_type_slug'] ?? ''));
            $electionDate = $this->normalizeDate($row['next_election_date'] ?? null);

            $normalized[] = [
                'state_name' => $stateName,
                'state_slug' => $stateSlug,
                'next_election_date' => $electionDate,
                'election_type' => $electionType,
                'election_type_slug' => $electionTypeSlug,
                'election_type_label' => $electionType !== ''
                    ? $this->formatElectionType($electionType)
                    : null,
                'event_label' => $electionDate !== null
                    ? $this->buildEventLabel($stateName, $electionType)
                    : null,
                'event_url' => $this->buildEventUrl($stateSlug, $electionTypeSlug, $electionDate),
                'offices' => $this->normalizeOfficeList($row['offices'] ?? []),
            ];
        }

        return $normalized;
    }

    /**
     * @param string|array<int, mixed>|null $value
     * @return array<int, string>
     */
    private function normalizeOfficeList(string|array|null $value): array
    {
        $items = [];

        if (is_array($value)) {
            $items = $value;
        } elseif (is_string($value) && $value !== '') {
            $items = explode('||', $value);
        }

        $normalized = [];

        foreach ($items as $item) {
            $office = trim((string) $item);

            if ($office === '') {
                continue;
            }

            $normalized[$office] = $office;
        }

        return array_values($normalized);
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $date = trim($value);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1
            ? $date
            : null;
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

    private function buildEventUrl(string $stateSlug, string $electionTypeSlug, ?string $electionDate): ?string
    {
        if ($stateSlug === '' || $electionTypeSlug === '' || $electionDate === null) {
            return null;
        }

        return '/elections/' . rawurlencode($stateSlug) . '/' . rawurlencode($electionTypeSlug) . '/' . rawurlencode($electionDate);
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

    /**
     * @return array<int, array<string, string>>
     */
    private function getBrowseOffices(): array
    {
        return [
            [
                'name' => 'President',
                'slug' => 'president',
            ],
            [
                'name' => 'U.S. Senate',
                'slug' => 'us-senate',
            ],
            [
                'name' => 'U.S. House',
                'slug' => 'us-house',
            ],
            [
                'name' => 'Governor',
                'slug' => 'governor',
            ],
        ];
    }
}
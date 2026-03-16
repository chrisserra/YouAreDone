<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\RaceRepository;

final class RaceController
{
    private RaceRepository $raceRepository;

    public function __construct(?RaceRepository $raceRepository = null)
    {
        $this->raceRepository = $raceRepository ?? new RaceRepository();
    }

    public function show(string $stateSlug, string $officeSlug, int $year, ?int $district = null): void
    {
        $data = $this->raceRepository->getRacePage(
            $stateSlug,
            $officeSlug,
            $year,
            $district
        );

        if (!$data || empty($data['race'])) {
            not_found($_SERVER['REQUEST_URI'] ?? '/');
            return;
        }

        $race = $data['race'];
        $elections = $data['elections'] ?? [];

        render_view('race/show', [
            'pageTitle' => $this->buildPageTitle($race),
            'metaDescription' => $this->buildMetaDescription($race),
            'canonicalUrl' => $this->buildRaceCanonicalUrl($race),
            'race' => $race,
            'elections' => $elections,
        ]);
    }

    private function buildPageTitle(array $race): string
    {
        $parts = [
            trim((string)($race['state_name'] ?? '')),
            trim((string)($race['office_name'] ?? '')),
            isset($race['election_year']) ? (string)$race['election_year'] : '',
        ];

        if ($this->hasDistrict($race)) {
            $parts[] = 'District ' . (int)$race['district_number'];
        }

        return trim(implode(' ', array_filter($parts))) . ' Race';
    }

    private function buildMetaDescription(array $race): string
    {
        $parts = [
            trim((string)($race['state_name'] ?? '')),
            trim((string)($race['office_name'] ?? '')),
            isset($race['election_year']) ? (string)$race['election_year'] : '',
        ];

        if ($this->hasDistrict($race)) {
            $parts[] = 'District ' . (int)$race['district_number'];
        }

        return trim(implode(' ', array_filter($parts))) . ' candidates, rankings, and election details.';
    }

    private function buildRaceCanonicalUrl(array $race): string
    {
        $path = '/races/'
            . rawurlencode((string)($race['state_slug'] ?? ''))
            . '/'
            . rawurlencode((string)($race['office_slug'] ?? ''))
            . '/'
            . rawurlencode((string)($race['election_year'] ?? ''));

        if ($this->hasDistrict($race)) {
            $path .= '/district-' . (int)$race['district_number'];
        }

        return absolute_url($path);
    }

    private function hasDistrict(array $race): bool
    {
        return ($race['district_type'] ?? '') === 'congressional_district'
            && (int)($race['district_number'] ?? 0) > 0;
    }
}
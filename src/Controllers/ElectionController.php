<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\CandidateRepository;
use App\Repositories\ElectionRepository;

final class ElectionController
{
    private ElectionRepository $electionRepository;
    private CandidateRepository $candidateRepository;

    public function __construct(
        ?ElectionRepository $electionRepository = null,
        ?CandidateRepository $candidateRepository = null
    ) {
        $this->electionRepository = $electionRepository ?? new ElectionRepository();
        $this->candidateRepository = $candidateRepository ?? new CandidateRepository();
    }

    public function show(string $stateSlug, string $electionTypeSlug, string $electionDate): void
    {
        $event = $this->electionRepository->getElectionEventDetail(
            $stateSlug,
            $electionTypeSlug,
            $electionDate
        );

        if (!$event) {
            not_found($_SERVER['REQUEST_URI'] ?? '/');
            return;
        }

        $racesByOffice = $this->electionRepository->getEventRacesByOffice(
            $stateSlug,
            $electionTypeSlug,
            $electionDate
        );

        $racesByOffice = $this->attachCandidatePreviews($racesByOffice);

        render_view('election/show', [
            'pageTitle' => $event['event_label'] ?? 'Election',
            'metaDescription' => $event['event_label'] ?? 'Election details',
            'canonicalUrl' => absolute_url(
                '/elections/' . $stateSlug . '/' . $electionTypeSlug . '/' . $electionDate
            ),
            'event' => $this->normalizeEvent($event),
            'racesByOffice' => $racesByOffice,
        ]);
    }

    /**
     * @param array<string, mixed> $event
     * @return array<string, mixed>
     */
    private function normalizeEvent(array $event): array
    {
        $event['offices'] = $this->normalizeOfficeList($event['offices'] ?? []);

        return $event;
    }

    /**
     * @param array<string, array<int, array<string, mixed>>> $racesByOffice
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function attachCandidatePreviews(array $racesByOffice): array
    {
        $candidateIds = [];

        foreach ($racesByOffice as $officeName => $races) {
            foreach ($races as $index => $race) {
                $electionId = (int) ($race['election_id'] ?? 0);
                $raceId = (int) ($race['race_id'] ?? 0);

                $candidates = $electionId > 0
                    ? $this->candidateRepository->getHomepageElectionCandidatePreview($electionId, 100)
                    : ($raceId > 0
                        ? $this->candidateRepository->getHomepageRaceCandidatePreview($raceId, 100)
                        : []);

                $racesByOffice[$officeName][$index]['candidates'] = $candidates;

                foreach ($candidates as $candidate) {
                    $candidateId = (int) ($candidate['candidate_id'] ?? 0);

                    if ($candidateId > 0) {
                        $candidateIds[$candidateId] = $candidateId;
                    }
                }
            }
        }

        $previewFlagsMap = $this->candidateRepository->getCandidatePreviewReasonGroupsMap(
            array_values($candidateIds),
            100
        );

        foreach ($racesByOffice as $officeName => $races) {
            foreach ($races as $index => $race) {
                $candidates = $racesByOffice[$officeName][$index]['candidates'] ?? [];

                foreach ($candidates as $candidateIndex => $candidate) {
                    $candidateId = (int) ($candidate['candidate_id'] ?? 0);

                    $candidates[$candidateIndex]['preview_flags'] = $previewFlagsMap[$candidateId]
                        ?? ['green' => [], 'red' => []];
                }

                $racesByOffice[$officeName][$index]['candidates'] = $candidates;
            }
        }

        return $racesByOffice;
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
}
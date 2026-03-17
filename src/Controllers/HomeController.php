<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\CandidateRepository;
use App\Repositories\RaceRepository;

final class HomeController
{
    private RaceRepository $raceRepository;
    private CandidateRepository $candidateRepository;

    public function __construct(
        ?RaceRepository $raceRepository = null,
        ?CandidateRepository $candidateRepository = null
    ) {
        $this->raceRepository = $raceRepository ?? new RaceRepository();
        $this->candidateRepository = $candidateRepository ?? new CandidateRepository();
    }

    public function index(): void
    {
        $stats = $this->candidateRepository->getHomepageStats();
        $nextElection = $this->raceRepository->getHomepageNextElection();

        if ($nextElection !== null && !empty($nextElection['election_id'])) {
            $nextElection['candidate_preview'] = $this->candidateRepository->getHomepageElectionCandidatePreview(
                (int)$nextElection['election_id'],
                3
            );
        } else {
            $nextElection = null;
        }

        $upcomingElections = $this->raceRepository->getHomepageUpcomingElections(6);
        $mostWatchedRaces = $this->raceRepository->getHomepageMostWatchedRaces(4);
        $featuredRace = $this->raceRepository->getHomepageFeaturedRace();

        if ($featuredRace !== null && !empty($featuredRace['race_id'])) {
            $featuredRace['candidate_preview'] = $this->candidateRepository->getHomepageRaceCandidatePreview(
                (int)$featuredRace['race_id'],
                5
            );
        } else {
            $featuredRace = null;
        }

        $accountabilitySignals = $this->candidateRepository->getHomepageAccountabilitySignals(5);
        $latestUpdates = $this->candidateRepository->getHomepageLatestUpdates(8);
        $browseOffices = $this->raceRepository->getHomepageBrowseOffices();
        $browseStates = $this->raceRepository->getHomepageBrowseStates();

        $hero = [
            'title' => 'Election Watch Dashboard',
            'subtitle' => 'Track upcoming primaries, most watched races, and candidate accountability.',
            'stats' => [
                [
                    'label' => 'Active Races',
                    'value' => (int)($stats['activeRaces'] ?? 0),
                    'icon' => 'fa-landmark',
                ],
                [
                    'label' => 'Upcoming Elections',
                    'value' => (int)($stats['upcomingElections'] ?? 0),
                    'icon' => 'fa-calendar-day',
                ],
                [
                    'label' => 'Tracked Candidates',
                    'value' => (int)($stats['trackedCandidates'] ?? 0),
                    'icon' => 'fa-users',
                ],
                [
                    'label' => 'Documented Flags',
                    'value' => (int)($stats['documentedFlags'] ?? 0),
                    'icon' => 'fa-flag',
                ],
            ],
        ];

        $methodology = [
            'trackedOffices' => [
                'President',
                'U.S. Senate',
                'U.S. House',
                'Governor',
            ],
            'trackedElectionTypes' => [
                'primary',
                'general',
                'runoff',
                'special',
                'special-primary',
                'jungle primary',
                'ranked-choice general',
            ],
            'sourcePolicy' => 'Candidate records are documented using publicly sourced information.',
        ];

        render_view('home/index', [
            'pageTitle' => 'YouAreDone.org',
            'metaDescription' => 'Track upcoming primaries, watched races, and candidate accountability.',
            'canonicalUrl' => absolute_url('/'),
            'ogImage' => absolute_url('/assets/images/og-default.png'),

            'hero' => $hero,
            'nextElection' => $nextElection,
            'upcomingElections' => $upcomingElections,
            'mostWatchedRaces' => $mostWatchedRaces,
            'accountabilitySignals' => $accountabilitySignals,
            'featuredRace' => $featuredRace,
            'latestUpdates' => $latestUpdates,
            'browseOffices' => $browseOffices,
            'browseStates' => $browseStates,
            'methodology' => $methodology,
        ]);
    }
}
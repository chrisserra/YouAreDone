<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\CandidateRepository;

final class CandidateController
{
    private CandidateRepository $candidateRepository;

    public function __construct(?CandidateRepository $candidateRepository = null)
    {
        $this->candidateRepository = $candidateRepository ?? new CandidateRepository();
    }

    public function show(string $slug): void
    {
        $data = $this->candidateRepository->getCandidatePage($slug);

        if (!$data || empty($data['candidate'])) {
            not_found($_SERVER['REQUEST_URI'] ?? '/');
            return;
        }

        $candidate = $data['candidate'];
        $candidateFlags = $data['candidate_flags'] ?? [];
        $electionFlags = $data['election_flags'] ?? [];
        $history = $data['history'] ?? [];

        $fullName = trim((string)($candidate['full_name'] ?? 'Candidate'));
        $candidateSlug = (string)($candidate['slug'] ?? $slug);

        render_view('candidate/show', [
            'pageTitle' => $fullName . ' Candidate Profile',
            'metaDescription' => $fullName . ' candidate profile, rankings, flags, and election history.',
            'canonicalUrl' => absolute_url('/candidate/' . rawurlencode($candidateSlug)),
            'candidate' => $candidate,
            'candidateFlags' => $candidateFlags,
            'electionFlags' => $electionFlags,
            'history' => $history,
        ]);
    }
}
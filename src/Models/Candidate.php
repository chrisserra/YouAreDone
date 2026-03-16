<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Helpers\Slug;
use App\Helpers\Text;
use InvalidArgumentException;

final class Candidate extends Model
{
    protected string $table = 'candidates';

    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM candidates WHERE slug = :slug LIMIT 1",
            ['slug' => $slug]
        );
    }

    public function findByFullName(string $fullName): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM candidates WHERE full_name = :full_name LIMIT 1",
            ['full_name' => Text::normalizeWhitespace($fullName)]
        );
    }

    public function create(array $data): int
    {
        $firstName     = Text::normalizeWhitespace((string)($data['first_name'] ?? ''));
        $middleName    = isset($data['middle_name']) ? Text::normalizeWhitespace((string)$data['middle_name']) : null;
        $lastName      = Text::normalizeWhitespace((string)($data['last_name'] ?? ''));
        $suffix        = isset($data['suffix']) ? Text::normalizeWhitespace((string)$data['suffix']) : null;
        $partyCode     = $data['party_code'] ?? null;
        $homeStateCode = isset($data['home_state_code']) ? strtoupper(trim((string)$data['home_state_code'])) : null;
        $isIncumbent   = !empty($data['is_incumbent']) ? 1 : 0;
        $status        = $data['status'] ?? null;
        $websiteUrl    = $data['website_url'] ?? null;
        $photoUrl      = $data['photo_url'] ?? null;
        $bio           = $data['bio'] ?? null;

        if ($firstName === '' || $lastName === '') {
            throw new InvalidArgumentException('Candidate first_name and last_name are required.');
        }

        $fullNameParts = array_filter([$firstName, $middleName, $lastName, $suffix], static fn ($v) => $v !== null && $v !== '');
        $fullName = implode(' ', $fullNameParts);

        $existing = $this->findByFullName($fullName);
        if ($existing) {
            return (int)$existing['id'];
        }

        $baseSlug = Slug::buildCandidateSlug($fullName);
        $slug = Slug::unique($this->db, 'candidates', 'slug', $baseSlug);

        $sql = "
            INSERT INTO candidates (
                first_name,
                middle_name,
                last_name,
                suffix,
                full_name,
                slug,
                party_code,
                home_state_code,
                is_incumbent,
                status,
                website_url,
                photo_url,
                bio,
                created_at,
                updated_at
            ) VALUES (
                :first_name,
                :middle_name,
                :last_name,
                :suffix,
                :full_name,
                :slug,
                :party_code,
                :home_state_code,
                :is_incumbent,
                :status,
                :website_url,
                :photo_url,
                :bio,
                NOW(),
                NOW()
            )
        ";

        return $this->insert($sql, [
            'first_name'      => $firstName,
            'middle_name'     => $middleName,
            'last_name'       => $lastName,
            'suffix'          => $suffix,
            'full_name'       => $fullName,
            'slug'            => $slug,
            'party_code'      => $partyCode,
            'home_state_code' => $homeStateCode,
            'is_incumbent'    => $isIncumbent,
            'status'          => $status,
            'website_url'     => $websiteUrl,
            'photo_url'       => $photoUrl,
            'bio'             => $bio,
        ]);
    }
}
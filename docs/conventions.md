# YouAreDone Data Conventions

## Public site rules
- Never mention AI anywhere publicly on the site.
- AI may be used internally only for research and draft data generation.

## Supported offices
Only use these offices:
- President
- U.S. Senate
- U.S. House
- Governor

## Supported election types
Only use these election types:
- primary
- general
- runoff
- special
- special-primary
- jungle primary
- ranked-choice general

## URL patterns
- /races/{state-slug}/{office-slug}/{year}
- /races/{state-slug}/{office-slug}/{year}/district-{district}
- /candidate/{candidate-slug}

## Slug rules
- Use kebab-case for all slugs.
- Use lowercase only.
- Words are separated by hyphens.
- No underscores.
- No spaces.
- Candidate slugs should usually be based on the candidate's commonly used full name.
- Office slugs must match existing office records in the database.
- Election slugs and race slugs must match existing project patterns.

## Candidate naming rules
- `full_name` should contain the public display name.
- Preserve suffixes like Jr., Sr., III when applicable.
- `preferred_name` may be used when relevant, but do not invent it.
- Do not create duplicate candidates if a likely existing match is already present.

## Race rules
- Before creating a new race, check whether it already exists in `races`.
- For U.S. House races, use district-based routing and district numbers.
- For statewide offices, district should be 0 and statewide routing rules should apply.

## Election rules
- Before creating a new election, check whether it already exists in `elections`.
- `round_number` should match the intended sequence for the race.
- Election type must map to an existing `election_types` row.

## Election candidate rules
- Before inserting into `election_candidates`, check whether the candidate is already attached to that election.
- `ballot_name` should match the actual ballot or public filing name when available.
- Do not mark `is_incumbent` unless it is clearly true.
- Do not mark `is_major_candidate` unless it is justified.

## Flag rules
- Candidate score = green flag weights minus red flag weights.
- `flag_color` must be either `green` or `red`.
- Flag slugs must use kebab-case.
- Do not create duplicate flags for the same concept.
- Prefer integer weights using the current project scale.
- Before adding a new flag, check whether a similar flag already exists.

## Data generation workflow
When generating updates:
1. Check `docs/schema.sql` for structure.
2. Check `docs/lookup-seeds.sql` for lookup/reference data.
3. Check `docs/current-data.sql` for existing rows before proposing inserts or updates.
4. Generate only missing or changed data when possible.
5. Keep SQL portable and consistent with existing naming and slug patterns.

## Collaboration preference
- Work step by step.
- Pause after each step.
- Wait for verification before moving to the next step.
- Include this preference in any starter prompt for a new chat about this project.
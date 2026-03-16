I am working on YouAreDone.org.

Before making any schema, data, insert, update, slug, flag, race, election, or candidate recommendations, first use these repo files as the source of truth:

- docs/schema.sql
- docs/lookup-seeds.sql
- docs/current-data.sql
- docs/conventions.md

Rules:
- Never mention AI anywhere publicly on the site.
- Follow docs/conventions.md exactly.
- Check whether data already exists before proposing inserts.
- Reuse existing slug and naming patterns.
- Generate only missing or changed rows when possible.
- Work step by step.
- Pause after each step.
- Wait for my verification before moving to the next step.

When helping with election or candidate updates:
1. Check current schema and conventions first.
2. Check current data first.
3. Identify what already exists.
4. Propose only the missing inserts or necessary updates.
5. Separate inserts, updates, and cleanup clearly.

If I ask for code changes, first identify the exact file to edit, then give one step at a time and wait for verification.
# Phase 1 — CV Profile and Versioning

Status: `planned`  
**Depends on:** Phase 0

## Outcome

A user can create, edit, view, and version a structured CV. Versions are
immutable snapshots and ownership is enforced server-side.

## Data design

Add `cv_profiles` with `user_id`, `title`, `base_data`, and `schema_version`.
Add `cv_versions` with `cv_profile_id`, `version_name`, `data`,
`schema_version`, `source`, and `created_by`. Index foreign keys and enforce
ownership through Policies. Keep the JSON contract in a versioned DTO or
validation class rather than accepting arbitrary JSON.

The initial sections are `personal_info`, `summary`, `skills`, `education`,
`experience`, `projects`, `certificates`, `languages`, and `activities`.

## API and frontend design

Implement the profile/version endpoints from the parent plan under
`/api/v1`. Use Form Requests for nested validation and API Resources for
responses. The Vue editor owns draft form state; saved versions are read-only.
Add profile list, editor, version list, and version detail views.

## Work sequence

1. Add migrations, models, relations, factories, and seed data.
2. Add requests, resources, policies, and profile/version services.
3. Add API routes/controllers and ownership tests.
4. Add TypeScript contract, API store, editor, and version views.
5. Verify snapshot immutability and update documentation.

## Verification

```powershell
cd apps/api; php artisan migrate:fresh --seed; php artisan test
cd ../web; npm run type-check; npm run build; npm run lint
```

## Checkpoint / exit criteria

- A user can save and reload a structured CV.
- Invalid nested data is rejected with field-level errors.
- A later save cannot mutate an earlier immutable version.
- A user cannot access another user's profile or version.

See [`github-issues.md`](github-issues.md).

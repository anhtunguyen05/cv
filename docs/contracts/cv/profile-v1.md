# CV Profile Contract v1

Owner: Epic 1, Stories 1.3 through 1.7 (`E1-COORD-PROFILE-001`)

Status: approved baseline, 2026-10-06. This is the stable source for the
Profile schema and HTTP boundary. Story fixture tasks turn this contract into
shared executable JSON; they do not redefine it.

## Resource

```json
{
  "id": "01J...ULID",
  "title": "Software Engineer",
  "revision": 1,
  "personal_information": {
    "full_name": "Nguyen Anh Tu",
    "headline": "Frontend engineer",
    "email": "tu@example.test",
    "phone": "+84 901 234 567",
    "location": "Ho Chi Minh City, Vietnam",
    "website_url": "https://example.test",
    "linkedin_url": "https://linkedin.com/in/example",
    "github_url": "https://github.com/example"
  },
  "summary": null,
  "skills": [],
  "education": [],
  "experience": [],
  "projects": [],
  "certificates": [],
  "languages": [],
  "activities": [],
  "created_at": "2026-10-06T00:00:00Z",
  "updated_at": "2026-10-06T00:00:00Z"
}
```

`id` and every repeatable item `id` are server-generated canonical 26-character
uppercase Crockford Base32 ULIDs. Lowercase or malformed route/item IDs are
invalid; malformed item IDs are field validation failures, while malformed,
absent, and foreign route IDs all receive `404 RESOURCE_NOT_FOUND`. The
owner identifier, internal schema version, idempotency key, and security
metadata are never returned. Responses are `Cache-Control: private, no-store`.

## Fields and validation

All strings use Unicode NFC and are trimmed of outer whitespace. Required
strings reject an empty result. Limits count Unicode grapheme clusters; each maximum also
has a UTF-8 byte ceiling of four times that number. Multiline text normalizes
CRLF to LF. Internal whitespace is preserved. Text is plain text, not markup:
C0 controls and Unicode format characters are rejected. LF is allowed only in
`summary`, `description`, and `highlights`; all other strings are single-line.
Renderers must context-encode text. Absolute URLs allow only `http` or `https`; rendered
external links use `rel="noopener noreferrer"`. Dates use `YYYY-MM` and must
fall from `1900-01` through `2100-12`.

| Field | Shape and limits |
| --- | --- |
| `title` | Required; 1–120 graphemes/480 bytes; unique per owner after the stated NFC/trim normalization; at most 10 Profiles per owner. |
| `personal_information` | Required object on create. `full_name` required, 1–120. Optional `headline` 1–160, `email` valid email up to 254, `phone` 3–32, `location` 1–160, and the three URL fields up to 2048. |
| `summary` | `null` or 1–2,000 characters. |
| `skills` | Up to 12 categories. A category is `{id,label,items}`; `label` is 1–60 and unique per Profile after the stated normalization. `items` has 1–30 `{id,name}` entries, each 1–80, no duplicate normalized name in its category. Total skill items may not exceed 100. |
| `education` | Up to 30 `{id,institution,degree,field_of_study?,location?,start_date?,end_date?,description?}` entries. `institution` and `degree` are 1–160; optional text fields are 1–160 except `description` 1–2,000. `end_date` requires `start_date` and cannot precede it. |
| `experience` | Up to 30 `{id,organization,role,employment_type?,location?,start_date,end_date?,is_current,highlights}` entries. `organization` and `role` are 1–160; `employment_type` is `full_time`, `part_time`, `contract`, `internship`, `freelance`, or `other`; `is_current` is required boolean; `end_date` is `null` when current and required otherwise; `highlights` has 0–10 strings of 1–400. |
| `projects` | Up to 30 `{id,name,role?,url?,start_date?,end_date?,technologies,highlights}` entries. `name` is 1–160; optional `role` 1–160; `end_date` requires `start_date` and cannot precede it; `technologies` has 0–30 unique normalized 1–80 strings; `highlights` has 0–10 strings of 1–400. |
| `certificates` | Up to 30 `{id,name,issuer,issued_on?,expires_on?,credential_url?}` entries. `name` and `issuer` are 1–160; `expires_on` requires `issued_on` and cannot precede it. |
| `languages` | Up to 20 `{id,language,proficiency}` entries. `language` is 1–80 and unique per Profile after the stated normalization; `proficiency` is `native`, `fluent`, `professional_working`, `limited_working`, or `elementary`. |
| `activities` | Up to 30 `{id,name,role?,organization?,start_date?,end_date?,description?}` entries. `name` is 1–160; optional role/organization are 1–160; optional description is 1–2,000; `end_date` requires `start_date` and cannot precede it. |

All listed collections may be empty. In the table, `?` means the key is always
present in an API resource and is `null` when no value exists. A complete
replacement request must include every key in its object or section, use `null`
to clear an optional scalar, and include no omitted required or additional
keys. Array order
is request order, preserved on reload and in snapshots.

The create payload is exactly `{title,personal_information}`. Its
`personal_information` value contains all eight documented keys: `full_name`
is a non-null string and every other key is either a valid string or `null`.
Additional or omitted keys fail validation. The same object representation
applies to a complete personal-information replacement.

## HTTP operations

All routes are under `/api/v1`, use the common envelope, require Sanctum
session authentication, and return the non-disclosing `404 RESOURCE_NOT_FOUND`
for absent or foreign Profile/item access.

| Operation | Route | Request | Result |
| --- | --- | --- | --- |
| Create | `POST /cv-profiles` | `{title,personal_information}`; required `Idempotency-Key` UUID | `201 data: Profile`; repeat of the same key returns the original result for 24 hours. |
| List | `GET /cv-profiles?page=&per_page=` | `page` and `per_page` are positive integers; default 1/20, maximum 100 | Owner-only paginated collection, ordered `updated_at DESC, id DESC`. |
| Detail | `GET /cv-profiles/{profile}` | none | `200 data: Profile`. |
| Personal information | `PUT /cv-profiles/{profile}/personal-information` | `{personal_information:{...}}`; required `If-Match: "<revision>"` | `200 data: Profile` and `ETag: "<new-revision>"`. |
| Title | `PUT /cv-profiles/{profile}/title` | `{title:"..."}`; required `If-Match` | `200 data: Profile` and `ETag`. |
| Section replacement | `PUT /cv-profiles/{profile}/{section}` where section is `summary`, `skills`, `education`, `experience`, `projects`, `certificates`, `languages`, or `activities` | Exactly `{section:<complete value>}`; required `If-Match` | `200 data: Profile` and `ETag`. |

Section replacement changes only the named section, happens in one transaction,
and increments `revision` by one. For a repeatable section, removal is explicit
because the caller submits that section's complete next list; omitted sibling
sections are never modified. Existing items retain their IDs; a supplied item
ID must be a unique valid ULID belonging to the same Profile and section, while
a new item omits `id`; otherwise validation fails without disclosing ownership.
UI uses manual save—there is no background autosave.

`If-Match` is exactly one strong quoted positive decimal revision, for example
`"7"`; missing, weak, wildcard, multi-value, or malformed values return
`422 VALIDATION_FAILED` under `details.if_match`. A valid but stale revision
returns `409 PROFILE_UPDATE_CONFLICT` without changing state. The client keeps local
edits, reloads, and lets the User reconcile; it never automatically retries.
Invalid fields return `422 VALIDATION_FAILED` with the exact field path, such
as `projects.2.highlights.1`. A duplicate title uses the same response with a
generic `title` field error. A lost update response is reconciled by retrieving
the Profile and comparing the intended section/revision before offering a
manual retry.

## Idempotency and persistence boundary

Create operations use an owner-and-operation-scoped idempotency ledger with a
unique `(user_id, operation, key)` index, original status/response, and 24-hour
expiry. `Idempotency-Key` is exactly one lowercase canonical UUID v4 value.
The request hash is SHA-256 of the RFC 8785 canonical JSON request body plus a
newline and its canonical route path. The first request, ledger row, and created
resource commit together. A concurrent or later unexpired same-key/same-hash
request returns the original response without a new mutation; a hash mismatch
returns `409 IDEMPOTENCY_KEY_REUSED`. An expired row is deleted under the same
unique-key lock before a new first request may insert its replacement.

The current aggregate is one Profile row: explicit `id`, `user_id`, `title`,
normalized title, `revision`, `schema_version` (`1.0`), `created_at`, and
`updated_at`, plus a `ProfileDocumentV1` JSONB document with exactly
`personal_information`, `summary`, `skills`, `education`, `experience`,
`projects`, `certificates`, `languages`, and `activities`. Row identity,
revision, schema version, timestamps, and title are never duplicated in JSONB.
PostgreSQL enforces the owner/normalized-title
unique key, ownership foreign key, positive revision, and lookup indexes. A
Profile create takes a transaction-scoped advisory lock derived from `user_id`,
recounts the owner's rows, and returns `422 VALIDATION_FAILED` at
`details.profiles` when the cap is reached. Laravel validates every nested
field and controls all transactions. Future readers add an explicit supported
document schema; stored `1.0` documents are never silently reinterpreted.
If the preliminary scaffold migration has been applied anywhere, implementation
uses a forward-only migration; it does not rewrite an applied migration.

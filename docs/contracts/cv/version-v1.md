# CV Version Contract v1

Owner: Epic 1, Story 1.8 (`E1-COORD-VERSION-001`)

Status: approved baseline, 2026-10-06. This is the stable source for Version
creation, immutable storage, and reads.

## Resource and snapshot

```json
{
  "id": "01J...ULID",
  "name": "Backend-focused application",
  "source_profile_id": "01J...ULID",
  "source_profile_revision": 7,
  "snapshot_schema_version": "1.0",
  "snapshot": {
    "title": "Software Engineer",
    "personal_information": { "full_name": "Nguyen Anh Tu" },
    "summary": null,
    "skills": [],
    "education": [],
    "experience": [],
    "projects": [],
    "certificates": [],
    "languages": [],
    "activities": []
  },
  "created_at": "2026-10-06T00:00:00Z"
}
```

The snapshot is one complete copy of the Profile v1 document: `title`,
`personal_information`, `summary`, and every listed section, including null
optional fields, nested item ULIDs, and collection order. It excludes Profile
`id`, `revision`, owner, schema version, and timestamps because those have
their own Version/source fields. `snapshot_schema_version` is `1.0` in Epic 1.
Readers support only that version. Support for a later schema requires an
explicit reader and never rewrites historical semantic content or `created_at`.

## HTTP operations

All routes require the current Sanctum session, use the common envelope, and
set `Cache-Control: private, no-store`.

| Operation | Route | Request | Result |
| --- | --- | --- | --- |
| Create | `POST /cv-profiles/{profile}/versions` | Exactly `{name}` with required `If-Match: "<revision>"` and `Idempotency-Key` UUID | `201 data: Version`; the same key returns the original result for 24 hours. |
| List | `GET /cv-versions?page=&per_page=&profile_id=` | `page`/`per_page` positive integers, default 1/20, maximum 100; optional valid owned ULID `profile_id` | Owner-only page under the common `data`/`meta`/`links` contract, ordered `created_at DESC, id DESC`; links preserve filter. |
| Detail | `GET /cv-versions/{version}` | none | `200 data: Version` from stored snapshot only. |

`name` is Unicode NFC, outer-whitespace trimmed, and 1–120 graphemes/480 UTF-8
bytes. Names
need not be unique: recreating an intentionally named snapshot is valid, but a
retry with the same idempotency key must not create another row. A malformed or
foreign `profile_id` filter returns `404 RESOURCE_NOT_FOUND`. No Version update
or delete route exists in Epic 1.

Creation starts an explicit transaction, locks the owned Profile, verifies the
`If-Match` grammar and error responses defined in Profile v1, verifies that
`title` and `personal_information.full_name` are valid, constructs the named
`SnapshotProfileV1` projection defined above from the row title and locked
`ProfileDocumentV1`, and inserts one new ULID Version with
`source_profile_revision`. A concurrent Profile update
either occurs before the lock and makes the request stale (`409
PROFILE_UPDATE_CONFLICT`), or waits until the snapshot commits; a mixed
snapshot is impossible. An absent or foreign Profile/Version returns the same
`404 RESOURCE_NOT_FOUND` and creates nothing. A valid but non-versionable
Profile returns `409 PROFILE_NOT_VERSIONABLE` with safe field paths.

## Idempotency and persistence boundary

Version creation uses the Profile v1 idempotency ledger under operation
`create-version`. Its request hash is SHA-256 of the RFC 8785 canonical JSON
body, a newline, the canonical route path, a newline, and the exact required
`If-Match` value; therefore a changed precondition cannot replay a former
Version response.
The ledger row and Version insert commit in the same transaction. A same-key,
same-hash retry returns the original Version even after its Profile changes; a
different hash returns `409 IDEMPOTENCY_KEY_REUSED`.

One Version row persists explicit `id`, `user_id`, `source_profile_id`,
`source_profile_revision`, `name`, `snapshot_schema_version`, `snapshot` JSONB,
and `created_at`; it has no `updated_at`. PostgreSQL enforces the ownership and
source foreign keys and list indexes, plus `snapshot_hash` as SHA-256 of the
RFC 8785 canonical JSON snapshot. Application code has no update path and
tests prove later Profile writes cannot affect Version semantic JSON or hash.
The source Profile foreign key uses `ON DELETE RESTRICT`; Epic 1 has no Profile
delete operation. A PostgreSQL trigger rejects every Version `UPDATE` and
`DELETE` after insert, and migration tests prove the guard.
If the preliminary multi-column snapshot migration has been applied,
implementation adds a forward-only migration to this contract rather than
editing history.

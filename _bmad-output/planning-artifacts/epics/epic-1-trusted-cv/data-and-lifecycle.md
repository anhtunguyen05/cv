# Epic 1 Data and Lifecycle

## Identity and ownership

- **E1-DATA-001:** Existing Laravel `users.id` remains bigint; its public API
  serialization is fixed by `E1-DEC-002`.
- **E1-DATA-002:** CV Profile, repeatable Profile items, and CV Version use
  server-generated ULIDs under `DATA-STD-002`.
- **E1-DATA-003:** Every Profile and Version persists an indexed owner
  relationship to the existing User identity. Child ownership is resolved
  through its aggregate, not accepted from the request.
- **E1-DATA-004:** MySQL 8.4 constraints are part of the contract. SQLite-only
  success does not prove uniqueness, collation, JSON, migration, or transaction
  behavior.

## Lifecycle models

### E1-STATE-AUTH-001

```text
unauthenticated --register/sign-in--> authenticated
authenticated --sign-out-----------> unauthenticated
authenticated --expiry/revocation--> unauthenticated
```

An auth transition never mutates Profile or Version state. A lost response or
expired CSRF/session state follows the approved reconciliation/recovery rule.

### E1-STATE-PROFILE-001

```text
absent --create(valid aggregate)--> mutable Profile
mutable Profile --approved update--> same Profile identity, new mutable state
mutable Profile --invalid/stale update--> previous state preserved
```

The canonical stories do not require Profile deletion. No task may add it
without a separate approved story.

### E1-STATE-VERSION-001

```text
valid owned Profile --create snapshot--> immutable Version
immutable Version --read/list---------> unchanged
immutable Version --update/delete-----> unsupported in Epic 1
```

## Persistence invariants

- **E1-DATA-005:** Profile aggregate writes and Version snapshot creation use
  explicit Laravel transaction boundaries.
- **E1-DATA-006:** Database uniqueness/foreign-key constraints back application
  checks; concurrency conflicts map to safe contract outcomes.
- **E1-DATA-007:** Structured fields are validated before persistence. JSON is
  acceptable only for intentionally variant nested structures with a versioned
  schema; ownership, identity, lifecycle, and lookup fields remain explicit.
- **E1-DATA-008:** A Version stores its complete snapshot and schema version at
  creation; later reads never join mutable Profile section rows to synthesize
  historic content.
- **E1-DATA-009:** Timestamps are stored in UTC; ordering does not rely on a
  timestamp alone when two records can share a timestamp value.

## Open data design

`E1-DEC-003` and `E1-DEC-004` must freeze the Profile schema, relational/JSON
split, nested item fields, limits, and canonicalization. `E1-DEC-005` must
freeze optimistic concurrency and write granularity. `E1-DEC-006` must freeze
the snapshot schema/versioning and name policy.

# Epic 2 Data and Lifecycle

## Ownership and identity

| Record | Identity | Owner/source rule | Mutability |
| --- | --- | --- | --- |
| Job Description | ULID | Directly owned by authenticated User | Stable logical resource; deletion marker and current pointer may change |
| Job Description Revision | ULID plus monotonic revision number | Child of one owned Job Description | Immutable |
| Analysis | ULID plus revision/rule deterministic key | Pins one owned revision | Immutable successful result |
| Match Report | ULID | Pins one owned CV Version, logical JD, revision, and Analysis | Immutable |

Database ownership/lifecycle/source fields are explicit indexed columns. JSON
is reserved for validated variable signals and report details; it does not hide
owner IDs, deletion state, source IDs, rule versions, or deterministic keys.

## E2-DATA-001 — Job Description aggregate

- Store owner identity, logical ULID, current revision ID, deletion timestamp,
  and timestamps on the aggregate root.
- Store raw text and optional role/company values on immutable revisions so an
  old source remains reproducible.
- Creation writes root plus first revision and current pointer atomically.
- Update inserts one revision and advances the current pointer under an
  approved concurrency precondition.

## E2-DATA-002 — Analysis record

- Store exact revision ID, Analysis schema version, analysis-rule version,
  normalized validated signals, and creation timestamp.
- Enforce the approved uniqueness/deduplication key at database and application
  boundaries.
- A failure does not produce a successful Analysis row with missing content.
  Optional attempt telemetry, if approved, is a separate operational record.

## E2-DATA-003 — Match Report record

- Store the owning User identity and every source/rule/schema identifier
  required by `E2-BR-012` alongside a validated immutable result.
- Match creation resolves ownership, non-deleted current revision, successful
  Analysis, and CV Version identity within one consistency boundary.
- A repeated request follows the approved deduplication policy but never
  rewrites an existing report.

## Job Description lifecycle

```text
create request
  -> logical JD + immutable revision 1 (current, active)
  -> valid update + concurrency check
       -> immutable revision N+1 (current, active)
       -> older revisions remain historical
  -> logical delete
       -> hidden from active list
       -> no edit/analyze/new match
       -> pinned historical analysis/report remains owner-readable
```

## Analysis lifecycle

```text
current active revision
  -> analyze request
       -> success: immutable Analysis(revision_id, analysis_rule_version)
       -> retryable failure: no successful partial record; safe retry offered
       -> terminal failure: no successful partial record; actionable message
new JD revision -> no successful Analysis until explicitly requested
```

## Match Report lifecycle

```text
owned immutable CV Version
  + current active JD revision
  + successful Analysis for that exact revision
  -> deterministic match
       -> immutable Match Report with pinned IDs/rule versions
       -> later Profile/JD edits or JD deletion do not alter stored meaning
```

## Transaction and concurrency invariants

- No request leaves a root without revision, advances a pointer without the new
  revision, or stores a Match Report without every pinned source.
- Competing updates cannot both claim the same next revision or silently lose a
  User's changes.
- Analysis and matching read one consistent immutable source set.
- MySQL 8.4 integration tests must prove constraints, collation-sensitive
  normalization, ordering, rollback, and competing request behavior.

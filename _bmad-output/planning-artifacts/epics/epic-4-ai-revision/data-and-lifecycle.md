# Epic 4 Data and Lifecycle

## Ownership and identity

| Record | Identity/source | Mutability |
| --- | --- | --- |
| Evidence Interview | ULID; owned through User and pinned Match Report tuple | Status advances; source/area set immutable |
| Question | Stable ID plus question-set version and area | Immutable within a started session |
| Evidence Answer | ULID; Interview/question/User provenance | Original answer immutable; correction policy explicit |
| Patch | ULID; source Version/Match/Interview plus predecessor | State machine; proposal revisions preserve provenance |
| Result CV Version | New Epic 1 ULID plus source/Patch provenance | Immutable snapshot |
| Provider attempt/audit | Sanitized ID/versions/outcome/reference | Append-only under Epic 5 policy |

JSON may store validated variable question/Patch content, but owner/source IDs,
status, idempotency keys, versions, timestamps, and lifecycle constraints remain
explicit indexed fields where required for integrity and queries.

## E4-DATA-001 — Interview and Evidence

- Starting resolves the owned Match Report and exact pinned source tuple in one
  transaction and snapshots unresolved areas/question-set version.
- Answer writes preserve User provenance and explicit non-supporting outcomes.
- Closing/expiry, answer correction, current question, concurrency, and context
  validity require `E4-DEC-002` and `E4-DEC-003`.

## E4-DATA-002 — Patch generation and lineage

- Provider attempt and validated Patch persistence are separate boundaries.
- Deterministic/idempotency keys include exact source, Evidence set, prompt,
  tool schema, provider/model, and regeneration predecessor as approved.
- A regenerated Patch links to but never rewrites its predecessor.

## E4-DATA-003 — Apply transaction

Inside one MySQL transaction with locking/preconditions:

1. Resolve owner and Patch status.
2. Revalidate schema, target, Evidence, source IDs, and exact old value.
3. Apply the allowlisted operation to an in-memory copy of the source snapshot.
4. Validate the complete new snapshot.
5. Insert exactly one immutable CV Version and provenance.
6. Mark exactly that Patch applied with result Version ID.

Any failure rolls back steps 5–6. A repeated approved idempotency key returns the
same result; competing approve/reject/apply operations have one winner.

## Lifecycle

```text
Interview: active -> completed | closed | expired
Evidence: unanswered -> answered | cannot_provide
Generation attempt: requested -> succeeded | retryable_failure | terminal_failure
Patch: pending_validation -> pending -> rejected | invalid | applied
Regeneration: rejected/invalid predecessor -> new generation attempt -> new Patch lineage item
```

`invalid` is reached only from a safely persisted application-owned Patch during
later edit/source/lifecycle revalidation; a malformed provider candidate remains
a terminal generation attempt. No provider response can transition a Patch to
`applied` or write a CV Version.

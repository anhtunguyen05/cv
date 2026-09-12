# Story 4.6: Regenerate a rejected or invalid Patch — Contract Slice

Refines `E4-CONTRACT-PROVIDER-001`, `E4-CONTRACT-PATCH-001`, and
`E4-CONTRACT-ERROR-001`.

| Operation | Proposed request | Success | Failure |
| --- | --- | --- | --- |
| Regenerate Patch | explicit action on owned rejected/invalid Patch; approved idempotency/precondition and bounded optional User feedback | New validated pending Patch with `predecessor_patch_id`, exact source/Evidence/provider/schema versions; predecessor unchanged | Invalid predecessor status, stale/unavailable context, retryable provider, terminal invalid, conflict/non-disclosure |
| Reconcile lost result | predecessor lineage/read or idempotency boundary | Same new Patch/attempt result | Current safe state and next action; never blind duplicate |

Server derives every source, revalidates context, and reuses the single provider
adapter and validator. Optional rejection feedback cannot broaden the target or
become supporting Evidence. Freeze eligible states/context, feedback, lineage,
dedupe, status/error, late results, and allowed actions through `E4-DEC-001`
through `E4-DEC-008` before `ready-for-dev`.

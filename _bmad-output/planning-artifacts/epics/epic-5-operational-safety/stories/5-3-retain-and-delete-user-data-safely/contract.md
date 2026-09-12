# Story 5.3 — Contract Slice

Refines `E5-CONTRACT-RETENTION-001`.

| Operation | Required input/control | Success | Failure |
| --- | --- | --- | --- |
| Preview retention run | policy/environment/cutoff, authorized operator | Safe per-class counts/actions/exceptions; no mutation | Denied/invalid/inventory mismatch |
| Execute retention | approved preview hash/policy/environment/change authorization | Checkpointed terminal result and safe audit | Partial-retryable/terminal with exact checkpoint; no false complete |
| Request/confirm User deletion | authenticated subject plus approved reauth/confirmation, no arbitrary scope IDs | Scoped request and status/retained exceptions | Denied/validation/conflict/hold/failure |
| Read deletion status | owner or authorized operator | Safe class outcomes and next actions | Non-disclosing denial |

Freeze periods, authority, classifications, actions/order, holds, backup/external
semantics, scheduler, approvals, codes and evidence in `E5-DEC-001`, `E5-DEC-004`.

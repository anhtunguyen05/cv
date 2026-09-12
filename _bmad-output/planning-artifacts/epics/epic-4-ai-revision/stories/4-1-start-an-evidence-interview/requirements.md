# Story 4.1: Start an Evidence interview — Requirements

Return to [Story overview](README.md); shared [rules](../../business-rules.md),
[data/lifecycle](../../data-and-lifecycle.md), [security](../../security-and-access.md),
and [UX/validation](../../ux-and-validation.md) remain authoritative.

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

**Always:** Require session and complete ownership graph; resolve source IDs and
unresolved areas server-side; snapshot question/area version atomically; return
or reconcile duplicates under approved policy; preserve all source records.

**Never:** Trust nested IDs/areas from the client; recompute or modify Match/CV;
create a session with no eligible area; send data to a provider during start;
disclose foreign source existence.

| Scenario | Expected behavior |
| --- | --- |
| Owned report with missing/Weak areas | One active pinned session with deterministic areas/questions |
| No unresolved area | Explicit not-needed state and no session |
| Existing active/completed/expired session | Approved reuse/restart/conflict behavior; no accidental duplicate |
| Report source deleted but historically readable | Eligibility follows frozen policy without source mutation |
| Foreign/missing/inconsistent source graph | Non-disclosing failure and no session |
| Concurrent/retried/lost start | One reconciled outcome under idempotency/transaction rules |

</frozen-after-approval>

## Concern coverage

Behavior and backend cover eligibility, session creation, source pinning, domain
rules, persistence, and transaction. Contract covers start/read/status/errors.
Security covers ownership/non-disclosure and no provider call. Validation covers
source/precondition/session states. Frontend covers start/not-needed/loading/
failure/recovery and accessible area explanation. Integration and verification
use one shared exact-source fixture set.

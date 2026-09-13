# Story 3.2: Preview a saved CV Version — Requirements

Return to the [Story overview](README.md). Shared rules remain authoritative in
[Epic business rules](../../business-rules.md), [data and lifecycle](../../data-and-lifecycle.md),
[security and access](../../security-and-access.md), and
[UX and validation](../../ux-and-validation.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries and constraints

**Always:** Resolve exact owned Version and active Template version; validate
snapshot compatibility; use the shared deterministic section registry; keep
User content inert; identify sources; expose complete and accessible states.

**Ask first:** Resolve planning blockers and record approval evidence before
implementation.

**Never:** Read draft Profile fields; mutate the Version; substitute another
Template/version; execute content; silently drop required content; recompute
source values in Template-specific components; claim print/export completion.

## Story-specific rules and edge cases

- **CV-PREVIEW-BR-001:** Direct URL, refresh, back/forward, multi-tab, and cache
  restore must resolve the same source tuple or fail explicitly.
- **CV-PREVIEW-BR-002:** Unknown/legacy snapshot fields follow an approved
  compatibility policy; required incompatibility yields no misleading partial
  Preview.
- **CV-PREVIEW-BR-003:** Empty, long, Unicode, long URL/word, repeated items,
  invalid date, asset failure, and renderer exception have frozen safe outcomes.

| Scenario | Expected behavior |
| --- | --- |
| Full supported snapshot | Every non-empty supported section in canonical order |
| Empty optional sections | Omitted without orphan heading/spacing/control |
| Profile changes after Version | Preview remains identical to saved snapshot |
| Markup-like text or unsafe URL | Escaped/inert text or approved safe link behavior |
| Foreign/missing Version or unavailable Template | Non-disclosing/unavailable error; no fragment shown |
| Unsupported schema or renderer failure | Explicit safe failure; exact selection retained for recovery |

</frozen-after-approval>

## Coverage by concern

| Concern | Authoritative coverage |
| --- | --- |
| Behavior | Rules/scenarios above and canonical ACs in [README](README.md) |
| Contract | Story slice in [contract.md](contract.md) plus Epic/global envelopes |
| Backend | Owned immutable Version read, schema validation, safe projection, no mutation |
| Security | Session, ownership, non-disclosure, escaping, URL/assets, sensitive caching |
| Validation | Snapshot/Template compatibility, section schema, text/date/link/collection bounds |
| Frontend | Route, loading/ready/empty/error/stale states, renderer, focus, semantics |
| Integration | Exact tuple, shared fixtures/registry, cache keys, field/code mapping |
| Verification | AC mapping and evidence gates in [verification.md](verification.md) |

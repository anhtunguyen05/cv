# Story 3.1: Select a Template — Requirements

Return to the [Story overview](README.md). Shared rules remain authoritative in
[Epic business rules](../../business-rules.md), [security and access](../../security-and-access.md),
and [UX and validation](../../ux-and-validation.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries and constraints

**Always:** Require the approved session, return only active available compatible
Templates, expose safe selection metadata, preserve exact identity/version, and
revalidate availability before Preview.

**Ask first:** Resolve planning blockers and record approval evidence before
implementation.

**Never:** Accept arbitrary renderer/module/HTML/CSS input; display inactive or
unauthorized entries; silently substitute a Template; create Preview/Export on
selection; infer availability only in the browser.

## Story-specific rules and edge cases

- **TEMPLATE-SELECT-BR-001:** Catalog ordering and availability are deterministic
  under one catalog/config version.
- **TEMPLATE-SELECT-BR-002:** A selection stores or carries both stable Template
  identity and exact version; a later availability change produces an explicit
  stale/unavailable state.
- **TEMPLATE-SELECT-BR-003:** Empty catalog, partial metadata failure, repeated
  selection, direct URL, back/forward, refresh, and multi-tab behavior follow
  the approved selection-state contract.

| Scenario | Expected behavior |
| --- | --- |
| Active compatible entries | Ordered safe summaries with one selectable identity/version each |
| Inactive, unavailable, incompatible, or malformed entry | Not selectable; no Preview/Export side effect |
| Availability changes after catalog load | Explicit revalidation failure; keep CV context and offer reselection |
| Empty catalog | Helpful empty state without fabricated default |
| Catalog request fails or session expires | Actionable global error/recovery; no stale success |
| Long/localized/markup-like metadata | Bounded, escaped, accessible presentation |

</frozen-after-approval>

## Coverage by concern

| Concern | Authoritative coverage |
| --- | --- |
| Behavior | Rules/scenarios above and canonical ACs in [README](README.md) |
| Contract | Story slice in [contract.md](contract.md) plus Epic/global envelopes |
| Backend | Registry, availability policy, query service, safe DTO, and ordering |
| Security | Session, catalog scope, trusted registry, escaping, safe assets, and rate limit |
| Validation | Identity/version, compatibility, metadata, stale selection, and transport |
| Frontend | Loading/empty/error/catalog/selected/unavailable and keyboard states |
| Integration | Shared catalog fixtures, selection tuple, cache keys, and route mapping |
| Verification | AC mapping and evidence gates in [verification.md](verification.md) |

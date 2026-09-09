# Input Reconciliation Review — Global Documentation

**Verdict: PASS.** The original findings and the final stable-ID gap are
resolved. This review records historical findings and their disposition.

## 2026-09-10 Follow-up

**[Resolved] Stable-ID coverage.** `docs/overview.md` promises that
lower-level artifacts reference global rule IDs, and `BMAD_PROJECT_GUIDE.md`
requires precise references rather than copying shared rules. `api.md`,
`data.md`, `security.md`, `validation.md`, `testing.md`, `accessibility.md`,
`observability.md`, and the async contract now satisfy that convention.
`ERROR-STD-*`, `REL-STD-*`, and `HTTP-CONTRACT-*` now identify the previously
uncitable rules. `API-STD-007` also points API failures to `HTTP-CONTRACT-001`.

### Resolved in follow-up

- AD-13 is no longer contradicted by `Deferred`; only account recovery remains
  open.
- Sanctum is explicitly target-state, with stateful-origin, credentialed CORS,
  cookie attribute, CSRF, logout, and expiry configuration requirements.
- `A11Y-STD-*` covers NFR-3 interaction states and keyboard/accessibility
  requirements.
- Compatibility entries and the spine now consistently describe `docs/*.md` as
  routing entry points, not competing documentation.

## Findings

1. **[Blocker] Deferred authentication contradicts the adopted decision.**
   `ARCHITECTURE-SPINE.md` AD-13 (lines 139–143) adopts Sanctum stateful
   sessions, while `Deferred` (lines 291–292) still says the browser
   authentication mechanism is open. Remove or narrow that deferred item to
   account-recovery/session-lifecycle policy. The security standard and HTTP
   contract otherwise correctly point to the adopted mechanism.

2. **[Blocker] The standards do not give lower-level artifacts stable global
   rule IDs.** `BMAD_PROJECT_GUIDE.md` requires stories/tasks to reference
   shared standards by IDs (for example `API-STD-001` and `ERROR-STD-001`), and
   `docs/overview.md` promises that behaviour. The new standards are only
   unnumbered bullets, so an epic cannot cite one precise rule without copying
   it. Assign stable IDs to the global rules (or revise the guide and overview
   to specify an equally precise citation convention).

3. **[Blocker] NFR-3 accessibility/UX-state requirements are not represented
   by a global standard.** The PRD requires loading, empty, validation,
   authorization, and failure states; keyboard access; and associated labels /
   errors. The new tree has no accessibility standard, and `testing.md` only
   names browser journeys. Add a cross-epic accessibility/usability standard
   (and index it) or explicitly place that scope in an existing standard with
   stable rule IDs.

4. **[Major] Current-versus-target labelling is incomplete for authentication.**
   `docs/standards/security.md` states that the SPA *uses* Sanctum and
   `auth:sanctum`, but the current API has only the `web` session guard
   (`apps/api/config/auth.php`) and `composer.json` has no Sanctum dependency.
   Since this repository is explicitly an early scaffold, mark AD-13 as the
   required target implementation until installed/configured, while retaining
   it as a binding global decision. Also specify production cookie transport
   requirements (`Secure` in production and the needed same-site/stateful-origin
   configuration) before an authentication story is marked ready.

## Confirmed reconciliation

- The previous `docs/database.md` raw-AI logging conflict is removed: data,
  security, and observability now distinguish raw sensitive content from
  sanitized audit metadata.
- API envelope/versioning, MySQL/SQLite scope, ULIDs, validation boundaries,
  risk-based tests, and post-MVP AI/worker boundaries agree with AD-14 through
  AD-19 and the PRD.
- Compatibility entries now point to the canonical documents, but the spine's
  `Deferred` lines 306–308 should be updated too: `docs/architecture.md` is no
  longer a conceptual source document; it is a compatibility entry point.

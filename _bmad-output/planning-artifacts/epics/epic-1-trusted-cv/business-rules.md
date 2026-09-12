# Epic 1 Shared Business Rules

These rules apply only inside Epic 1 but are shared by multiple stories. Story
files reference these IDs and add only story-specific behavior.

## Account and ownership

- **E1-BR-001 — Trusted identity:** Laravel derives the acting User from the
  authenticated session. A client-supplied User ID never grants ownership.
- **E1-BR-002 — Ownership isolation:** A User may create, read, or change only
  their own CV Profiles and may create/read only their own CV Versions.
- **E1-BR-003 — Non-disclosure:** A missing protected resource and a resource
  owned by another User produce the same externally visible result.
- **E1-BR-004 — Credential isolation:** Passwords, password hashes, session
  cookies, and CSRF material are never part of a User, Profile, or Version
  response and never enter ordinary logs.
- **E1-BR-005 — Session independence:** Sign-out, expiry, or invalid session
  state removes access but never edits or deletes User-owned product data.

## CV Profile

- **E1-BR-006 — Structured source:** A CV Profile is the mutable, structured
  source for every supported CV section; an unstructured text blob is not a
  valid substitute.
- **E1-BR-007 — Atomic aggregate writes:** A create or update either persists a
  valid requested Profile state or leaves the previous state unchanged. Invalid
  nested entries never create partial aggregate state.
- **E1-BR-008 — Stable item identity:** Education, experience, project,
  certificate, language, and activity items have stable server-owned IDs so
  edit/remove operations cannot depend on array position.
- **E1-BR-009 — Optional emptiness:** Optional empty sections and empty optional
  collections do not invalidate an otherwise valid Profile or prevent Version
  creation.
- **E1-BR-010 — Explicit removal:** Omitting an optional field is not silently
  interpreted as deleting existing data. Removal uses the approved update
  contract and preserves unrelated valid entries.
- **E1-BR-011 — One validation authority:** Laravel is authoritative for field,
  nested-item, ownership, and state invariants; frontend validation is an
  interaction aid using the same approved contract.

## CV Version

- **E1-BR-012 — Complete snapshot:** A CV Version captures the full approved
  structured Profile state and source Profile ID in one transaction.
- **E1-BR-013 — Immutability:** No operation updates a saved Version. A changed
  snapshot is represented by a new Version with a new ID.
- **E1-BR-014 — Source independence:** Editing or removing Profile content after
  Version creation does not alter the Version or its downstream meaning.
- **E1-BR-015 — Stable ordering:** Version lists are scoped to the acting User
  and ordered newest first with a deterministic ID tie-breaker.

## Cross-story consistency

- **E1-BR-016 — Contract-first parallelism:** Work may proceed in parallel only
  after the shared contract it consumes is approved; consumers may not fork
  local variants of a shared User, Profile, item, or error shape.
- **E1-BR-017 — Explicit failures:** Every write and auth action exposes one of
  success, retryable failure, or terminal failure; ambiguous completion has an
  approved reconciliation path before a retry is offered.

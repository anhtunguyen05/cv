# Epic 1 UX and Validation

## Shared interaction states

- **E1-UX-001:** Account forms and every Profile/Version write define idle,
  dirty, submitting, success, field-validation, authorization/not-found,
  retryable failure, terminal failure, and stale-conflict states where relevant.
- **E1-UX-002:** A pending write disables duplicate submission without making
  keyboard focus or progress opaque. An ambiguous result is reconciled before
  the action becomes repeatable.
- **E1-UX-003:** Field errors are programmatically associated with controls,
  an error summary can receive focus after failed submission, and dynamically
  repeated entries retain stable labels and keys.
- **E1-UX-004:** On validation failure, valid non-sensitive input remains;
  passwords and credential material are cleared. A failed nested edit does not
  silently delete other entries.
- **E1-UX-005:** Session expiry clears protected screen data before navigation
  to sign-in and preserves only an approved safe return destination.
- **E1-UX-006:** Empty optional Profile sections have an explicit empty state and
  never appear as a failure or block Version creation.

## Shared validation behavior

- **E1-VAL-001:** Backend field paths are stable and map one-to-one to frontend
  controls, including nested collection items. The exact path syntax is fixed
  by `E1-DEC-003`.
- **E1-VAL-002:** Strings are trimmed or preserved according to one approved
  field policy before validation; unsupported silent coercion is rejected.
- **E1-VAL-003:** Empty, over-limit, malformed, unsupported nested, duplicate,
  and cross-field-invalid inputs produce field-keyed errors without partial
  persistence.
- **E1-VAL-004:** Repeatable collection limits, item limits, string character
  and byte limits, Unicode policy, URL/date semantics, and ordering rules are
  contract data rather than UI-only constants.
- **E1-VAL-005:** The frontend may fail fast using Zod/VeeValidate but always
  consumes and displays authoritative Laravel validation outcomes.

## Editor coordination

Stories 1.3 through 1.7 share Profile load/save state, nested field paths,
concurrency handling, and unsaved-change navigation. Their tasks must reference
`E1-COORD-PROFILE-001` in their story draft before parallel implementation.
Story 1.8 reuses the read-only Profile representation but must not snapshot
unsaved browser state.

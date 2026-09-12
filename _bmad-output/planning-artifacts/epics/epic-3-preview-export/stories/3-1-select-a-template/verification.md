# Story 3.1: Select a Template — Verification

## AC-to-task traceability

| Acceptance criterion | Tasks |
| --- | --- |
| `AC-3-1-select-a-template-01` | `TASK-3-1-01` through `TASK-3-1-07` |
| `AC-3-1-select-a-template-02` | `TASK-3-1-01` through `TASK-3-1-07` |

## Required evidence

- Registry/unit: identity/version, active/compatibility, invalid definitions,
  deterministic order, and no arbitrary renderer lookup.
- Laravel: authenticated catalog envelope, empty/failure, safe metadata, cache,
  and stale selection validation.
- Vue: schema adapter, all chooser states, repeated selection, safe text, focus,
  keyboard, and assistive names.
- Browser: catalog-to-Preview handoff, refresh/back, availability change, no
  Preview/Export side effect on rejection, and synthetic fixture reset.

## Exit gate

- Both canonical ACs have passing evidence tied to exact fixture versions.
- `E3-COORD-TEMPLATE-001` records the accepted identity/version/catalog boundary.
- No application change is marked complete before decisions and prerequisites close.
- Task completion records command/result or review evidence; planned commands
  alone do not count.

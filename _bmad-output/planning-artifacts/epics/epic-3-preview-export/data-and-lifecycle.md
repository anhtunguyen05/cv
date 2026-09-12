# Epic 3 Data and Lifecycle

## Identity and mutability

| Concept | Identity/source | Mutability |
| --- | --- | --- |
| Template | Stable key plus immutable version | Availability may change; a published version does not mutate |
| CV Version | Epic 1 immutable ULID | Read-only Epic 3 source |
| Preview | Exact source tuple and renderer version | Transient deterministic projection by default |
| Export intent | Exact successful Preview tuple | Transient browser action by default |

## E3-DATA-001 — Template registry

- Store or configure stable identity, version, active/availability state, safe
  metadata, supported source-schema range, and renderer mapping.
- Never accept a client-provided module path, CSS URL, HTML fragment, or
  executable renderer identifier.
- A behavior-changing published layout/mapping creates a new version rather
  than rewriting the meaning of historical source tuples.

## E3-DATA-002 — Render projection

- Resolve the owned CV Version snapshot and selected active Template in one
  consistency boundary.
- Validate the snapshot schema before mapping it to the shared section registry.
- Keep canonical data values separate from display labels and print formatting.
- Preview cache keys contain the full source tuple; invalidation cannot replace
  one Version or Template version with another.

## E3-DATA-003 — Export state

- MVP browser Export creates no server binary or trusted completion record by
  default.
- If an audit intent is approved, it records intent and source IDs only; it does
  not assert that a browser produced or saved a file.
- Temporary client state contains no more sensitive content than the currently
  authorized Preview and is cleared under the approved navigation/session rule.

## Lifecycle

```text
Template version: published -> active/available -> inactive/unavailable
Selection: none -> selected -> revalidated -> preview-ready | unavailable
Preview: loading -> ready -> print-preparing | failed
Export intent: print-preparing -> dialog-invoked -> returned/unknown
```

Browser APIs generally cannot prove save/print completion. The product must not
invent a successful artifact state from the dialog returning.

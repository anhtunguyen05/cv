# Epic 3 Test Strategy

## Required evidence layers

| Layer | Epic 3 responsibility |
| --- | --- |
| Unit/domain | Template availability/version rules, section mapping, source tuple, deterministic ordering |
| Laravel feature | Catalog/source envelopes, session, ownership, unavailable and schema errors |
| Vue unit/component | Adapters, selection states, safe rendering, empty/long content, print preparation |
| Contract | Versioned Template and CV snapshot fixtures shared across backend/frontend |
| Accessibility | Semantic document outline, names, focus order, keyboard operation, error announcements |
| Visual/print | Approved browsers, viewports, page sizes, overflow, page breaks, hidden controls |
| Playwright | Select Template, Preview exact Version, reject foreign source, invoke print, return/retry |

## E3-TEST-001 — Template catalog fixtures

Fixtures cover active, inactive, unavailable, incompatible, version-changed,
empty, and malformed Template entries with deterministic order.

## E3-TEST-002 — CV snapshot renderer corpus

Use synthetic empty/minimal/full/long/Unicode/markup-like snapshots and every
supported section. Each pins schema, Template version, renderer version, and
expected semantic section order.

## E3-TEST-003 — Source isolation

Create Version A, edit the Profile, create Version B, then prove Preview and
Export for A never show draft or B values. Repeat with two Users and missing,
foreign, incompatible, and stale selections.

## E3-TEST-004 — Print acceptance

Freeze the browser/viewport/page matrix, capture print-emulation output, and
assert content presence, safe formatting, page-break invariants, hidden UI,
links, and no clipping under approved tolerances.

Pixel snapshots support review but do not replace semantic/content assertions.

## E3-TEST-005 — Failure and retry

Cover catalog failure, source failure, renderer exception, asset failure,
unsupported browser, blocked print, dialog return/cancel ambiguity, navigation,
and session expiry without false completion or source switching.

## Integration gates

1. Template identity/version/catalog fixture checkpoint.
2. CV snapshot section-registry and render-projection checkpoint.
3. Preview semantic/accessibility/visual checkpoint.
4. Print stylesheet and browser acceptance checkpoint.
5. Ownership and critical browser journey checkpoint.

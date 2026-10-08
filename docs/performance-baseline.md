# CareerFitCV performance baseline (report-only)

Date: 2026-10-08
Baseline commit: `96a02306cf0d28b970d51bcf0bf16f9e4107a2dd`

This document is the repeatable fixture/scenario definition for the performance
work. It is intentionally report-only until stable production-build and browser
traces are available; it does not fail CI or claim a runtime gain.

## Reusable scenarios

| Dataset | Fixture | Primary measurements |
| --- | --- | --- |
| D1 | 5 small CV Profiles | baseline request, parsing, and render |
| D2 | 100 Profiles with projects and experience | dashboard pagination and payload |
| D3 | 100 immutable CV Versions with large snapshots | version pagination, payload, cache heap |
| D4 | 100 Match Reports | authenticated aggregate count versus list serialization |
| D5 | JD near 5,000 Unicode code points | ordinary input responsiveness |
| D6 | JD near 49,000-50,000 Unicode code points, including emoji | truncation and paste responsiveness |
| D7 | CV with all 9 sections populated | completion and dirty-checking hot paths |

The same fixtures, browser/device profile, and network conditions must be used
for before/after runs. Dashboard cold and warm navigation are measured
separately. CV and JD editor runs use repeated median and p95 samples rather
than a single best run.

## Before-change static measurements

These are reproducible source measurements from the baseline commit, not
runtime timings:

| Area | Baseline observation |
| --- | --- |
| CV completion | `useCvEditorDraft.ts` had 10 `cloneDocument` references, including the computed completion path |
| JD input | `useJdEditorController.ts` used spread-based code-point work in 2 locations and encoded the whole input in the `overLimit` computed |
| Dashboard reports | `useDashboardController.ts` referenced `useMatchReportsQuery` twice and fetched report objects for the total |
| Profile list | `ProfilePresenter::data` expanded the full persisted document into collection items |
| Version list | `VersionPresenter::data` included the immutable `snapshot` and provenance fields in collection items |

No milliseconds, byte, heap, Web Vital, or bundle-size claim is made from these
static observations. Those values require a production build and browser
profiling session against D1-D7.

## Measurement commands

From `apps/web`:

```text
npm run type-check
npm run lint:check
npm run test:unit
npm run build
npm run preview
```

Record cold and warm dashboard request count/transferred bytes, CV editor
scripting/long-task/allocation samples, JD typing and paste samples, initial
and route bundle sizes, peak heap, and route navigation duration. Report median
and p95 for each repeated scenario.

## Report-only budget

The following are review metrics, not current pass/fail thresholds: dashboard
transferred bytes and request count, CV list parse time, CV/JD input scripting
time, initial JS transfer, peak heap, and route navigation duration. Core Web
Vitals reference targets (LCP <= 2.5s, INP <= 200ms, CLS <= 0.1 at p75) are
context only and are not claimed as measured for this scaffold.

TemplateCard mousemove and layout lazy-loading changes remain intentionally
omitted until a profiler or bundle report demonstrates a material bottleneck.

---
name: 'CareerFitCV'
type: architecture-spine
purpose: build-substrate
altitude: initiative
paradigm: 'layered application with an authoritative domain boundary'
scope: 'MVP and later AI-assisted CV tailoring across the web client, Laravel API, persistence, and optional worker'
status: final
created: '2026-09-01'
updated: '2026-09-01'
binds: ['FR-1', 'FR-2', 'FR-3', 'FR-4', 'FR-5', 'FR-6', 'FR-7', 'FR-8', 'FR-9', 'FR-10', 'FR-11', 'FR-12', 'NFR-1', 'NFR-2', 'NFR-3', 'NFR-4', 'NFR-5']
sources:
  - 'README.md'
  - 'docs/architecture.md'
  - 'docs/ai-workflow.md'
  - 'docs/database.md'
  - 'docs/decisions.md'
  - 'docs/implementation-plan.md'
  - '_bmad-output/planning-artifacts/prds/prd-CareerFitCV-2026-09-01/prd.md'
companions:
  - '_bmad-output/planning-artifacts/prds/prd-CareerFitCV-2026-09-01/prd.md'
  - 'docs/implementation/'
---

# Architecture Spine — CareerFitCV

## Design Paradigm

Use a layered application with one authoritative domain boundary:

```text
Web presentation (Vue)
        |
        v
Application/API boundary (Laravel controllers, requests, resources)
        |
        v
Domain services and state transitions (Laravel)
        |
        v
Persistence (Laravel models, migrations, JSON contracts)

Optional worker and external providers integrate through explicit application
boundaries and never become alternate owners of trusted product state.
```

## Invariants & Rules

### AD-1 — [ADOPTED][PRESERVE] Structured CV is the canonical representation

- **Binds:** FR-3, FR-4, FR-5, FR-8, FR-9, FR-10, FR-12
- **Prevents:** The editor, matcher, renderer, and later AI features from using incompatible free-form CV representations.
- **Rule:** Store and exchange CV content as a versioned structured document; raw text may be retained only as an input or audit artifact, never as the sole domain representation.

### AD-2 — [ADOPTED][REFINE] Laravel owns trusted product state

- **Binds:** FR-1 through FR-12, NFR-1, NFR-2, NFR-4
- **Prevents:** Vue, AI providers, or the worker independently mutating User-owned resources or bypassing authorization and validation.
- **Rule:** The Laravel API is the sole owner of authentication, ownership checks, validation, persistence, and trusted state transitions. Vue owns interaction state; integrations return data through explicit application contracts.

### AD-3 — [ADOPTED][PRESERVE] CV Profile is mutable; CV Version is immutable

- **Binds:** FR-3, FR-4, FR-5, NFR-2
- **Prevents:** Editing a reusable Profile from silently changing a Version already used for a Match Report, Preview, or Export.
- **Rule:** A CV Profile may be edited as a draft. A saved CV Version is a complete immutable snapshot with a stable identity. Changes that must be preserved create another Version.

### AD-4 — [ADOPTED][REFINE] Downstream results pin their source snapshot

- **Binds:** FR-5, FR-8, FR-10, FR-11, NFR-2, NFR-5
- **Prevents:** A Match Report, Preview, or Export changing meaning after the CV Profile is edited.
- **Rule:** A Match Report identifies the exact CV Version, logical Job
  Description, Job Description revision, and matching-rule version used. A
  Preview and Export identify the exact CV Version, Template, and applicable
  template version used.

### AD-11 — [ADOPTED][REFINE] Job Description revisions preserve report history

- **Binds:** FR-6, FR-7, FR-8, NFR-2, NFR-4, NFR-5
- **Prevents:** Editing or deleting a Job Description from changing the meaning
  of an existing analysis or Match Report.
- **Rule:** A Job Description has a stable `job_description_id` and immutable
  `job_description_revision_id` records. An edit creates a new current
  revision; analysis is pinned to exactly one revision and one
  `analysis_rule_version`, and is not implicitly regenerated. New Match Reports
  may use only the current revision of a non-deleted Job Description, and only
  after that revision has successful analysis. A deleted Job Description is
  logically deleted from new analysis and matching workflows. Its revisions,
  analyses, and historical Match Reports remain readable and reproducible to
  its owner. A Match Report stores the logical Job Description ID, exact
  revision ID, and exact analysis it consumed. The persisted source identifiers
  are `job_description_id`, `job_description_revision_id`, `analysis_id`, and
  `analysis_rule_version`; the Match Report also stores its `cv_version_id` and
  `matching_rule_version`.

### AD-5 — [ADOPTED][REFINE] Deterministic matching is the MVP decision

- **Binds:** FR-7, FR-8, FR-9, NFR-5
- **Prevents:** The first release becoming dependent on an LLM, provider availability, opaque scoring, or non-repeatable output.
- **Rule:** MVP matching uses versioned deterministic normalization, skill vocabulary, evidence classification, and scoring rules. Any later AI explanation is additive and cannot replace the stored deterministic result.

### AD-6 — [ADOPTED][PRESERVE] AI is proposal-only and human-approved

- **Binds:** FR-12 and later AI capabilities, NFR-1, NFR-2
- **Prevents:** Fabricated claims, silent CV mutation, and untraceable provider writes.
- **Rule:** AI providers may inspect permitted inputs and return a validated recommendation or Patch proposal. Only the application can persist it, and a User must explicitly approve a Patch before a new CV Version is created.

### AD-7 — [ADOPTED][PRESERVE] Single controlled orchestrator before multi-agent expansion

- **Binds:** FR-12 and later AI capabilities
- **Prevents:** Divergent agent ownership, routing complexity, and multiple uncoordinated write paths.
- **Rule:** Start with one application-controlled orchestrator and explicit tools/contracts. Introduce multiple agents only after a measured limitation is recorded and the same validation and approval boundaries remain intact.

### AD-8 — [ADOPTED][REFINE] Worker is an optional integration, not a domain owner

- **Binds:** FR-10, FR-11, later asynchronous processing, NFR-4
- **Prevents:** Making the current minimal worker or a future PDF/parser service a mandatory dependency for the MVP or a second source of truth.
- **Rule:** Browser Preview/Export must work without the worker. A worker may perform isolated expensive work through an explicit job/result contract; it may not directly mutate trusted CV, Match Report, or Patch state.

### AD-9 — [SOURCE-CORRECTED][REPLACE] Laravel health path is `/api/health`

- **Binds:** current operational verification and Phase 0 baseline
- **Prevents:** Health checks and documentation targeting a route that the Laravel source does not expose.
- **Rule:** Treat Laravel `GET /api/health` and framework `GET /up` as the API health surface. Treat worker `GET /health` and `GET /api/health` as worker endpoints; do not conflate the services.

### AD-10 — [ADOPTED][REFINE] MVP export starts at browser print/HTML

- **Binds:** FR-10, FR-11, NFR-2, NFR-4
- **Prevents:** Coupling MVP completion to server-side PDF infrastructure before artifact requirements justify it.
- **Rule:** Render Preview from a saved CV Version and Template, then use the browser print/HTML path for initial Export. Add server-side or worker Export only as a separately justified capability.

### Dependency direction

```mermaid
flowchart TD
    Web[Vue Web Client] --> Api[Laravel API Boundary]
    Api --> Domain[Domain Services and State Transitions]
    Domain --> Store[Persistent State]
    Domain --> Matcher[Deterministic Matching]
    Domain --> Renderer[Template Renderer]
    Domain --> Orchestrator[Optional AI Orchestrator]
    Orchestrator --> Provider[External AI Provider]
    Domain --> Worker[Optional Worker]
    Provider -. proposal only .-> Orchestrator
    Worker -. result contract .-> Api
```

## Consistency Conventions

| Concern | Convention |
| --- | --- |
| Naming | Use glossary nouns `User`, `CV Profile`, `CV Version`, `Job Description`, `Match Report`, `Template`, `Preview`, `Export`, and `Patch`; keep API resource identifiers explicit. |
| IDs and sources | Use stable resource IDs; pin derived records to source CV Version and Job Description revision; include schema/rule/template versions where interpretation can change. |
| Data formats | Use validated structured JSON for CV and variable analysis; keep raw inputs separate from derived interpretation; use the documented JSON error envelope. |
| State mutation | Mutate drafts explicitly; create immutable CV Versions and Job Description revisions; apply later Patch approval transactionally; reject stale source values. |
| Ownership | Resolve authorization from authenticated User ownership, not from a client-supplied User ID; every child resource must be ownership-aware. |
| Integration boundary | Providers and workers return DTO/result data through application contracts; they cannot call persistence or become trusted-state owners. |
| Failure behavior | Analysis and Export failures are explicit and retryable where safe; partial trusted state is not silently committed. |
| MVP boundary | Implement account access, structured CV, deterministic matching, Template Preview, and browser Export first; keep AI Patch, provider, worker PDF, and multi-agent work later. |

## Stack

| Name | Version |
| --- | --- |
| PHP | `^8.3` application constraint; current Docker image PHP 8.4 |
| Laravel Framework | `^13.17` |
| Vue | `^3.5.40` |
| Vite | `^8.1.5` |
| Pinia | `^4.0.2` |
| Vue Router | `^5.2.0` |
| TypeScript | `~6.0.0` |
| Python worker runtime | `>=3.11` |
| MySQL Docker service | `8.4` |
| Redis Docker service | `7-alpine`, optional for MVP application behavior |

## Structural Seed

### Repository boundaries

```text
CareerFitCV/
├── apps/web/       # Vue presentation and interaction state
├── apps/api/       # Laravel API, domain services, trusted persistence
├── apps/worker/    # Minimal optional worker integration
├── docs/           # Conceptual architecture and implementation packages
└── _bmad-output/   # Generated planning artifacts and architecture spine
```

### MVP data ownership

```mermaid
erDiagram
    USER ||--o{ CV_PROFILE : owns
    USER ||--o{ JOB_DESCRIPTION : owns
    CV_PROFILE ||--o{ CV_VERSION : snapshots
    CV_VERSION ||--o{ MATCH_REPORT : produces
    JOB_DESCRIPTION ||--o{ MATCH_REPORT : targets
    CV_VERSION ||--o{ EXPORT : renders
    TEMPLATE ||--o{ EXPORT : formats
```

The diagram is a relationship seed. Field-level JSON shape belongs to the
canonical PRD and implementation packages; ownership and snapshot identity are
architecture invariants.

### Environment topology

```text
Development:
  Browser -> apps/web dev server -> apps/api Laravel runtime
                                  -> local SQLite or apps/api Docker MySQL
                                  -> optional apps/api Docker Redis
  apps/worker -> independent health service on its own port

MVP production shape:
  Browser -> deployed web surface -> Laravel API -> database/storage
                                      \-> optional queue/worker integration
```

## Capability → Architecture Map

| Capability / Area | Lives in | Governed by |
| --- | --- | --- |
| FR-1 Account access | Laravel auth boundary + Vue auth experience | AD-2, ownership convention |
| FR-2 Ownership isolation | Laravel policies/application boundary | AD-2, ownership convention |
| FR-3 CV Profile | Laravel domain/persistence + Vue editor | AD-1, AD-2, AD-3 |
| FR-4/FR-5 CV Version/source integrity | Laravel version service and persisted snapshots | AD-3, AD-4 |
| FR-6/FR-7 Job Description | Laravel intake/analysis services + Vue input | AD-2, AD-4, AD-5 |
| FR-8/FR-9 Match Report | Laravel deterministic matching service + Vue report | AD-4, AD-5 |
| FR-10/FR-11 Preview and Export | Vue renderer initially; Laravel/worker only for later server Export | AD-4, AD-8, AD-10 |
| FR-12 AI Patch | Later Laravel orchestrator/provider boundary and Patch service | AD-2, AD-6, AD-7 |
| NFR-1/NFR-2 Safety and traceability | Laravel authorization, validation, persistence, audit boundary | AD-2, AD-3, AD-4, AD-6 |

## Deferred

- Browser authentication mechanism and account recovery policy remain open until
  Phase 0 implementation begins.
- Production database/storage topology and retention policy remain open; local
  SQLite and API-local Docker MySQL are both current repository options.
- Exact CV JSON field limits, aliases, matching weights, and quality thresholds
  belong to the Phase 1/2 contracts and evaluation fixtures.
- Template representation and final visual/print direction remain open until
  Phase 4 design work; the architecture only requires a renderer consuming a
  saved CV Version.
- Server-side/worker PDF generation remains deferred until browser Export fails
  an approved artifact requirement.
- AI provider, prompt versioning, tool-call logging, retention, and deletion
  controls remain deferred to the post-MVP AI/hardening work.
- Multi-agent decomposition remains deferred until a measured single-orchestrator
  limitation is recorded.
- The existing `docs/architecture.md` remains a conceptual source document; this
  spine is the build substrate and corrects the stale current health-route claim
  without rewriting the source document.

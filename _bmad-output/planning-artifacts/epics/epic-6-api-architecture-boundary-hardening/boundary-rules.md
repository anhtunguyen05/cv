# Epic 6 Boundary Rules

## Dependency direction

```text
Presentation -> Application -> Domain
Infrastructure -> Application contracts and Domain
```

Infrastructure is the only layer allowed to depend on Laravel persistence,
database transactions/locks, Eloquent models, or outbound HTTP clients.

## Application

Application contracts and DTOs are framework-independent. Use cases own
workflow orchestration and receive ports through dependency injection. They
must not import `App\Models`, Eloquent, `DB`, or Laravel HTTP clients.

## Domain

Domain policies, backed enums, and value objects are pure PHP. They must not
import Laravel, Eloquent, database, HTTP, or presentation concerns.

## Presentation

Controllers, requests, resources, and response mappers own HTTP concerns only.
Controllers call application ports/use cases and do not query Eloquent or
construct database transactions.

## Infrastructure

Adapters map persisted records and provider responses to application DTOs and
results. Bind every production adapter in the composition root. Adapters must
not silently change public contracts or write trusted state on behalf of an
external provider.

## Compatibility

Existing string values remain canonical at persistence and HTTP boundaries.
Ownership checks, idempotency receipts, ETags, immutable snapshots, triggers,
and rollback/retry semantics are invariants, not adapter implementation
details.

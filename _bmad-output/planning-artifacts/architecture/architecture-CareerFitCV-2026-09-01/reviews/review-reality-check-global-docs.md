# Reality Check — Global Documentation

## Verdict: PASS — target architecture accurately labeled

The canonical spine and standards now distinguish adopted target rules from
the early-scaffold runtime. No remaining high finding blocks their use as a
developer/agent implementation source of truth.

## Resolved findings

1. AD-13 now fixes browser authentication to Sanctum, and `Deferred` limits
   the open decision to account recovery. The former contradiction is removed.
2. `docs/standards/security.md` explicitly labels Sanctum as a target
   implementation prerequisite and records stateful origins, credentialed CORS,
   session-cookie settings, CSRF bootstrap, logout, and expiry handling.
3. The global overview explicitly says that product-domain behavior remains
   target design until implemented and verified. This correctly covers product
   routes/envelopes, ULIDs, MySQL integration, Vitest, and Playwright.

## Confirmed implementation gaps (correctly labeled as target)

- `/api/v1` is only the web default base URL; Laravel currently exposes only
  `/api/health` (plus framework `/up`). There are no product endpoints, API
  Resources, Form Requests, policies, custom error renderer, or envelope
  enforcement yet. The HTTP documents are valid future contracts, not current
  runtime behavior.
- MySQL 8.4 is provided by API-local Compose, but Laravel still defaults to
  SQLite and PHPUnit explicitly runs SQLite. Also, Compose makes `app` depend
  on Redis startup. Therefore MySQL-canonical and Redis-optional are target
  operating rules; the current local topology has not yet been aligned.
- ULID product identities are a valid Laravel 13 convention, but no product
  aggregate/migration exists. Existing `users` and system migrations remain
  bigint as documented.

## Confirmed repository facts

- Laravel `^13.17` / PHP `^8.3`, Vue `^3.5.40`, Vite `^8.1.5`, MySQL `8.4`,
  Redis `7-alpine`, and Python `>=3.11` match their manifests/configuration.
- Laravel serves `GET /api/health` and framework `GET /up`; the worker serves
  `/health` and `/api/health`. The operational-health exception in the common
  HTTP contract is therefore source-consistent.

## Sources checked

- `apps/api/composer.json`, `apps/api/composer.lock`, `apps/api/bootstrap/app.php`,
  `apps/api/routes/api.php`, `apps/api/config/{database,session}.php`, and
  `apps/api/docker-compose.yml`.
- `apps/web/package.json` and `apps/web/src/shared/api/client.ts`.
- Laravel 13 Sanctum official documentation (SPA installation/configuration and
  CSRF flow), checked 2026-09-10.

## Non-blocking record-hygiene note

The earlier `review-reality-check.md`, `review-adversarial.md`, and
`review-rubric.md` retain superseded claims that authentication is deferred or
that no new target tooling was selected. They are review snapshots rather than
canonical standards; label them superseded if this directory is presented to
implementation agents as current guidance.

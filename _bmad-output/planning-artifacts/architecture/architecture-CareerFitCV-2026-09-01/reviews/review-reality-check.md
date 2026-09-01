# Reality Check — CareerFitCV Architecture Spine

## Verdict

Named technologies and current boundaries are reality-checked against the
repository manifests and source rather than treated as newly selected stack
choices.

## Evidence

- Laravel `^13.17` and PHP `^8.3` are declared in `apps/api/composer.json`;
  Docker uses PHP 8.4 in `apps/api/Dockerfile`.
- Vue, Vite, Pinia, Vue Router, and TypeScript versions/ranges are declared in
  `apps/web/package.json`.
- Python `>=3.11` is declared in `apps/worker/pyproject.toml`.
- MySQL 8.4 and Redis 7 Alpine are declared in `apps/api/docker-compose.yml`.
- Laravel exposes `/api/health` and `/up`; the worker exposes `/health` and
  `/api/health`.

No new technology is introduced by this spine. Exact production auth, storage,
provider, and worker decisions remain deferred.

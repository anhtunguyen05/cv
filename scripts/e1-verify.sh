#!/usr/bin/env bash
set -euo pipefail

MODE="${1:-all}"
case "$MODE" in
  api-fast|api-pg|web|e2e|all) ;;
  *) echo "usage: $0 {api-fast|api-pg|web|e2e|all}" >&2; exit 2 ;;
esac

RUN_ID="${E1_RUN_ID:-$(date -u +%Y%m%d%H%M%S)}"
if [[ ! "$RUN_ID" =~ ^[a-z0-9]+$ ]]; then
  echo 'E1_RUN_ID must contain only lowercase ASCII letters and digits' >&2
  exit 2
fi

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
API_DIR="$ROOT_DIR/apps/api"
WEB_DIR="$ROOT_DIR/apps/web"
PROJECT="careerfitcv-e1-e2e-$RUN_ID"
COMPOSE=(docker compose --project-name "$PROJECT" --file "$API_DIR/docker-compose.e2e.yml")

pick_port() {
  local port
  for port in $(seq "$1" "$(( $1 + 100 ))"); do
    if ! (echo >/dev/tcp/127.0.0.1/$port) >/dev/null 2>&1; then
      echo "$port"
      return 0
    fi
  done
  return 1
}

run_web_checks() {
  npm --prefix "$WEB_DIR" run type-check
  npm --prefix "$WEB_DIR" run lint:check
  npm --prefix "$WEB_DIR" run test:unit -- --run
}

start_disposable_api() {
  export E1_API_PORT="${E1_API_PORT:-$(pick_port 18080)}"
  export E1_WEB_PORT="${E1_WEB_PORT:-$(pick_port 4173)}"
  export E1_RUN_ID="$RUN_ID"
  cleanup_disposable_api() {
    "${COMPOSE[@]}" down -v --remove-orphans >/dev/null 2>&1 || true
  }
  # Register cleanup before startup so a failed build, pull, or dependency
  # creation cannot leave a disposable project running in the background.
  trap cleanup_disposable_api EXIT INT TERM
  "${COMPOSE[@]}" up -d --build
  # The source bind mount hides the image's vendor directory on some hosts.
  # Install dependencies before probing the PHP health endpoint in that case.
  "${COMPOSE[@]}" exec -T app composer install --no-interaction --no-progress --prefer-dist
  for attempt in $(seq 1 60); do
    if curl --fail --silent "http://127.0.0.1:${E1_API_PORT}/api/health" >/dev/null; then
      break
    fi
    if [[ "$attempt" == 60 ]]; then
      echo 'Disposable API did not become healthy' >&2
      "${COMPOSE[@]}" logs --no-color app nginx >&2 || true
      exit 1
    fi
    sleep 2
  done
  "${COMPOSE[@]}" exec -T app php artisan migrate:fresh --seed --force
}

case "$MODE" in
  api-fast) (cd "$API_DIR" && vendor/bin/phpunit --configuration phpunit.fast.xml) ;;
  web) run_web_checks ;;
  api-pg|e2e|all)
    start_disposable_api
    if [[ "$MODE" == api-pg || "$MODE" == all ]]; then
      "${COMPOSE[@]}" exec -T app vendor/bin/phpunit --configuration phpunit.e2e.xml
    fi
    if [[ "$MODE" == e2e || "$MODE" == all ]]; then
      run_web_checks
      (cd "$WEB_DIR" && VITE_BACKEND_URL="http://127.0.0.1:${E1_API_PORT}" E1_WEB_PORT="$E1_WEB_PORT" npm run test:e2e)
    fi
    ;;
esac

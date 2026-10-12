# Frontend architecture

The frontend is a Vue 3, TypeScript, and Vite application. Vue Router owns route
composition, Pinia owns client-side global state, and TanStack Vue Query owns
server state and cache. `ofetch` is the shared HTTP client. VeeValidate and Zod
are reserved for forms and validation when a real feature requires them.

## Source structure

- `apps/web/src/app/`: application wiring, router, providers, and environment configuration.
- `apps/web/src/pages/`: route-level composition. Keep pages thin; compose features and shared UI.
- `apps/web/src/features/`: business capabilities. Create only the folders a real feature needs.
- `apps/web/src/shared/`: business-agnostic API, components, composables, schemas, types, and utilities.
- `apps/web/src/assets/`: global styles and static assets.

Shared UI uses Atomic Design only where it improves reuse:
`shared/components/ui` is for shadcn-vue primitives, while `atoms`, `molecules`,
and `organisms` are project-level reusable abstractions. Feature-specific UI
stays in its feature.

Native `input`, `textarea`, `select`, `label`, and `button` elements belong only
inside `shared/components/ui` primitives. Consumers compose those primitives so
their model, validation, ARIA, and disabled/loading contracts remain consistent.

## Dependency rules

The intended direction is `app/pages/features -> shared`. Shared code must not
import from pages or features. A feature should expose a public API through its
`index.ts` when consumers need a stable entry point; avoid broad barrel files.

Server data flows through `feature query/mutation -> feature API -> shared/api/client.ts`.
Do not copy ordinary query results into Pinia. Use Pinia for client state such as
session state, UI state, wizard state, and temporary cross-page selections.

## Naming

Use `PascalCase.vue`, `useSomething.ts`, `*.store.ts`, `*.api.ts`, `*.queries.ts`,
`*.mutations.ts`, `*.schema.ts`, and `*.types.ts`.

For example, a user capability belongs under `features/users/` with its endpoint
in `api/users.api.ts`, its query in `api/users.queries.ts`, and its UI in
`components/UserTable.vue`. Reuse the shared `Button` primitive (or a
consumer-backed atom/molecule) before adding another action abstraction;
`LoginForm.vue` belongs in `features/auth/components/`.

No product feature, authentication flow, or shadcn component has been invented
or implemented as part of this foundation.

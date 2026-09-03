# Vue Frontend Architecture Summary

## 1. Technology Stack

### Core
- Vue 3
- TypeScript
- Vite
- Vue Router

### State Management
- Pinia
  - Dùng cho client/global state.
  - Ví dụ: auth state, UI state, wizard state, temporary shared state.
  - Không dùng Pinia như cache mặc định cho toàn bộ dữ liệu lấy từ API.

### Server State / Data Fetching
- TanStack Query for Vue (`@tanstack/vue-query`)
  - Quản lý server state.
  - Query caching.
  - Refetch.
  - Retry.
  - Pagination / infinite query.
  - Mutation.
  - Cache invalidation.

### HTTP Client
Ưu tiên:
- `ofetch`

Có thể thay bằng:
- Axios

`ofetch` được dùng làm API client chung tại `shared/api/`.

### Forms & Validation
- VeeValidate
- Zod
- `@vee-validate/zod`

### Styling / UI
- Tailwind CSS
- shadcn-vue
- Reka UI
- lucide-vue-next

### Code Quality
- ESLint
- Prettier

---

# 2. Architecture Style

Project sử dụng kết hợp:

- Feature-based architecture
- Atomic Design cho shared UI
- Shared kernel cho code tái sử dụng
- Application layer thông qua `app/`

Không áp dụng Atomic Design cho toàn bộ source code.

Atomic Design chủ yếu được dùng trong:

```text
shared/components/
```

Business code được tổ chức theo feature:

```text
features/
```

Application-level configuration nằm trong:

```text
app/
```

---

# 3. Recommended Source Structure

```text
src/
├── app/
│   ├── router/
│   │   ├── index.ts
│   │   ├── routes.ts
│   │   └── guards/
│   │
│   ├── providers/
│   │   ├── pinia.ts
│   │   └── vue-query.ts
│   │
│   ├── layouts/
│   │   ├── DefaultLayout.vue
│   │   └── AuthLayout.vue
│   │
│   └── config/
│       └── env.ts
│
├── pages/
│   ├── auth/
│   │   ├── LoginPage.vue
│   │   └── RegisterPage.vue
│   │
│   ├── dashboard/
│   │   └── DashboardPage.vue
│   │
│   └── users/
│       ├── UserListPage.vue
│       └── UserDetailPage.vue
│
├── features/
│   ├── auth/
│   │   ├── api/
│   │   │   ├── auth.api.ts
│   │   │   ├── auth.queries.ts
│   │   │   └── auth.mutations.ts
│   │   │
│   │   ├── components/
│   │   │   ├── LoginForm.vue
│   │   │   └── RegisterForm.vue
│   │   │
│   │   ├── composables/
│   │   │   └── useAuth.ts
│   │   │
│   │   ├── schemas/
│   │   │   └── auth.schema.ts
│   │   │
│   │   ├── stores/
│   │   │   └── auth.store.ts
│   │   │
│   │   ├── types/
│   │   │   └── auth.types.ts
│   │   │
│   │   └── index.ts
│   │
│   ├── users/
│   │   ├── api/
│   │   ├── components/
│   │   ├── composables/
│   │   ├── schemas/
│   │   ├── types/
│   │   └── index.ts
│   │
│   └── ...
│
├── shared/
│   ├── components/
│   │   ├── ui/
│   │   │   ├── button/
│   │   │   ├── input/
│   │   │   ├── dialog/
│   │   │   ├── table/
│   │   │   └── ...
│   │   │
│   │   ├── atoms/
│   │   │   ├── AppButton.vue
│   │   │   ├── AppInput.vue
│   │   │   ├── AppLabel.vue
│   │   │   ├── AppBadge.vue
│   │   │   └── AppIcon.vue
│   │   │
│   │   ├── molecules/
│   │   │   ├── FormField.vue
│   │   │   ├── SearchInput.vue
│   │   │   └── PaginationControl.vue
│   │   │
│   │   └── organisms/
│   │       ├── AppHeader.vue
│   │       └── AppSidebar.vue
│   │
│   ├── api/
│   │   ├── client.ts
│   │   ├── errors.ts
│   │   └── types.ts
│   │
│   ├── composables/
│   │   ├── useDebounce.ts
│   │   ├── usePagination.ts
│   │   └── useModal.ts
│   │
│   ├── constants/
│   │   ├── routes.ts
│   │   └── app.constants.ts
│   │
│   ├── schemas/
│   │   └── common.schema.ts
│   │
│   ├── types/
│   │   ├── api.types.ts
│   │   └── common.types.ts
│   │
│   └── utils/
│       ├── cn.ts
│       ├── formatDate.ts
│       └── formatCurrency.ts
│
├── assets/
│   ├── images/
│   └── styles/
│       └── main.css
│
├── App.vue
├── main.ts
└── env.d.ts
```

---

# 4. Folder Responsibilities

## `app/`

Chứa application-level configuration và wiring.

Ví dụ:

- Router configuration
- Route guards
- Pinia setup
- TanStack Query setup
- Layouts
- Environment/config mapping

Không đặt business logic cụ thể trong `app/`.

### Dependency example

```text
main.ts
  ↓
app/providers
  ↓
Vue plugins
```

---

## `pages/`

Đại diện cho route-level components.

Page nên chủ yếu thực hiện composition.

Ví dụ:

```vue
<script setup lang="ts">
import LoginForm from '@/features/auth/components/LoginForm.vue'
</script>

<template>
  <LoginForm />
</template>
```

Không nên nhét toàn bộ:

- API calls
- validation
- mutations
- domain rules
- complex UI state

trực tiếp vào Page.

---

# 5. `features/`

Mỗi feature chứa code liên quan đến một business capability.

Ví dụ:

```text
features/auth/
features/users/
features/products/
features/orders/
```

Một feature có thể chứa:

```text
feature/
├── api/
├── components/
├── composables/
├── schemas/
├── stores/
├── types/
└── index.ts
```

Không bắt buộc feature nào cũng phải có tất cả các folder trên.

Chỉ tạo folder khi thực sự cần.

Ví dụ:

```text
features/users/
├── api/
├── components/
└── types/
```

hoàn toàn hợp lệ nếu feature chưa cần store hoặc schema.

---

# 6. `shared/`

`shared/` chỉ chứa code:

- reusable giữa nhiều feature;
- không phụ thuộc business domain cụ thể;
- không mang business meaning của một feature riêng.

Rule:

```text
Dùng trong một feature
→ giữ trong feature.

Dùng ở nhiều feature + generic
→ đưa vào shared.

Dùng ở nhiều feature nhưng mang business meaning
→ cân nhắc tách thành feature/domain riêng.
```

---

# 7. Atomic Design

Atomic Design chỉ áp dụng chủ yếu cho reusable components.

```text
shared/components/
├── ui/
├── atoms/
├── molecules/
└── organisms/
```

## UI primitives

```text
shared/components/ui/
```

Dành cho shadcn-vue generated components.

Ví dụ:

```text
ui/button/
ui/input/
ui/dialog/
ui/table/
```

Không cần ép shadcn component vào `atoms` hoặc `molecules`.

---

## Atoms

Các building blocks nhỏ và generic.

Ví dụ:

```text
AppButton
AppInput
AppLabel
AppBadge
AppIcon
```

Có thể wrap shadcn-vue primitives nếu cần abstraction riêng của project.

```text
shadcn Button
      ↓
AppButton
```

---

## Molecules

Kết hợp nhiều atom/UI primitive.

Ví dụ:

```text
FormField
SearchInput
PaginationControl
```

---

## Organisms

Reusable UI block lớn hơn nhưng vẫn không nên chứa business logic đặc thù.

Ví dụ:

```text
AppHeader
AppSidebar
```

Business-specific organism nên nằm trong feature.

Ví dụ:

```text
features/auth/components/LoginForm.vue
features/users/components/UserTable.vue
```

thay vì đưa chúng vào:

```text
shared/components/organisms/
```

---

# 8. State Management Rules

## Pinia

Dùng cho client/global state.

Ví dụ:

```text
auth session state
sidebar state
theme
wizard state
temporary selections
cross-page client state
```

Không sử dụng Pinia như server-cache mặc định.

Không nên:

```text
GET /users
   ↓
API
   ↓
Pinia users store
```

nếu dữ liệu chỉ là server state.

---

## TanStack Vue Query

Dùng cho server state.

Ví dụ:

```text
GET /users
GET /products
GET /orders
```

Flow:

```text
Component
   ↓
Vue Query
   ↓
Feature API
   ↓
Shared API Client
   ↓
Backend
```

Ví dụ:

```text
UserListPage
   ↓
UserTable
   ↓
useUsersQuery()
   ↓
getUsers()
   ↓
api client
```

---

# 9. API Layer

API client chung:

```text
shared/api/client.ts
```

Ví dụ:

```ts
import { ofetch } from 'ofetch'

export const api = ofetch.create({
  baseURL: import.meta.env.VITE_API_URL,
  credentials: 'include',
})
```

Feature-specific endpoints:

```text
features/users/api/users.api.ts
```

Ví dụ:

```ts
import { api } from '@/shared/api/client'
import type { User } from '../types/user.types'

export function getUsers() {
  return api<User[]>('/users')
}
```

Vue Query:

```text
features/users/api/users.queries.ts
```

```ts
import { useQuery } from '@tanstack/vue-query'
import { getUsers } from './users.api'

export function useUsersQuery() {
  return useQuery({
    queryKey: ['users'],
    queryFn: getUsers,
  })
}
```

---

# 10. Forms & Validation

Feature-specific schemas nằm trong feature.

```text
features/auth/schemas/login.schema.ts
```

Ví dụ:

```ts
import { z } from 'zod'

export const loginSchema = z.object({
  email: z.string().email(),
  password: z.string().min(8),
})
```

Form:

```text
features/auth/components/LoginForm.vue
```

Dùng:

- VeeValidate
- Zod
- `@vee-validate/zod`

Shared validation chỉ dành cho rule generic.

Ví dụ:

```text
shared/schemas/common.schema.ts
```

---

# 11. Dependency Direction

Dependency mong muốn:

```text
App
 ↓
Pages
 ↓
Features
 ↓
Shared
```

UI composition:

```text
Page
 ↓
Feature Component
 ↓
Shared Organism
 ↓
Shared Molecule
 ↓
Shared Atom
 ↓
shadcn-vue primitive
```

API:

```text
Page / Feature Component
        ↓
Feature Query
        ↓
Feature API
        ↓
Shared API Client
        ↓
Backend
```

Không để:

```text
shared → features
```

Không để:

```text
shared → pages
```

Không để:

```text
feature A → internal code của feature B
```

nếu không thực sự cần thiết.

Nếu cần chia sẻ capability giữa các feature, expose thông qua public API (`index.ts`) hoặc cân nhắc domain abstraction phù hợp.

---

# 12. Import Convention

Sử dụng alias:

```text
@/
```

Ví dụ:

```ts
import { api } from '@/shared/api/client'
import LoginForm from '@/features/auth/components/LoginForm.vue'
```

Ưu tiên public exports khi feature lớn:

```ts
import { useAuth } from '@/features/auth'
```

thay vì deep import quá sâu:

```ts
import { useAuth } from '@/features/auth/composables/internal/useAuth'
```

---

# 13. Naming Conventions

Vue components:

```text
PascalCase.vue
```

Ví dụ:

```text
LoginForm.vue
AppButton.vue
UserTable.vue
```

Composables:

```text
useSomething.ts
```

Ví dụ:

```text
useAuth.ts
usePagination.ts
```

Stores:

```text
*.store.ts
```

API:

```text
*.api.ts
*.queries.ts
*.mutations.ts
```

Schemas:

```text
*.schema.ts
```

Types:

```text
*.types.ts
```

---

# 14. Important Architecture Rules

1. Không tạo abstraction chỉ để làm structure trông phức tạp.
2. Không tạo folder rỗng nếu chưa có nhu cầu.
3. Business logic phải ưu tiên nằm trong feature.
4. Shared phải business-agnostic.
5. Pages chỉ nên composition và route-level orchestration.
6. Pinia quản lý client state.
7. TanStack Query quản lý server state.
8. API endpoint definition thuộc feature.
9. API client infrastructure thuộc shared.
10. shadcn-vue primitives nằm trong `shared/components/ui`.
11. Atomic Design chỉ áp dụng cho reusable UI.
12. Không ép mọi component phải được phân loại atom/molecule/organism.
13. Component chỉ dùng riêng cho feature phải ở feature.
14. `app/` chỉ chứa application wiring/configuration.
15. `main.ts` nên càng mỏng càng tốt.

---

# 15. Agent Implementation Prompt

Copy prompt dưới đây cho coding agent sau khi project Vue đã được khởi tạo.

```text
You are working inside an existing Vue 3 + TypeScript + Vite project.

Your task is to establish the frontend architecture and base project structure.

Do NOT implement product/business features.
Do NOT invent application-specific requirements.
Do NOT generate unnecessary placeholder code.
Do NOT over-engineer the project.

## Target stack

Use and prepare the project for:

- Vue 3
- TypeScript
- Vite
- Vue Router
- Pinia
- @tanstack/vue-query
- ofetch
- VeeValidate
- Zod
- @vee-validate/zod
- Tailwind CSS
- shadcn-vue
- Reka UI
- lucide-vue-next
- ESLint
- Prettier

Before installing anything, inspect package.json and the existing project.
Do not reinstall dependencies that already exist.

## Architecture

Use a hybrid architecture consisting of:

- application layer
- route pages
- feature-based business modules
- shared reusable kernel
- Atomic Design for reusable shared UI

Target high-level structure:

src/
├── app/
├── pages/
├── features/
├── shared/
├── assets/
├── App.vue
├── main.ts
└── env.d.ts

## app/

Create application-level infrastructure.

Expected structure where applicable:

src/app/
├── router/
├── providers/
├── layouts/
└── config/

Responsibilities:

- router configuration
- route guards
- plugin/provider registration
- global layouts
- environment/config abstraction

Do not put business logic in app/.

Keep main.ts thin.

## pages/

Create the pages directory as the route-level composition layer.

Pages should eventually depend on features and shared components.

Do not create fake business pages unless required to preserve the current starter application's behavior.

If the starter project currently contains demo views/components, either reorganize or remove them only when doing so is safe and does not break the application.

## features/

Create:

src/features/

Features will contain business-specific code.

A future feature may look like:

feature/
├── api/
├── components/
├── composables/
├── schemas/
├── stores/
├── types/
└── index.ts

IMPORTANT:

Do not create all of these folders eagerly for imaginary features.

Only establish the feature root and any structure justified by existing code.

Document the convention instead of generating many empty directories.

## shared/

Create reusable, business-agnostic infrastructure.

Use:

src/shared/
├── components/
├── api/
├── composables/
├── constants/
├── schemas/
├── types/
└── utils/

Shared code must never depend on feature-specific or page-specific code.

Dependency direction:

app/pages/features -> shared

Never:

shared -> features
shared -> pages

## Atomic Design

Apply Atomic Design only to reusable UI.

Use:

src/shared/components/
├── ui/
├── atoms/
├── molecules/
└── organisms/

`ui/` is reserved for shadcn-vue primitives/generated components.

Do not manually move generated shadcn components into atoms/molecules.

Atoms are small project-level reusable abstractions.

Molecules combine primitives/atoms.

Organisms are larger reusable business-agnostic UI sections.

Feature-specific components must remain inside features.

For example:

Correct:
features/auth/components/LoginForm.vue

Incorrect:
shared/components/organisms/LoginForm.vue

because LoginForm belongs specifically to the auth feature.

## Server state

Use TanStack Vue Query for server state.

Feature API flow should follow:

Feature component
→ feature query/mutation
→ feature API function
→ shared API client
→ backend

Do not store ordinary API query results in Pinia merely to make them globally accessible.

## Client state

Use Pinia for client/global application state such as:

- authentication/session client state
- UI state
- wizard state
- temporary shared selections
- cross-page client state

Avoid duplicating TanStack Query server state in Pinia.

## API Client

Create a reusable ofetch client under:

src/shared/api/client.ts

Use environment configuration rather than hard-coded backend URLs.

Prepare sensible defaults for JSON APIs.

Do not invent authentication/refresh-token behavior unless it already exists in the project.

Create a normalized API error abstraction only if useful and keep it minimal.

## Vue Query Provider

Configure @tanstack/vue-query centrally.

Put application-level setup under:

src/app/providers/

Keep configuration minimal and production-safe.

Register it from main.ts or a clean provider setup.

## Pinia Provider

Keep Pinia application setup in app/providers if it improves separation.

Do not wrap Pinia unnecessarily if a trivial export is sufficient.

## Router

Move or organize Vue Router configuration under:

src/app/router/

Preserve existing routes and application behavior.

Do not invent product routes.

## Forms

Prepare conventions for:

- VeeValidate
- Zod
- @vee-validate/zod

Feature-specific schemas belong inside:

features/<feature>/schemas/

Generic reusable schemas belong inside:

shared/schemas/

Do not generate fake login/register schemas unless corresponding functionality already exists.

## Styling

Preserve and configure Tailwind according to the installed Tailwind version.

Do not copy configuration instructions for an older Tailwind major version without checking the actual installed version.

Prepare the project for shadcn-vue.

If shadcn-vue has not been initialized, initialize it using its official CLI only if it can be done safely and non-interactively from the available project configuration.

Place generated shadcn-vue components under:

src/shared/components/ui

If CLI configuration requires subjective design choices that cannot be inferred, create the architecture first and report the remaining shadcn initialization step instead of inventing choices.

## Import alias

Ensure:

@/* -> src/*

works consistently in:

- TypeScript
- Vite
- Vue
- shadcn-vue configuration where applicable

Prefer imports such as:

@/shared/api/client
@/features/auth
@/app/router

## Naming

Use:

PascalCase.vue for Vue components

useX.ts for composables

*.store.ts for Pinia stores

*.api.ts for endpoint functions

*.queries.ts for Vue Query queries

*.mutations.ts for Vue Query mutations

*.schema.ts for Zod schemas

*.types.ts for types

## Public feature APIs

Features may expose public APIs through:

features/<feature>/index.ts

Avoid unnecessary barrel files at every directory level.

Do not create circular dependencies.

## Existing starter code

Inspect all current Vue starter files before modifying them.

Remove default demo components/assets only when they are no longer used.

Ensure there are no broken imports after restructuring.

Preserve a minimal working home screen if necessary.

## Quality requirements

After implementation:

1. run the formatter
2. run ESLint
3. run TypeScript type checking
4. run the production build

Use the project's existing npm scripts when available.

Fix architecture-related errors you introduce.

Do not suppress TypeScript or ESLint errors using unsafe workarounds.

## Documentation

Create:

docs/frontend-architecture.md

Document:

- stack
- folder responsibilities
- dependency rules
- Pinia vs TanStack Query responsibilities
- Atomic Design conventions
- API flow
- naming conventions
- where new feature code should go
- examples of correct and incorrect placement

Keep documentation concise and aligned with the actual generated repository structure.

## Final report

At the end, report:

1. files/folders created
2. files moved or removed
3. dependencies added
4. configuration changed
5. validation commands executed and results
6. anything intentionally left for later

Do not claim success unless lint/typecheck/build actually pass.

The resulting project should remain runnable and should provide a clean foundation for future feature development without prematurely implementing application-specific functionality.
```

---

# 16. Suggested Final Architecture

For this project, prefer:

```text
src/
├── app/
│   ├── router/
│   ├── providers/
│   ├── layouts/
│   └── config/
│
├── pages/
├── features/
│
├── shared/
│   ├── components/
│   │   ├── ui/
│   │   ├── atoms/
│   │   ├── molecules/
│   │   └── organisms/
│   ├── api/
│   ├── composables/
│   ├── constants/
│   ├── schemas/
│   ├── types/
│   └── utils/
│
├── assets/
├── App.vue
├── main.ts
└── env.d.ts
```

Core principle:

```text
app     = application wiring
pages   = routes/screens
features = business capabilities
shared  = reusable business-agnostic code
```

Combined with:

```text
Pinia          = client state
Vue Query      = server state
ofetch         = HTTP client
VeeValidate    = form state
Zod            = validation/schema
shadcn-vue     = UI primitives
Atomic Design  = reusable component composition
```

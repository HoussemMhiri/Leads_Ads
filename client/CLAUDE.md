## Code Quality — Always

- **Clean**: No dead code, no commented-out blocks, no unused imports or variables. Every line earns its place.
- **Readable**: Code should read like prose. Prefer explicit names over brevity. Avoid implicit behavior and magic values.
- **Maintainable**: One responsibility per function/component. Avoid side effects in components; centralize them in stores/services. Keep components small — if a component grows beyond ~150 lines, split it.
- **Extensible**: Don't hardcode what could be data-driven. Avoid tight coupling between features. New requirements should slot in, not require rewrites.

> When in doubt between clever and clear, always choose clear.

## Before Every Task

Before making any change, always explain:
1. **The problem** — what is wrong or what is being added and why.
2. **The constraints** — what must be preserved or avoided.
3. **The solution** — the chosen approach and why (mention alternatives if relevant).

Only proceed with changes after this explanation.

---

## Foundational Context

This is the Vue 3 + TypeScript SPA for Leads_Ads. You are an expert with this stack.

- **vue** - v3.5
- **vue-router** - v4
- **pinia** - v3
- **@tanstack/vue-query** - v5 (server state)
- **vite** - v6
- **tailwindcss** - v4 (via `@tailwindcss/vite`)
- **reka-ui** - v2 (headless UI primitives)
- **vee-validate** + **@vee-validate/zod** - form validation
- **zod** - schema validation
- **axios** - HTTP client
- **lucide-vue-next** - icons
- **vitest** - unit testing

## Conventions

- Follow existing code conventions. Always check sibling files before creating something new.
- Use descriptive names: `isSubmitting`, not `loading`; `handleSendInvitations`, not `send`.
- Check for existing components (especially in `src/components/ui/`) before writing new ones.

## Application Structure

```
src/
├── features/          # Feature modules (self-contained)
│   ├── auth/
│   │   ├── components/
│   │   ├── schemas/
│   │   ├── services/
│   │   ├── store/
│   │   └── types/
│   └── workspace/
│       ├── components/
│       └── employee/
│           ├── composables/   # Vue Query hooks (useMembers, useRoles, useEmployeeMutations)
│           ├── components/
│           ├── schemas/
│           ├── services/
│           ├── store/         # Only if session state is needed (prefer composables)
│           └── types/
├── components/
│   ├── ui/            # Headless reka-ui primitives (never modify arbitrarily)
│   ├── forms/         # Shared form field wrappers (TextField, etc.)
│   ├── navigation/
│   └── shared/        # Shared utility components (AlertMessage, etc.)
├── layouts/           # MainLayout, AuthLayout
├── views/             # Route-level views (thin wrappers, delegate to features)
├── stores/            # Global Pinia stores (session.store.ts, etc.)
├── router/
├── plugins/           # api.ts (axios instance)
├── utils/             # handleApiError.ts, validators.ts
└── lib/               # utils.ts (cn helper)
```

## Architecture & Practices

### State management — the rule

| State type | Tool | Examples |
|---|---|---|
| **Server state** — fetched from API, can go stale | `@tanstack/vue-query` composables | members, roles, campaigns, leads |
| **Session state** — set at login, lives until logout | Pinia store | auth user, workspace name/logo, CSRF |
| **UI state** — local to a component | `ref()` / `reactive()` | modal open, form input, loading flags |

### Vue Query (server state)

- All data fetched from the API must use `useQuery` / `useMutation` — **never** Pinia for server data.
- Composables live in `features/{feature}/composables/` and are named `use*.ts`.
- `queryKey` must be stable, descriptive arrays: `['members']`, `['campaigns', id]`.
- Mutations must call `queryClient.invalidateQueries()` in `onSuccess` to keep cache fresh.
- Handle errors in mutation `onError` callbacks using `parseApiError(err).message`.
- `useQueryClient()` is only valid inside `setup()` — never call it outside component context.

```typescript
// features/workspace/employee/composables/useMembers.ts
import { useQuery } from '@tanstack/vue-query'
import { employeeService } from '../services/employee.service'

export function useMembers() {
  return useQuery({
    queryKey: ['members'],
    queryFn: () => employeeService.getMembers(),
  })
}

// features/workspace/employee/composables/useEmployeeMutations.ts
import { useMutation, useQueryClient } from '@tanstack/vue-query'

export function useRemoveMember() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => employeeService.removeMember(id),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['members'] }),
  })
}
```

Usage in a component:
```typescript
const { data: members, isLoading, isError, error } = useMembers()
const { mutate: removeMember, isPending } = useRemoveMember()

// Calling a mutation with per-call callbacks:
removeMember(id, {
  onSuccess: () => emit('update:open', false),
  onError: (err) => { errorMsg.value = parseApiError(err).message },
})
```

### Pinia (session state)

- Use Pinia **only** for state that arrives with login and survives navigation: auth user, workspace info, CSRF.
- Always use the **setup store** (composition API) pattern — never options stores.
- Use a `withLoading()` helper for async Pinia actions.
- Use a **service** for all API calls — never call `api` directly from a component or store method.
- Keep **views thin** — no business logic, no direct API calls. Delegate to feature components.
- Favor composables over prop-drilling more than 2 levels deep.
- Do not introduce abstractions without a real, present need.
- When unsure about a Vue 3 pattern, check the official Vue or Pinia docs before proceeding.

---

## Packages & Tooling

- **Always ask for confirmation before adding a new npm package**, and explain:
  - what problem it solves
  - why it's better than a custom solution
  - trade-offs (if any)

---

## Components

- Always use `<script setup lang="ts">`.
- Define props with `defineProps<{ ... }>()`, emits with `defineEmits<{ ... }>()`.
- Use `v-model` update pattern: `defineEmits<{ 'update:modelValue': [value: string] }>()`.
- Import reactive Vue APIs explicitly: `import { ref, computed, watch } from 'vue'`.
- Use `storeToRefs()` to destructure reactive store state.
- Use `lucide-vue-next` for all icons.
- Use `cn()` from `@/lib/utils` for conditional Tailwind classes.

```vue
<script setup lang="ts">
import { ref, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { cn } from '@/lib/utils'

const props = defineProps<{
  label: string
  disabled?: boolean
}>()

const emit = defineEmits<{
  submit: [value: string]
}>()
</script>
```

## Responsive Design

- **All UI must be mobile-first and fully responsive.** Design for small screens first, then scale up with `sm:`, `md:`, `lg:` breakpoints.
- Use `flex-col` / `flex-row` with breakpoint switches, `grid` with `grid-cols-1 sm:grid-cols-2`, and `w-full sm:w-auto` patterns.
- Touch targets must be at least 44px tall on mobile.
- Never rely on hover-only interactions — ensure tap/click works on touch devices.

## UI Components (shadcn-vue)

This project uses **shadcn-vue** components. Always check what is already installed before building anything custom.

**Available components** (in `src/components/ui/`):

| Component  | Import path                    |
| ---------- | ------------------------------ |
| `Accordion`| `@/components/ui/accordion`    |
| `Button`   | `@/components/ui/button`       |
| `Dialog`   | `@/components/ui/dialog`       |
| `Field`    | `@/components/ui/field`        |
| `Form`     | `@/components/ui/form`         |
| `Input`    | `@/components/ui/input`        |
| `Label`    | `@/components/ui/label`        |
| `Select`   | `@/components/ui/select`       |
| `Separator`| `@/components/ui/separator`    |
| `Sheet`    | `@/components/ui/sheet`        |
| `Sidebar`  | `@/components/ui/sidebar`      |
| `Skeleton` | `@/components/ui/skeleton`     |
| `Spinner`  | `@/components/ui/spinner`      |
| `Tooltip`  | `@/components/ui/tooltip`      |

- **Before building any UI element**, check this list. If a component exists, use it — do not re-implement it.
- If a needed component is missing, use the **shadcn MCP** (available in this session) to search the registry and install it: ask the MCP `list_components` or `add_component` directly. Do not ask the user to run CLI commands for component installation.
- Complex components (Dialog, Select, Sidebar, Sheet) have subcomponent exports via `index.ts` barrel files.
- Use CVA (`class-variance-authority`) for variant-based styling. See `Button` as the reference.
- Use the `cn()` utility for all dynamic class composition:

```typescript
import { cn } from '@/lib/utils'
// cn('base-class', condition && 'conditional-class', props.class)
```

## Stores (Pinia)

Pinia is **only for session state** — data that arrives at login and persists until logout. Do not use Pinia to fetch or cache server data; use Vue Query composables for that.

- Always use the **setup store** (composition API) pattern — never options stores.
- Store naming: `useFeatureNameStore` (e.g., `useAuthStore`, `useWorkspaceStore`).
- Store key (first argument): camelCase string matching the name (e.g., `'authStore'`).
- State is `ref()`, computed properties are `computed()`.
- Use a `withLoading()` helper for any async Pinia action (e.g., login, logout, workspace update).
- Always explicitly return the public interface from the store.

## API Calls (Services)

- All API calls go through the configured axios instance: `import api from '@/plugins/api'`.
- Services are plain objects exported as `const featureService = { ... }`.
- Use typed generics: `api.get<ResponseType>('/path')`.
- Use the `prefix` config option to namespace endpoints — see `ENDPOINT_PREFIXES` in `plugins/api.ts`.
- Always return `res.data` directly.
- Services throw errors — stores catch them via `withLoading()`.

```typescript
import api from '@/plugins/api'
import type { Item } from '../types/example.types'

export const exampleService = {
  async getAll(): Promise<Item[]> {
    const res = await api.get<Item[]>('/items', { prefix: 'employees' })
    return res.data
  },

  async create(data: CreateItemData): Promise<Item> {
    const res = await api.post<Item>('/items', data, { prefix: 'employees' })
    return res.data
  },
}
```

- Use `skipAuthRedirect: true` on calls that run during auth initialization (e.g., `getCurrentUser`, `getCurrentEmployee`).

## Forms (vee-validate + Zod)

- Define schemas with Zod in `features/{feature}/schemas/{feature}.schema.ts`.
- Export inferred TypeScript types alongside each schema: `export type SignupInput = z.infer<typeof signupSchema>`.
- Use `toTypedSchema()` from `@vee-validate/zod` to bridge Zod → vee-validate.
- Use `useForm({ validationSchema, initialValues })` in components, not field-level validation.
- Use `handleSubmit()` to wrap submission handlers.
- Use shared `emailSchema` from `@/utils/validators` — do not redefine email validation.

```typescript
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { mySchema } from '../schemas/my.schema'

const { handleSubmit, errors } = useForm({
  validationSchema: toTypedSchema(mySchema),
  initialValues: { field: '' },
})

const onSubmit = handleSubmit(async (values) => {
  await store.doAction(values)
})
```

- Use the `TextField` component from `@/components/forms/TextField.vue` for text/password inputs — it handles vee-validate field binding, label, error display, and password toggle.
- Use `AlertMessage` from `@/components/shared/AlertMessage.vue` to display store-level `error` and `successMessage`.

## Error Handling

- Use `parseApiError(error)` from `@/utils/handleApiError` to parse Axios errors into `ParsedError`.
- Use `handleApiError(error)` when you only need the error message string.
- Use `isAuthError()`, `isValidationError()` helpers for conditional error logic.
- Never swallow errors silently — always set `error.value` in the store or surface to the user.

## Types

- Define types in `features/{feature}/types/{feature}.types.ts`.
- Use `interface` for API response shapes and data models.
- Use discriminated unions for multi-state responses (see `MeResponse` in `session.service.ts`).
- Import types with `import type { ... }`.

## Router

- Route **names** use camelCase: `'dashboard'`, `'forgotPassword'`, `'employeeSignin'`.
- Route **paths** use kebab-case: `/sign-in`, `/forgot-password`, `/employee/sign-in`.
- Use `meta: { requiresAuth: true }` or `meta: { requiresGuest: true }` for guards.
- Lazy-load non-critical routes: `component: () => import('@/views/...')`.
- Always navigate using named routes: `router.push({ name: 'dashboard' })`.

## Layouts

- `MainLayout` — authenticated app shell with sidebar (`SidebarProvider` + `AppSidebar`).
- `AuthLayout` — split-screen layout for all auth pages.
- Views should not contain layout logic — delegate to the appropriate layout via the router.

## Views

- Views are thin wrappers: minimal markup, delegate to feature components.
- Do not put business logic, API calls, or store interactions directly in views — use feature components.

## Path Aliases

- Always use `@/` for absolute imports from `src/`: `import { cn } from '@/lib/utils'`.
- Never use deep relative paths (`../../..`).

## Testing

- Tests live in `src/__tests__/` or colocated `*.spec.ts` files.
- Use Vitest for unit tests.
- Run tests: `npm run test:unit`.

## Development Commands

- `npm run dev` — start Vite dev server
- `npm run build` — production build (also runs type-check)
- `npm run type-check` — TypeScript check only
- `npm run lint` — ESLint with auto-fix
- `npm run format` — Prettier formatting
- `npm run test:unit` — run Vitest tests

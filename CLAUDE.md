# Leads_Ads — Project Root

## Monorepo Structure

```
Leads_Ads/
├── server/    # Laravel 12 API (PHP 8.4)
└── client/    # Vue 3 + TypeScript SPA
```

Each side has its own `CLAUDE.md` with detailed conventions — always read them:

- `server/CLAUDE.md` — Laravel, PHP, Pest, architecture rules
- `client/CLAUDE.md` — Vue 3, TypeScript, Pinia, API conventions

---

## Architecture Overview

This is a **multi-tenant SaaS** application with two distinct user types:

| Type         | Auth Guard        | Database                  | Session key            |
| ------------ | ----------------- | ------------------------- | ---------------------- |
| **Owner**    | `web` / `sanctum` | Central DB                | standard session       |
| **Employee** | `employee`        | Tenant DB (per-workspace) | `tenant_id` in session |

Tenancy is resolved **server-side via session** (`InitializeTenancyBySession` middleware). The client does not manage tenant context — it only passes cookies.

---

## API Contract

### Transport

- **Base URL**: `http://localhost:8000` (dev) — configured via `VITE_BACKEND_BASE_URL`
- **Auth**: Cookie-based sessions (`withCredentials: true`) + XSRF token (`withXSRFToken: true`)
- **Content**: Always `Accept: application/json`
- No Bearer tokens. No API keys. Cookies only.

### Endpoint Namespaces

| Prefix key    | URL prefix              | Scope                             |
| ------------- | ----------------------- | --------------------------------- |
| `auth`        | `/api/auth`             | Owner register/login/logout/OAuth |
| `employees`   | `/api/employees`        | Employee auth + workspace actions |
| `connections` | `/api/meta/connections` | Meta ad connections               |
| `campaigns`   | `/api/meta/campaigns`   | Meta campaigns                    |

### Session Restore Endpoint

`GET /api/me` — always returns `200`, never `401`. Used on app boot to restore auth state.

**Response shape** (discriminated union):

```json
// Owner logged in
{ "type": "owner", "user": { "id", "name", "email", "avatar", "tenant": { "id", "subdomain", "company_name" } } }

// Employee logged in
{ "type": "employee", "employee": { "id", "name", "email", "role" }, "tenant": { "id", "workspace" } }

// Nobody logged in
{ "type": null }
```

### Error Shape (Laravel validation — HTTP 422)

```json
{ "message": "...", "errors": { "field": ["error message"] } }
```

### Auth Flow

1. Client boots → calls `GET /api/me` → restores session state into stores
2. Owner login → `POST /api/auth/login` → sets session cookie
3. Employee login → `POST /api/employees/login` → resolves tenant from email, sets `tenant_id` in session
4. On `401`: interceptor in `client/src/plugins/api.ts` auto-redirects to the correct login page

---

## Dev Commands

### Server (`server/`)

```bash
composer run dev        # Start Laravel dev server + queue + logs
php artisan test --compact          # Run all tests
vendor/bin/pint --dirty             # Format changed PHP files
```

### Client (`client/`)

```bash
npm run dev             # Start Vite dev server
npm run build           # Production build (runs type-check first)
npm run type-check      # TypeScript check only
npm run lint            # ESLint with auto-fix
npm run test:unit       # Vitest unit tests
```

---

## Route Files

| File                        | Purpose                                        |
| --------------------------- | ---------------------------------------------- |
| `server/routes/api.php`     | Owner auth, email verification, OAuth, session |
| `server/routes/tenant.php`  | Employee auth + workspace routes (tenant DB)   |
| `server/routes/web.php`     | Web routes (minimal)                           |
| `server/routes/console.php` | Scheduled commands                             |

# Mudes.co — Backend Architecture

| Field | Value |
|---|---|
| Document | BACKEND_ARCHITECTURE.md |
| Companion Documents | PROJECT_SPECIFICATION.md v1.0.0, DATABASE_SPECIFICATION.md v1.0.0 |
| Version | 1.0.0 |
| Status | Draft for Development |
| Scope | Laravel 12 / PHP 8.4 API-first backend only. Frontend (Next.js) is out of scope for this document. |
| Date | 2026-07-18 |

This document is the implementation guide for all Laravel development on Mudes.co. It contains **no PHP code, no migrations, no models, no controllers** — every rule here must be translated into actual classes by the implementer exactly as described. Concrete modules referenced throughout (Articles, Gallery, Activities, Study Schedule, Attendance, Digital Library, Announcements, Organization, Media, Settings) are the ones defined in `PROJECT_SPECIFICATION.md` §3 and `DATABASE_SPECIFICATION.md`.

---

## Table of Contents

1. Introduction
2. Architecture Style
3. Folder Structure
4. Request Lifecycle
5. Service Layer
6. Repository Layer
7. DTO Layer
8. API Resource Layer
9. Validation Layer
10. Authentication
11. Authorization
12. Middleware
13. Events
14. Listeners
15. Jobs
16. Notifications
17. Cache Strategy
18. File Storage
19. Logging Strategy
20. Error Handling
21. Security Architecture
22. Performance Strategy
23. Scalability
24. Development Standards
25. Testing Architecture
26. Module Template
27. Development Workflow
28. Future Architecture

---

## 1. Introduction

### 1.1 Architecture Goals

- **API-first:** the backend has no view layer beyond transactional email templates; every feature is reachable only through the REST API defined in `PROJECT_SPECIFICATION.md` §12, consumed by Next.js and, eventually, other clients.
- **Long-life maintainability under rotating contributors:** Mudes.co is community-run software, not a funded product with a stable team. The architecture must let a new volunteer developer change one module safely without first understanding the whole codebase.
- **Testability without a live server:** business logic must be exercisable in isolation, without booting an HTTP kernel.
- **Auditability by default:** every mutation is attributable and reversible-in-spirit (soft delete), per `DATABASE_SPECIFICATION.md` §2.

### 1.2 Design Principles

- Dependencies point inward: outer layers (HTTP) depend on inner layers (business rules), never the reverse.
- Every cross-layer boundary is a typed contract (an interface, a DTO, an API Resource) — never a raw array or a framework request object passed further than it needs to go.
- Nothing is built for a hypothetical future need; every abstraction in this document exists because a named requirement in `PROJECT_SPECIFICATION.md` needs it. Where a seam is deliberately left for a *documented* future item (§24 of that spec), this document says so explicitly rather than silently over-building.

### 1.3 System Overview

One Laravel 12 application, one PostgreSQL 16+ database, one Redis instance (cache + session + queue broker), serving a versioned REST API under `/api/v1`. Authentication is Sanctum SPA-session based for the Next.js admin dashboard. There is no server-rendered admin UI and no public user accounts (per `PROJECT_SPECIFICATION.md` §3.3) — the backend's only two audiences are authenticated staff and anonymous public API consumers.

### 1.4 Architecture Decisions (ADR Summary)

| Decision | Alternative Considered | Rationale |
|---|---|---|
| Clean Architecture layering (Domain/Application/Infrastructure) over "thin-model, fat-controller" default Laravel | Default Laravel MVC | Rotating volunteer contributors need enforced boundaries; a default MVC app concentrates logic in Controllers/Models where it's hardest to test and easiest to duplicate |
| Repository pattern, interface-first | Direct Eloquent calls in Services | Enables Service unit tests with fakes; isolates the one place persistence technology is known |
| DTOs at the Controller→Service boundary | Passing `Request`/arrays into Services | Keeps Services framework-agnostic and independently unit-testable; type safety at the boundary |
| Eloquent Models used as the Domain layer's entities | Fully framework-agnostic Domain (POPOs + a separate persistence mapping layer) | Pragmatic compromise: full Domain purity would double the boilerplate for a project this size with no team to maintain it; the trade-off (Domain technically depends on Eloquent) is accepted and named here rather than hidden |
| Action classes used only when logic is shared by ≥2 Services/Jobs/Commands | An Action class per use case (CQRS-style) | Avoids ceremony; most use cases fit naturally in one Service method, so a mandatory Action-per-use-case layer would be unused abstraction |
| `varchar` + `CHECK` for status fields, mirrored as PHP native enums in the Domain layer | Native Postgres `ENUM` type | Already decided in `DATABASE_SPECIFICATION.md` §2.6; this document adds that the same value set is expressed as a PHP `enum` in `Domain/Enums` so application code gets compile-time exhaustiveness checking on top of the DB's runtime constraint |
| Event-driven cache invalidation, long TTL as fallback only | Short TTL, no explicit invalidation | Already decided in `PROJECT_SPECIFICATION.md` §7.2; public content must reflect edits immediately, not after a cache window |
| No search engine (Meilisearch/Typesense) at launch | Add a search engine now | Already decided in `DATABASE_SPECIFICATION.md` §16; `ILIKE` behind a Repository interface is the correct rung of the ladder until volume proves otherwise |
| No refresh-token flow | Implement token refresh | Sanctum has no native refresh-token concept and no client in scope needs long-lived detached tokens; would be speculative infrastructure |
| Single Laravel monolith, not services-per-module | Microservices split (e.g., Attendance as its own service) | Project scope (one small organization) doesn't justify distributed-systems cost; the Repository/Service contract boundaries are exactly what would make a future split tractable, so that option isn't foreclosed — it's just not exercised now |

---

## 2. Architecture Style

### 2.1 Why Clean Architecture

Clean Architecture is chosen specifically because this codebase will outlive any single contributor. Its value here is not theoretical purity — it is that a volunteer developer working on, say, the Digital Library module six months from now can change how documents are stored (Infrastructure) without touching how download eligibility is decided (Domain/Application), and can write a Service-level test without a database at all. The cost (more files per module, an explicit interface for every Repository) is paid once per module and is enumerated exactly in Section 26 (Module Template) so it never requires re-deriving.

### 2.2 Layers and Responsibilities

| Layer | Folder | Responsibility | Must Never |
|---|---|---|---|
| **Presentation** | `app/Http/` | Translate HTTP ⇄ Application layer: routing, middleware, request validation, response shaping | Contain business rules or call Eloquent/query builder directly |
| **Application** | `app/Application/` | Orchestrate one use case per method: compose Domain rules and Infrastructure calls inside a transaction boundary | Know about `Illuminate\Http\Request`, HTTP status codes, or JSON shape |
| **Domain** | `app/Domain/` | The business entities (Models), the rules attached to them (Policies), the state changes they emit (Events), and their value sets (Enums) | Depend on `app/Infrastructure` or `app/Http` classes |
| **Infrastructure** | `app/Infrastructure/` | Implement Domain-facing contracts: persistence (Repositories), side effects triggered by persistence (Observers), reactions to Domain Events that touch external systems (Listeners), deferred work (Jobs) | Contain business decisions — it executes decisions the Application/Domain layers already made |
| **Shared** | `app/Shared/` | Cross-cutting code with no business meaning of its own: generic traits, base exception classes, small framework-agnostic helpers | Contain anything specific to one module |

**Dependency rule:** Presentation → Application → Domain ← Infrastructure. Infrastructure *implements* interfaces declared in the Domain/Application layer; the Domain layer never imports a class from `Infrastructure`. Shared is a leaf every layer may depend on; it depends on nothing project-specific.

---

## 3. Folder Structure

```
app/
├── Http/                              # Presentation layer
│   ├── Controllers/Api/V1/            # One controller per resource, thin — see §4
│   ├── Middleware/                    # See §12
│   ├── Requests/                      # Form Requests, one per mutating endpoint — see §9
│   └── Resources/                     # API Resources, one per Model — see §8
│
├── Application/                       # Application layer
│   ├── Services/                      # One per module, e.g. ArticleService — see §5
│   ├── DTO/                           # Create/Update DTOs and cross-repository Output DTOs — see §7
│   └── Actions/                       # Single-purpose classes reused by ≥2 Services/Jobs — see §5.2
│
├── Domain/                            # Domain layer
│   ├── Models/                        # Eloquent models, treated as domain entities — see §2.3
│   ├── Policies/                      # One per Model, mirrors the permission matrix — see §11
│   ├── Events/                        # Domain events — see §13
│   └── Enums/                         # PHP enums mirroring DATABASE_SPECIFICATION §5's CHECK catalog
│
├── Infrastructure/                    # Infrastructure layer
│   ├── Repositories/
│   │   ├── Contracts/                 # XRepositoryInterface — see §6
│   │   └── Eloquent/                  # XRepository implements XRepositoryInterface
│   ├── Observers/                     # Audit columns, cache invalidation — see §6, §17
│   ├── Listeners/                     # Reactions to Domain Events — see §14
│   └── Jobs/                          # Queued work — see §15
│
├── Shared/                            # Cross-cutting, no business meaning
│   ├── Traits/                        # e.g. HasUuid, HasAuditColumns (applied to every Domain Model)
│   ├── Support/                       # ApiResponse envelope helper, generic filter/sort value object
│   ├── Helpers/                       # Small stateless functions used across modules
│   └── Exceptions/                    # DomainException base class — see §20
│
├── Console/                           # Artisan commands (e.g., the nightly statistics snapshot trigger)
├── Exceptions/                        # Laravel's Exception Handler wiring (bootstrap/app.php withExceptions) — see §20
└── Providers/                         # RepositoryServiceProvider (interface bindings), EventServiceProvider, etc.
```

**Why Models live under `Domain/` instead of the Laravel-default `app/Models`:** this is a deliberate deviation from Laravel's scaffold default, made to keep the Domain layer's ownership of its entities visually explicit in the tree. It requires no extra configuration — Laravel autoloads everything under `app/` via PSR-4 regardless of subfolder, and any config referencing the model class (e.g., `config/auth.php`'s `providers.users.model`) simply points at the new namespace.

**Why `Actions/` sits under `Application/`, not as a top-level folder:** an Action is application-orchestration logic that happens to be reused outside a single Service (e.g., `GenerateAttendanceQrTokenAction`, used by both `AttendanceService` and a scheduled Job that pre-opens the next week's sessions). It is not a separate architectural layer — promoting it to a top-level directory would imply it is, which overstates its role.

This structure supersedes the simpler flat sketch in `PROJECT_SPECIFICATION.md` §8; the mapping is 1:1 (e.g., that document's `app/Repositories/Contracts` is this document's `app/Infrastructure/Repositories/Contracts`) — nothing already agreed changes in substance, only the grouping.

---

## 4. Request Lifecycle

```
HTTP Request
  ↓
Route (routes/api.php, versioned under /api/v1)
  ↓
Middleware (auth:sanctum, throttle, permission check, request logging — see §12)
  ↓
Controller (Http/Controllers/Api/V1/{X}Controller — resolves the Form Request via method injection)
  ↓
Form Request Validation (Http/Requests/{Store|Update}{X}Request — rules() + authorize(), runs before the controller method body executes)
  ↓
DTO (built by the DTO's own static factory, e.g. CreateArticleDTO::fromRequest($request))
  ↓
Service (Application/Services/{X}Service — opens a DB transaction, applies business rules)
  ↓
Repository (Infrastructure/Repositories/Eloquent/{X}Repository — the only place Eloquent query builder is used)
  ↓
Model (Domain/Models/{X} — Observer fires here: audit columns populated, cache tag invalidated)
  ↓
Database (PostgreSQL — constraints from DATABASE_SPECIFICATION.md are the final guarantee)
  ↓
API Resource (Http/Resources/{X}Resource — shapes the returned Model, never exposes it raw)
  ↓
JSON Response (standard envelope — see §8.2)
```

**Read path** is identical through Middleware/Controller, then diverges: Controller → Service (or, for simple unfiltered reads, the Controller may call the Repository directly through its interface — see §5.1 for when this is acceptable) → Repository (checks cache first per §17, falls back to DB) → API Resource → JSON Response. A read path never needs a DB transaction.

**Every arrow is a hard boundary:** a Controller may not call a Repository directly for a *mutating* action (must go through a Service, so the transaction and business rules are never bypassed); a Service may not build its own Eloquent query (must go through a Repository, so persistence stays swappable and testable).

---

## 5. Service Layer

### 5.1 Responsibilities

A Service orchestrates exactly one use case per public method (e.g., `ArticleService::create()`, `ArticleService::publish()`, `AttendanceService::checkIn()`). It is the only layer allowed to open a DB transaction and the only layer allowed to dispatch a Domain Event as part of a use case's success path.

**When a Controller may skip the Service and call a Repository directly:** for a plain, unfiltered `index`/`show` read with no business rule attached (e.g., listing published articles) — reading is not a "use case" in the same sense a mutation is. The moment a read requires business logic (e.g., "only show occurrences the current user's role permits," or aggregating across repositories for a dashboard), it moves into a Service (or, for cross-repository reads, produces an Output DTO — see §7.3).

### 5.2 Naming Convention

`{Module}Service`, e.g. `ArticleService`, `AttendanceService`, `OrganizationPositionService`. Methods are named for the domain action, not generically after CRUD (`publish()`, `checkIn()`, `cancelOccurrence()`), except where the action genuinely is a plain create/update/delete with no further nuance.

### 5.3 Dependency Injection

Constructor-injected: Repository *interfaces* (never concrete Eloquent Repository classes) and other Services. Bindings are registered in `Providers/RepositoryServiceProvider`. A Service must never `new` a Repository or another Service.

### 5.4 Transaction Handling

Every Service method that mutates state wraps its Repository calls in `DB::transaction()`. Domain Events with listeners that trigger external side effects (email, cache flush on a distributed cache, notifications) are configured to dispatch **after commit** (`ShouldDispatchAfterCommit`/`afterCommit` on queued listeners), so no side effect fires against data that could still roll back.

### 5.5 Business Rules

Business rules are, specifically, anything the database's `CHECK` constraints in `DATABASE_SPECIFICATION.md` §8 *cannot* express because they require traversal, cross-table lookup, or time-of-request context:

| Rule | Enforced In |
|---|---|
| Organization position cannot become its own ancestor | `OrganizationPositionService` (requires recursive traversal — DB cannot check this) |
| Attendance check-in only accepted while session window is open | `AttendanceService` (requires `now()` comparison against `opens_at`/`closes_at` plus business framing, not just the DB's `CHECK` on the window's own validity) |
| Scheduled article flips to published only via the queued job at `published_at`, not by any other write path | `ArticleService` + `PublishScheduledArticles` Job |
| Only one organization period may be active — Service still checks-and-deactivates-the-previous-one explicitly, even though the DB's partial unique index is the final guarantee | `OrganizationPeriodService` |

### 5.6 Error Handling

Services throw Domain-meaningful exceptions (`AttendanceWindowClosedException`, `OrganizationPositionCycleException`) — never an HTTP exception, never a raw framework exception. See §20 for how these are translated to responses.

### 5.7 Example (Narrative, Not Code)

`AttendanceService::checkIn(CheckInDTO $dto)`: loads the `attendance_sessions` row by `qr_token` via `AttendanceSessionRepositoryInterface`; throws `AttendanceWindowClosedException` if `now()` is outside `[opens_at, closes_at]`; within a transaction, creates the `attendances` row via `AttendanceRepositoryInterface` (relying on the DB's partial unique index to reject a duplicate check-in for a known member); dispatches `AttendanceRecorded` after commit.

---

## 6. Repository Layer

### 6.1 Interfaces and Implementation

One interface per aggregate-root table (`ArticleRepositoryInterface`, `AttendanceSessionRepositoryInterface`, etc.) in `Infrastructure/Repositories/Contracts`, with exactly one Eloquent implementation in `Infrastructure/Repositories/Eloquent`, bound in `RepositoryServiceProvider`. Interfaces expose domain-meaningful methods (`findPublishedBySlug()`, `findActiveSessionForSource()`), not a generic `find($id)`/`all()` CRUD-only surface, wherever the module's queries have real shape.

### 6.2 Database Responsibility

The Repository is the **only** place in the codebase permitted to reference Eloquent's query builder (`::where()`, `::with()`, `::orderBy()`, etc.). This is what makes "Services never write raw SQL" (§21) a structural guarantee rather than a style preference — a Service has no query-builder dependency to reach for in the first place.

### 6.3 Caching Strategy

Repositories backing public, read-heavy endpoints (Article, Announcement, StudySchedule, OrganizationProfile, Gallery listing — see §17 for the full list) wrap their read methods in a Redis cache lookup before hitting the database. Admin-only or low-traffic repositories are not cached.

### 6.4 Search Strategy

Each Repository interface that needs it exposes a `search(string $term, Filter $filter)` method. At launch this is implemented with Postgres `ILIKE` (per `DATABASE_SPECIFICATION.md` §16's explicit decision to defer full-text search until volume demands it). Because the method lives behind an interface, swapping the implementation for a dedicated search engine later (§28) never touches a Service or Controller.

### 6.5 Pagination

Every list-returning Repository method returns Laravel's `LengthAwarePaginator` (never a raw `Collection`), so the API Resource layer (§8.3) can populate `meta` consistently across every module without per-module special-casing.

### 6.6 Filtering and Sorting

A single generic `QueryFilter` value object (in `Shared/Support`) carries an allow-listed set of filter keys, sort column, and sort direction, validated against a per-Repository allow-list declared as a constant on the Repository (e.g., `Article::FILTERABLE = ['category_id', 'status']`). This is intentionally one shared mechanism, not a bespoke filter class per module — the filtering need (equality/range filters plus one sort column) is the same shape everywhere in this schema, so one implementation covers it (ladder rung 6/7 in the project's engineering conventions).

---

## 7. DTO Layer

### 7.1 Why DTOs Are Used

DTOs exist to keep the Application layer from depending on `Illuminate\Http\Request` — a Service that only ever receives a typed, immutable object can be unit-tested with a plain constructor call, no HTTP kernel, no `FormRequest` bootstrapping. They also close off an entire class of bugs where a Service reads an array key that doesn't exist because a validation rule changed but the Service's array-key assumption didn't.

### 7.2 Input DTOs

`Create{X}DTO`, `Update{X}DTO` — `readonly` classes, one constructor, constructed exclusively via a static factory `fromRequest(FormRequest $request): self`. This factory is the **only** place in the codebase, besides the Form Request itself, that calls `$request->validated()`.

### 7.3 Output DTOs

Not used for the common case — a Domain Model flows from Service back to Controller and is shaped by an API Resource (§8). Output DTOs are reserved for the specific case where a Service's result isn't a single Model: e.g., `DashboardSummaryDTO` in the Statistics module, which aggregates reads from several Repositories (`AttendanceRepository`, `ArticleRepository`, `LibraryDocumentRepository`) into one payload for the dashboard endpoint in `PROJECT_SPECIFICATION.md` §17.

### 7.4 Validation DTOs

Validation itself belongs entirely to the Form Request layer (§9) — a DTO is constructed only from already-validated input. A DTO's constructor may still assert a type invariant (e.g., a non-empty string, a valid enum case) as a last defensive line, but this is not a duplicate of business validation — it's a guarantee that the DTO can never represent an invalid state, useful because DTOs are also constructed directly in tests without going through a Form Request.

### 7.5 Mapping Strategy

Mapping from `FormRequest` (or from a Model, for the rare Output DTO built off multiple sources) to DTO lives on the DTO's own static factory — not in a separate `{X}Mapper` class. For a codebase this size, a proliferation of one-off Mapper classes would be indirection without benefit; colocating the mapping with the type it produces is the smaller, equally clear diff.

---

## 8. API Resource Layer

### 8.1 Transformation Rules

One Resource per Model (`ArticleResource`, `GalleryResource`, …). A Resource is an explicit allow-list of fields — it never exposes a Model attribute it doesn't name. Relationships are included only via `whenLoaded()`, so a Controller that forgets to eager-load a relationship gets a missing key in the JSON (loud, visible in a Feature test) rather than a silent N+1 query.

### 8.2 Response Standard

Every endpoint, success or failure, returns the envelope defined in `PROJECT_SPECIFICATION.md` §12.14:

```json
{ "success": true, "message": "string", "data": {}, "meta": {}, "errors": null }
```

This envelope is produced by one shared helper (`Shared/Support/ApiResponse`), called from every Controller — the shape is defined exactly once, not reimplemented per Controller.

### 8.3 Pagination Response

When a Controller returns a paginated Repository result, `ApiResponse` populates `meta` from the paginator automatically (`current_page`, `per_page`, `total`, `last_page`) — a Controller never manually builds this structure.

### 8.4 Collection Response

`{X}Resource::collection($items)` wrapped in the same envelope; `data` holds the array of transformed items, `meta` carries pagination when the source was a paginator (empty object otherwise).

### 8.5 Error Response

Same envelope, `success: false`, `data: null`, `errors` populated per §20's exact shape. No Controller constructs an error response manually — it is always the output of the centralized Exception Handler.

---

## 9. Validation Layer

### 9.1 Form Request

One per mutating endpoint (`Store{X}Request`, `Update{X}Request`). Owns both `rules()` and `authorize()` — `authorize()` always delegates to the relevant Policy method (§11), never re-implements a role check inline.

### 9.2 Custom Rules

Invokable `Rule` classes for cross-field or business-shaped validation that doesn't require a DB round-trip, mirroring the `CHECK` constraints in `DATABASE_SPECIFICATION.md` §8 as a fast-fail UX layer — e.g. `EndAfterStart` (mirrors `chk_activities_end_after_start`), `ExactlyOneSource` (mirrors `chk_library_documents_source_exclusive`). This is deliberate duplication of the DB constraint, not redundancy: the DB constraint is the final guarantee against any write path (including a future one that bypasses the Form Request); the custom Rule is what turns a violation into a clean 422 instead of a 500 from a caught `QueryException`.

### 9.3 Business Validation

Anything requiring a DB lookup beyond a single-column uniqueness check (e.g., "no other organization period may already be `is_active`") is validated in the Form Request via a DB-aware rule (`Rule::unique`, or a small custom rule querying the Repository), so the client gets a normal 422 — it is not deferred to a Service-thrown exception that would surface as a less specific error.

### 9.4 File Validation

Media uploads validated per collection via Laravel's `file`/`mimes`/`max` rules, matching the type/size limits in `PROJECT_SPECIFICATION.md` §18 (images: jpg/png/webp; documents: pdf/mp3/mp4).

### 9.5 Authorization Validation

Exclusively via the Form Request's `authorize()` → Policy. Never a manual `if ($user->hasRole(...))` inside `rules()` or the Controller.

---

## 10. Authentication

| Concern | Design |
|---|---|
| Mechanism | Laravel Sanctum, SPA stateful-cookie authentication for the Next.js admin dashboard |
| Token strategy | Personal access tokens reserved for any future non-browser client; each token is named and scoped to explicit abilities (least privilege) — no unscoped "god tokens" issued |
| Session strategy | Sanctum cookie session, Redis session driver, `SameSite=Lax`, `Secure` in production, domain configured via `SANCTUM_STATEFUL_DOMAINS` |
| Admin Login | `POST /api/v1/auth/login` — credential check, rate-limited 5/min per IP+email (named limiter, §12), session/cookie issued on success |
| Logout | `POST /api/v1/auth/logout` invalidates the session server-side, not just a client-side cookie clear |
| Refresh | No refresh-token flow is implemented — Sanctum SPA sessions refresh implicitly with activity via normal session lifetime; this is a deliberate non-feature (§1.4 ADR) |
| Token Expiration | Session lifetime per `config/session.php` (e.g., 2 hours idle); any issued personal access token carries an explicit `expires_at` |

---

## 11. Authorization

| Concern | Design |
|---|---|
| Spatie Permission | Roles and permissions per `DATABASE_SPECIFICATION.md` §4.3, seeded to match `PROJECT_SPECIFICATION.md` §5 exactly — six roles (`super-admin`, `ketua`, `sekretaris`, `humas`, `multimedia`, `editor`) |
| Policies | One per Domain Model, each method corresponding to one cell of the permission matrix in `PROJECT_SPECIFICATION.md` §5.1 — the Policy is the single enforcement point; Spatie roles/permissions are the data it checks |
| Gates | Reserved for checks not tied to one Model instance (e.g., `Gate::define('access-admin-dashboard')`); used sparingly — Policies are the default for anything resource-shaped |
| Role hierarchy | Flat, not tree-based. Super Admin has no special-cased bypass in code (no `if ($user->isSuperAdmin())` shortcuts inside Policies) — its authority comes entirely from having every permission assigned, keeping exactly one enforcement path for every role including the top one |
| Permission naming | `{resource}.{action}`, e.g. `articles.create`, `articles.publish`, `attendance.manage` — action-level, not CRUD-only, so a "can create but not publish" distinction (per the permission matrix) is representable |

---

## 12. Middleware

| Middleware | Purpose |
|---|---|
| Authentication (`auth:sanctum`) | Rejects unauthenticated requests to any admin route group |
| Authorization (`can:{ability},{model}`) | Delegates to the relevant Policy method; used on every mutating admin route |
| Rate Limit (`throttle:{name}`) | Named limiters: `auth` (5/min login attempts), `public-check-in` (per-QR-token limit), `contact-form` (per-IP limit) |
| Maintenance | Laravel's standard `PreventRequestsDuringMaintenance` |
| Request Logging | Custom middleware writing method/path/user/status/duration to the `api` log channel (§19) |
| Localization | Sets app locale from `Accept-Language`, defaulting to `id` — a thin placeholder seam, since only Bahasa Indonesia is supported at launch (`PROJECT_SPECIFICATION.md` §4); not a full i18n system until the multi-language item in §24 of that document is picked up |
| Security Headers | One global middleware setting `X-Content-Type-Options`, `X-Frame-Options`, `Content-Security-Policy`, `Referrer-Policy` |

---

## 13. Events

| Event | Carries | Responsibility |
|---|---|---|
| `ArticlePublished` | `Article` | Triggers public cache invalidation, activity log entry |
| `ArticleScheduledPublishDue` | `Article` | Raised by the scheduled-publish Job at the moment `published_at` arrives; flips status and reuses `ArticlePublished`'s listeners |
| `AttendanceRecorded` | `Attendance` | Updates incremental statistics counter (§14), activity log entry |
| `AttendanceSessionOpened` | `AttendanceSession` | Enables the QR display on the admin session screen; no further side effects at launch |
| `StudyScheduleOccurrenceCancelled` | `StudyScheduleOccurrence` | Public cache invalidation, activity log entry |
| `AnnouncementCreated` | `Announcement` | Triggers the urgent-priority notification listener (§16), public cache invalidation |
| `ActivityStatusChanged` | `Activity`, previous status | Public cache invalidation |
| `GalleryPhotoUploaded` | `GalleryPhoto` | Triggers the image-optimization Job (delegated to Spatie Media Library's own conversion pipeline) |
| `LibraryDocumentDownloaded` | `LibraryDocument` | Increments `download_count`; feeds the statistics dashboard |
| `OrganizationPeriodActivated` | `OrganizationPeriod` | Public cache invalidation for the org-chart page |
| `UserDeactivated` | `User` | Activity log entry; invalidates that user's active sessions |

Each event exists specifically to decouple a side effect that is not part of the triggering use case's own success criteria — e.g., an article is successfully published whether or not the cache invalidation listener runs; coupling that side effect into `ArticleService::publish()` directly would make the Service responsible for infrastructure concerns it shouldn't need to know about.

---

## 14. Listeners

| Listener Category | Listens To | Responsibility |
|---|---|---|
| Notification | `AnnouncementCreated` (when `priority = urgent`) | Queues the `NewAnnouncement` notification (§16) |
| Logging | Every event on sensitive modules (Users, Roles, Settings, Organization, Attendance) | Writes one `activity_logs` row (§19) |
| Statistics | `AttendanceRecorded` | Increments a Redis-backed running counter used by the dashboard's real-time attendance-rate card — the one deliberate exception to "statistics are computed at query time" (`PROJECT_SPECIFICATION.md` §17), because this specific counter is read on every dashboard load and a full-table aggregate on every load would be wasteful; all other statistics in §17 remain query-time |
| Cache | `ArticlePublished`, `AnnouncementCreated`, `ActivityStatusChanged`, `StudyScheduleOccurrenceCancelled`, `OrganizationPeriodActivated` | Flushes the relevant Redis cache tag (§17) |
| Search Index | — | **Explicitly not implemented.** No external search engine is in scope (§6.4); listed here only to record that this category is intentionally absent, not an oversight |

---

## 15. Jobs

| Job | Trigger | Notes |
|---|---|---|
| Image Optimization | Media upload | Spatie Media Library's own conversion job — reused, not reimplemented |
| Video Processing / Transcoding | — | **Not implemented.** `PROJECT_SPECIFICATION.md` never scopes video transcoding — only native playback of uploaded files or YouTube embeds. Stated explicitly as out of scope |
| Email Sending | Password reset, announcement digest | Queued Mailables, `mail` queue connection |
| Cache Refresh | After a public-cache invalidation event | Re-warms the homepage/schedule cache proactively, so the first post-invalidation visitor doesn't pay the cache-miss cost |
| Statistics Calculation | Nightly schedule (`app/Console`) | Computes the monthly attendance summary snapshot (§17 of `PROJECT_SPECIFICATION.md`) once, rather than recomputing it on every report view |
| Media Conversion | Media upload | Spatie's built-in conversion job (thumbnail/medium/WebP per collection) |
| Backup | Nightly schedule | Uses `spatie/laravel-backup` (an already-known Spatie package family) for DB dump + media snapshot to cloud storage, rather than a bespoke backup script |

---

## 16. Notifications

| Notification | Channel(s) | Notes |
|---|---|---|
| Password Reset | Email | Standard Laravel reset flow |
| Announcement Digest | Email | Queued, sent only for `priority = urgent` announcements — avoids notification fatigue on routine posts |
| Content Awaiting Approval | Database | In-admin notification bell for Ketua/Super Admin when Editor-authored content awaits publish approval; backed by Laravel's standard `notifications` table, polled on dashboard load — no websocket layer |
| Future Push Notification | — (deferred) | The Notification classes are written channel-agnostic (Laravel's `via()` method already supports this at zero extra cost), so a `push` channel can be added later without touching any call site. This is the one place a seam is added ahead of strict need, justified because the seam itself is free — not because push infrastructure is being pre-built |
| Study Reminder | — (deferred) | Listed in `PROJECT_SPECIFICATION.md` §3.7 Future Improvements; would be a scheduled Job (time-based, not Event-triggered) checking upcoming `study_schedule_occurrences` — designed for, not required at launch |

---

## 17. Cache Strategy

| Concern | Design |
|---|---|
| Backend | Redis, using Laravel cache tags (requires Redis — already the chosen driver) |
| Cache keys | `{resource}:{scope}:{identifier}`, e.g. `articles:list:page:1:category:kajian`, `organization:profile` |
| TTL | Long default (24h) as a safety net only — invalidation is event-driven and immediate on write, per the decision already made in `PROJECT_SPECIFICATION.md` §7.2; TTL is not the primary invalidation mechanism |
| Invalidation | Observers/Listeners flush the relevant cache tag on create/update/delete of the owning Model (§13, §14) |
| Repository cache | Applied only to Repositories backing public, read-heavy endpoints: Article, Announcement, StudySchedule, OrganizationProfile, Gallery listing, Activity listing, public Library listing. Admin-only reads are never cached — low traffic, always-fresh requirement for editors |
| Dashboard cache | Statistics queries (§17 of `PROJECT_SPECIFICATION.md`) cached with a short TTL (5 min) since they're staff-only reads with no write-invalidation precision requirement |

---

## 18. File Storage

| Concern | Design |
|---|---|
| Public files | `public` disk in development, S3-compatible public-read bucket in production — gallery photos, article/activity covers, public-visibility library documents |
| Private files | `internal`-visibility library documents (`DATABASE_SPECIFICATION.md` §4.16) live on a private disk, served through a signed, time-limited URL or a Policy-checked streaming Controller action — never a public path, so the `visibility` rule is enforced at the storage layer, not only at the listing-query layer |
| Media Library | Spatie Media Library across every collection cataloged in `DATABASE_SPECIFICATION.md` §5 |
| Image optimization / thumbnails | Spatie's `registerMediaConversions` per collection — thumbnail, medium, WebP, per `PROJECT_SPECIFICATION.md` §18 |

---

## 19. Logging Strategy

| Log Type | Mechanism | Purpose |
|---|---|---|
| Application logs | Laravel `daily` file channel | Framework/runtime errors |
| Activity logs | `activity_logs` table (`DATABASE_SPECIFICATION.md` §4.21) | Queryable, user-facing audit trail shown on the admin Logs screen (`PROJECT_SPECIFICATION.md` §14) |
| Audit logs | Same table as Activity logs | The two terms are used interchangeably across the companion documents; this is one mechanism, not two parallel systems |
| Security logs | Dedicated `security` file channel | Auth failures, rate-limit trips, Policy-denial events — operational/forensic, not user-facing, so file-based rather than DB-based |
| API logs | Request-logging middleware (§12) → `api` file channel | Sampled/short-retention in production, full in staging |

---

## 20. Error Handling

### 20.1 Exception Hierarchy

A small set of Domain-meaningful exceptions extends a common `Shared\Exceptions\DomainException` base: `AttendanceWindowClosedException`, `OrganizationPositionCycleException`, `SingletonProfileViolationException`, etc. Services throw these; nothing else in the codebase throws a raw generic `\Exception` for an expected business-rule failure.

### 20.2 Mapping to Responses

| Exception | HTTP Status | Notes |
|---|---|---|
| `ValidationException` (native) | 422 | `errors` populated per-field from the validator's message bag |
| `AuthorizationException` / Policy denial | 403 | Generic message — never reveals *why*, to avoid leaking a resource's existence to an unauthorized caller |
| `QueryException` (e.g., `RESTRICT` FK violation) | 409 | Translated to a domain-meaningful message; the raw SQL error is never surfaced to the client |
| Spatie Media Library exceptions (unsupported type, size exceeded) | 422 | Normalized into the same validation-error shape as any other 422 |
| Any `Shared\Exceptions\DomainException` subclass | 409 or 422 (per exception) | Message is the exception's own, human-readable business explanation |
| Unhandled/unexpected | 500 | Logged in full to the `daily` channel; client receives a generic message, never a stack trace, even in a misconfigured environment |

Every case above is translated centrally in the Exception Handler (`bootstrap/app.php`'s `withExceptions`, Laravel 12) into the one standard envelope — no Controller ever hand-rolls its own error response.

### 20.3 Standard Error Response

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "data": null,
  "meta": {},
  "errors": { "field": ["message"] }
}
```

---

## 21. Security Architecture

| Concern | Mechanism |
|---|---|
| XSS | `articles.body` sanitized server-side on save via a whitelist HTML purifier (not escape-on-output only, since it is rendered as HTML on the public site) |
| SQL Injection | Structurally prevented — Services have no query-builder dependency to reach for (§6.2); only Repositories touch Eloquent, exclusively via parameter binding |
| CSRF | Sanctum's stateful CSRF cookie mechanism for the SPA |
| Rate Limiting | Named throttle limiters per route group (§12) |
| File Upload Security | MIME-type sniffing (not extension-only), size caps per collection, `internal`-visibility files stored outside the public webroot |
| Password Policy | Form Request rule: min 10 characters, mixed case + number (`PROJECT_SPECIFICATION.md` §3.3) |
| Audit Trail | `activity_logs` (§19) |
| Encryption | Laravel encrypted cast on `settings.value` where `is_encrypted = true`; HTTPS enforced at the reverse proxy in production |

---

## 22. Performance Strategy

| Concern | Approach |
|---|---|
| Caching | §17 |
| Database Indexing | Fully specified in `DATABASE_SPECIFICATION.md` §7 — this document only references it, no independent indexing decisions are made here |
| Lazy Loading Prevention | `Model::preventLazyLoading()` enabled in every non-production environment (throws, catching N+1s in dev/CI before they ship); in production it logs instead of throwing, so a missed case degrades rather than 500s |
| Queue | All slow work (media conversion, email, scheduled publish) is offloaded per §15, keeping request-path latency low |
| Background Jobs | Same as above |
| Optimization | Route/config/view caching in deployment (`PROJECT_SPECIFICATION.md` §22), OPcache enabled on production PHP-FPM |

---

## 23. Scalability

| Concern | Approach |
|---|---|
| Horizontal Scaling | Stateless API containers behind a load balancer — possible specifically because session state lives in Redis, not local process memory |
| Redis | Shared cache/session/queue broker across all API instances |
| Queue Workers | Scaled independently of web instances (separate container/process), since queue volume doesn't track web request volume 1:1 |
| CDN | Public media (gallery, public library files) served through a CDN in front of the object storage disk |
| Media Storage | S3-compatible object storage from day one in production, so scaling web instances never fragments local-disk media across nodes |
| Future Microservices | Not adopted now — a single well-layered monolith is the right size for this project's scope. The Repository/Service contract boundaries already in place are exactly what would make a future extraction (e.g., Attendance as its own service) tractable, without paying distributed-systems cost today |

---

## 24. Development Standards

Restates and extends `PROJECT_SPECIFICATION.md` §9:

- **SOLID, PSR-12, strict types, dependency injection, Repository/Service/DTO patterns:** as defined in that document, unchanged.
- **DRY:** shared logic is factored into `Shared/Support` (or a base class) only after it is duplicated a third time — not preemptively extracted on the first occurrence.
- **KISS:** the layering in this document is the *ceiling* of complexity permitted per module, not a floor — a module that doesn't need an Action class doesn't get one (§5.1, §5.2).
- **Observer pattern:** used exclusively for persistence-triggered, model-lifecycle-scoped side effects (audit columns, cache invalidation) — never for business rules, which belong in a Service.
- **Event-driven:** used exclusively for side effects that are not part of a use case's own success criteria (§13) — never as a substitute for a direct Service method call when the caller genuinely needs the result synchronously.
- **Action classes:** only when the same small piece of logic is invoked from two or more Services/Jobs/Commands (§5.1's ADR).

---

## 25. Testing Architecture

| Test Type | Scope | Notes |
|---|---|---|
| Unit Test | Services, with Repository interfaces faked/mocked | This is exactly why Repositories are interface-first — a Service test never touches a database |
| Feature Test | Full HTTP request/response cycle per endpoint | Per `PROJECT_SPECIFICATION.md` §21 |
| Repository Test | Against a real Postgres test database in CI, **not** SQLite | SQLite cannot validate the partial unique indexes and `CHECK` constraints from `DATABASE_SPECIFICATION.md` — those are Postgres-specific and must be exercised against the real engine |
| Service Test | Business-rule branch coverage (e.g., attendance window boundary conditions) | |
| API Test | Response-envelope shape assertions | A shared `assertApiSuccess()`/`assertApiError()` test-helper trait avoids re-asserting the envelope shape in every test file |
| Policy Test | Every cell of the permission matrix in `PROJECT_SPECIFICATION.md` §5.1 has a corresponding allow/deny test | |
| Queue Test | `Queue::fake()` / `Event::fake()` asserting the correct Job/Event is dispatched | Does not rerun the Job's internal logic — that belongs to the Job's own unit test |

---

## 26. Module Template

Every new module requires exactly these files, no more, no fewer, following the naming convention in `PROJECT_SPECIFICATION.md` §9.1:

| File | Path Pattern |
|---|---|
| Migration | `database/migrations/{timestamp}_create_{table}_table.php` |
| Model | `app/Domain/Models/{X}.php` |
| Repository Interface | `app/Infrastructure/Repositories/Contracts/{X}RepositoryInterface.php` |
| Repository | `app/Infrastructure/Repositories/Eloquent/{X}Repository.php` |
| DTO | `app/Application/DTO/Create{X}DTO.php`, `Update{X}DTO.php` |
| Service | `app/Application/Services/{X}Service.php` |
| Form Request | `app/Http/Requests/Store{X}Request.php`, `Update{X}Request.php` |
| Policy | `app/Domain/Policies/{X}Policy.php` |
| Observer | `app/Infrastructure/Observers/{X}Observer.php` |
| Controller | `app/Http/Controllers/Api/V1/{X}Controller.php` |
| API Resource | `app/Http/Resources/{X}Resource.php` |
| Routes | entry in `routes/api.php`, grouped under `/api/v1` |
| Seeder | `database/seeders/{X}Seeder.php` (only where reference/lookup data needs seeding, e.g. categories, roles) |
| Factory | `database/factories/{X}Factory.php` |
| Feature Test | `tests/Feature/{X}Test.php` |
| Unit Test | `tests/Unit/{X}ServiceTest.php` |

A module that has no meaningful business rule beyond CRUD (e.g., `ArticleCategory`) still gets every file in this list — the discipline is what keeps the codebase navigable for a rotating contributor base; it is not scaled down per-module.

---

## 27. Development Workflow

Recommended build order, at the engineering-task level (maps to `PROJECT_SPECIFICATION.md` §23's phase list):

1. **Project Setup** — Laravel 12 install, Docker Compose dev environment (php-fpm, postgres, redis, node), base `Providers`, CI pipeline skeleton (lint + test on push).
2. **Authentication** — Sanctum configuration, `User` model + migration, login/logout endpoints.
3. **RBAC** — Spatie Permission install/config, seed the six roles and their permissions from `PROJECT_SPECIFICATION.md` §5.1.
4. **Core Database** — all migrations from `DATABASE_SPECIFICATION.md`, applied in dependency order: `users` → `members` → `organization_*` → `media` → content tables (`article_categories`/`articles`, `galleries`/`gallery_photos`, `activities`, `library_categories`/`library_documents`, `announcements`) → `study_schedules`/`study_schedule_occurrences` → `attendance_sessions`/`attendances` → `settings`/`activity_logs`.
5. **Repositories** — interfaces + Eloquent implementations + `RepositoryServiceProvider` bindings, one module at a time.
6. **Services** — business rules and DTOs, module by module, alongside their Repository.
7. **API** — Controllers, Form Requests, Resources, routes — completing each module end-to-end before moving to the next, so every module is independently demoable.
8. **Admin Modules** — no new architecture introduced here; this is the integration point where all modules exist together and admin-flow E2E testing happens against the real system.
9. **Statistics** — dashboard aggregation queries and the nightly snapshot Job (§15, §17).
10. **Media** — Spatie Media Library conversions wired per collection (§18).
11. **Optimization** — caching verified end-to-end, indexing verified against real query plans, load test against the highest-traffic public endpoints.
12. **Deployment** — per `PROJECT_SPECIFICATION.md` §22.

---

## 28. Future Architecture

| Item | How This Architecture Accommodates It |
|---|---|
| API Versioning | `/api/v1` is already the convention; a future `v2` is a parallel route group + Resource set, old version kept until clients migrate — no additional versioning infrastructure is built ahead of that need |
| Webhooks | Not scoped today. If a future integration needs outbound webhooks, it is a Job dispatched from the relevant Event's listener (§13/§14) — the seam already exists, nothing new to build |
| Search Engine | Swap the affected Repository's `search()` implementation (§6.4) for a Meilisearch/Typesense-backed one behind the same interface — no Service or Controller changes required |
| PWA | Frontend-only concern; no backend change beyond ensuring API responses stay cache-friendly, which is already true |
| Mobile App | Consumes the same `/api/v1` REST surface; no backend change required unless a mobile-specific need (e.g., device push tokens) arises, at which point it is an additive Notification channel (§16) |
| AI Integration | Would be a new bounded module (its own Service querying existing Repositories — Digital Library, Study Schedule — as its knowledge source), not a cross-cutting change to this architecture |

---

*End of BACKEND_ARCHITECTURE.md — read together with `PROJECT_SPECIFICATION.md` and `DATABASE_SPECIFICATION.md`, this is the complete basis for Laravel implementation. Any architectural deviation during development must be reflected back into this document.*

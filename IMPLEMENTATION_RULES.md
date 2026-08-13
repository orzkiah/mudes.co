# Mudes.co — Implementation Rules

| Field | Value |
|---|---|
| Document | IMPLEMENTATION_RULES.md |
| Companion Documents (final, unmodified) | PROJECT_SPECIFICATION.md, DATABASE_SPECIFICATION.md, BACKEND_ARCHITECTURE.md, API_SPECIFICATION.md |
| Version | 1.0.0 |
| Scope | Mandatory, module-agnostic rules for every future Laravel module |

This document states **rules**, not rationale — the architecture, schema, and API contract are already fully specified in the companion documents above. Where a rule needs a pointer to *why*, it cross-references the relevant section instead of re-explaining it. A module that violates any rule here is not complete, regardless of whether it "works."

---

## 1. Development Principles

- **Clean Architecture** — Presentation → Application → Domain ← Infrastructure. Never violate the dependency direction (full layer definitions: `BACKEND_ARCHITECTURE.md` §2).
- **SOLID** — applied at Service/Repository boundaries. Not ceremony inside trivial classes.
- **DRY** — extract shared logic only after a third duplication, not on sight.
- **KISS** — this document's layering is a ceiling, not a floor. A module with no non-trivial business rule does not get an Action class just because the pattern exists.
- **PSR-12** — enforced by Laravel Pint in CI; no manual style debates.
- **Convention over Configuration** — follow §2/§3 exactly. No per-module folder or naming variants.
- **API First** — no Blade views beyond transactional email templates. Every feature is reachable only through `/api/v1`.

---

## 2. Folder Convention

| Artifact | Path |
|---|---|
| Controllers | `app/Http/Controllers/Api/V1/` |
| Middleware | `app/Http/Middleware/` |
| Form Requests | `app/Http/Requests/` |
| API Resources | `app/Http/Resources/` |
| Services | `app/Application/Services/` |
| DTOs | `app/Application/DTO/` |
| Actions | `app/Application/Actions/` (only when logic is reused by ≥2 Services/Jobs/Commands) |
| Models | `app/Domain/Models/` |
| Policies | `app/Domain/Policies/` |
| Events | `app/Domain/Events/` |
| Enums | `app/Domain/Enums/` |
| Repository Interfaces | `app/Infrastructure/Repositories/Contracts/` |
| Repository Implementations | `app/Infrastructure/Repositories/Eloquent/` |
| Observers | `app/Infrastructure/Observers/` |
| Listeners | `app/Infrastructure/Listeners/` |
| Jobs | `app/Infrastructure/Jobs/` |
| Traits | `app/Shared/Traits/` |
| Support (envelope helper, filter/sort object) | `app/Shared/Support/` |
| Helpers | `app/Shared/Helpers/` |
| Exceptions (base classes) | `app/Shared/Exceptions/` |
| Feature Tests | `tests/Feature/` |
| Unit Tests | `tests/Unit/` |

Full rationale for this layout: `BACKEND_ARCHITECTURE.md` §2–3. This table is the binding lookup — do not re-derive a different structure per module.

---

## 3. Module Standard

Every business module ships with:

- [ ] Migration
- [ ] Model
- [ ] Factory
- [ ] Seeder
- [ ] Repository Interface
- [ ] Repository (Eloquent)
- [ ] Service
- [ ] DTO (Create + Update)
- [ ] Store Request
- [ ] Update Request
- [ ] Policy
- [ ] Observer
- [ ] API Resource
- [ ] API Resource Collection
- [ ] Controller
- [ ] Routes entry
- [ ] Feature Test
- [ ] Unit Test

Exact path patterns: `BACKEND_ARCHITECTURE.md` §26.

**Documented exceptions (the only ones permitted):**

| Item | May be omitted only when | Must still be stated in the PR |
|---|---|---|
| Seeder | The table has no fixed/reference data — pure runtime/transactional writes (e.g. Attendance Logs, Activity Logs) | "No seeder: runtime-only table" |
| API Resource Collection | The resource has no list endpoint (e.g. Organization Profile is a singleton) | "No collection: singleton resource" |
| Observer | The model has zero audit/cache/side-effect need — rare | Explicit justification; default assumption is every model gets one (`BACKEND_ARCHITECTURE.md` §2.2) |
| Policy | **Never omitted.** Even a module with no restrictions gets a Policy whose methods explicitly return `true` | N/A — an intentionally permissive Policy is not the same as a missing one |

A module missing any item without a stated exception is incomplete — see §20.

---

## 4. Controller Rules

A Controller action may only:

1. Receive a Form Request (via method injection).
2. Call `$this->authorize()`.
3. Call exactly one Service method (or, for a plain unfiltered read with no business rule, call a Repository directly — `BACKEND_ARCHITECTURE.md` §5.1).
4. Return an API Resource.

A Controller must **never**: contain a conditional business rule, call `Model::where()`/`::create()`/etc., call the `DB` facade, or hand-build an error response.

---

## 5. Service Rules

- Owns business logic; one public method = one use case.
- Depends on Repository **interfaces** and other Services only — never a concrete Eloquent Repository, never `Model::query()`.
- Wraps every mutating method in `DB::transaction()`.
- Dispatches Domain Events only after commit.
- Returns a Domain Model or an Output DTO — never a raw array, never an API Resource (shaping is the Controller's job).
- Throws Domain exceptions (§16), never a bare framework exception, for an expected business failure.

---

## 6. Repository Rules

- The **only** layer permitted to touch Eloquent's query builder.
- One interface per aggregate root, one Eloquent implementation, bound in `RepositoryServiceProvider`.
- Owns: CRUD, filtering (via the shared `QueryFilter` object, `BACKEND_ARCHITECTURE.md` §6.6 — never a bespoke filter class per module), search (`ILIKE` behind the interface, upgradeable later), sorting, pagination (`LengthAwarePaginator` for every list method — never a raw `Collection`), and cache-read wrapping where designated (§15).

---

## 7. DTO Rules

- Every create/update Service method receives a `readonly` DTO — never a `Request` object, never a raw array.
- Constructed via a static `fromRequest(FormRequest $request): self` factory — the only place besides the Form Request itself that calls `$request->validated()`.
- Must not depend on `Illuminate\Http\Request` internally and must not query the database.

---

## 8. Validation Rules

- All input validation lives in a Form Request's `rules()`.
- `authorize()` on the Form Request delegates to the Policy — never an inline role check.
- No `$request->validate()` inside a Controller.
- Business validation requiring a DB lookup (cross-entity, multi-column uniqueness) still belongs in the Form Request via a DB-aware Rule, so the client gets a 422 — never deferred to a Service exception that would surface a less specific error (`API_SPECIFICATION.md` §7).

---

## 9. API Resource Rules

- A Controller never returns a Model or a raw array — always a Resource or `Resource::collection()`.
- Every field is an explicit allow-list; relationships load only via `whenLoaded()`.
- Any paginated list endpoint uses a Resource Collection so `meta.pagination` is populated automatically (`API_SPECIFICATION.md` §2, §4.4) — never hand-built.

---

## 10. Authorization Rules

- Every business Model has a Policy, invoked via `$this->authorize()` in both the Controller and the Form Request's `authorize()`.
- Gates are reserved for checks not tied to one Model instance; used sparingly.
- No `$user->hasRole('super-admin')`-style shortcut anywhere outside a Policy — one enforcement path per resource, no bypass for any role including the top one (`BACKEND_ARCHITECTURE.md` §11).

---

## 11. Database Rules

- UUID (v7) primary keys, everywhere, no exceptions.
- Soft delete + `created_by`/`updated_by`/`deleted_by` on every project-owned table, unless it is one of the sanctioned exceptions in `DATABASE_SPECIFICATION.md` §2.7 (Spatie package tables, `activity_logs`, `notifications`) — no new undocumented exception may be introduced.
- Multi-step writes are transactional at the **Service** layer, never in the Repository or Controller.
- Models hold relationships, casts, and shared query scopes only. No business methods on a Model. A Model growing business logic ("fat model") is a defect, not a style choice.

---

## 12. Event Rules

Canonical event list and each event's listener responsibilities: `BACKEND_ARCHITECTURE.md` §13–14 (`ArticlePublished`, `AttendanceRecorded`, `AnnouncementCreated`, `GalleryPhotoUploaded`, `StudyScheduleOccurrenceCancelled`, `ActivityStatusChanged`, `LibraryDocumentDownloaded`, `OrganizationPeriodActivated`, `UserDeactivated`, and their notification equivalents) — not restated here.

**Rule for adding a new event:** dispatch one only for a side effect that is **not** part of the triggering use case's own success criteria (cache invalidation, logging, statistics, notification). If the caller needs the result synchronously, return it directly from the Service — do not model it as an Event.

---

## 13. Observer Rules

- Observers handle only: audit-column population, cache-tag invalidation, activity-log writes.
- Observers **never** contain a business decision (no conditional business rule inside `saving()`/`updating()`/etc.) — that belongs in the Service that triggered the write, not in a lifecycle hook.

---

## 14. Queue Rules

Anything not required to form the HTTP response is queued, never executed synchronously in a Controller or Service call:

- Image/media optimization and conversion
- Email sending
- Scheduled/deferred publishing (e.g. flipping a scheduled Article to published)
- Notification dispatch
- Statistics snapshot calculation
- Backup jobs

Full job catalog: `BACKEND_ARCHITECTURE.md` §15.

---

## 15. Cache Rules

- Only Repositories **read** from cache; only Observers/Listeners **invalidate** it.
- Key convention: `{resource}:{scope}:{identifier}` (`BACKEND_ARCHITECTURE.md` §17) — no ad hoc key formats.
- Invalidation is event-driven on write; the 24h default TTL is a fallback, never the primary mechanism.
- Cache only the modules already designated read-heavy/public in `BACKEND_ARCHITECTURE.md` §17. Adding caching to a new module's Repository requires adding it to that list first — caching is not a default, it's an opt-in per module.

---

## 16. Error Handling

- Services throw Domain exceptions extending `Shared\Exceptions\DomainException` — never a bare `\Exception` for an expected business failure.
- No empty `catch` blocks. A caught exception is re-thrown, logged, or converted to a Domain exception — never silently discarded.
- Every response funnels through the centralized Exception Handler into the standard envelope (`API_SPECIFICATION.md` §2). A Controller never constructs its own error response.

---

## 17. Logging

- Every mutating Service method on a sensitive module (Users, Roles, Settings, Organization, Attendance) writes one `activity_logs` row via its Observer/Listener — this **is** the audit log, not a separate system (`BACKEND_ARCHITECTURE.md` §19).
- Security-relevant events (auth failure, rate-limit trip, Policy denial) log to the `security` file channel, not the database.
- Never log a password, token, or full request body containing a sensitive field.

---

## 18. Testing Rules

| Test Type | Required For |
|---|---|
| Unit | Every Service method with a business-rule branch |
| Feature | Every endpoint — happy path plus at least one error path |
| Authorization | Every Policy method — both the allow and the deny case |
| Validation | Every Form Request rule that isn't a trivial `required` |
| Repository | Every custom query method, run against **Postgres** in CI, never SQLite (partial indexes and `CHECK` constraints in `DATABASE_SPECIFICATION.md` are Postgres-specific) |
| Service | Business-rule branch coverage (window boundaries, cycle prevention, etc.) |

**Target minimum line coverage: 80%** for `app/Application/` and `app/Domain/`. Coverage on `app/Http/`, `app/Shared/`, and generated boilerplate is not separately tracked.

---

## 19. Documentation Rules

Whenever a module changes:

- [ ] `API_SPECIFICATION.md` updated if any endpoint, request, or response shape changed
- [ ] `DATABASE_SPECIFICATION.md` updated if any table, column, or constraint changed
- [ ] `CHANGELOG.md` entry added
- [ ] `README.md` updated if local setup or environment variables changed

A schema or contract change merged without its companion document update is treated as incomplete — same severity as a missing test.

---

## 20. Pull Request Checklist

A module is not "done" until every box below is checked, or explicitly marked not applicable with a one-line reason:

- [ ] Coding standards: PSR-12 clean (`pint --test` passes)
- [ ] Every file in §3 present, or its exception documented in the PR description
- [ ] Feature, Unit, Authorization, and Validation tests passing
- [ ] Static analysis passes at the project's configured level
- [ ] Documentation updated per §19, or marked not applicable
- [ ] No duplicated logic — an existing Service/Repository method was searched for before adding a new one
- [ ] No unused imports, no dead code
- [ ] Security review: every new endpoint has a Policy check and a Form Request; no raw SQL; no secret logged
- [ ] Performance review: no N+1 query (lazy-loading prevention catches this in dev), pagination on every list endpoint, caching added only where §15 designates it

---

*End of IMPLEMENTATION_RULES.md — binding for every module built against `PROJECT_SPECIFICATION.md`, `DATABASE_SPECIFICATION.md`, `BACKEND_ARCHITECTURE.md`, and `API_SPECIFICATION.md`. A rule change here requires updating this document as its own revision.*

# Mudes.co
### Official Digital Platform of Pemuda Pemudi LDII Desa Condet

| Field | Value |
|---|---|
| Project Name | Mudes.co |
| Document | PROJECT_SPECIFICATION.md |
| Version | 1.0.0 |
| Status | Draft for Development |
| Author | AI Solution Architecture (drafted for Pemuda Pemudi LDII Desa Condet dev team) |
| Date | 2026-07-18 |
| Classification | Internal — Single Source of Truth |

### Revision History

| Version | Date | Author | Description |
|---|---|---|---|
| 1.0.0 | 2026-07-18 | Architecture Team | Initial complete specification |

---

## Table of Contents

1. Executive Summary
2. Business Requirements
3. Functional Requirements
4. Non-Functional Requirements
5. User Roles
6. Technology Stack
7. System Architecture
8. Folder Structure
9. Coding Standards
10. Database Design
11. Entity Relationship Description
12. REST API Specification
13. Public Website Specification
14. Admin Dashboard Specification
15. Attendance System
16. Digital Library
17. Statistics Dashboard
18. Media Manager
19. Integrations
20. Security
21. Testing Strategy
22. Deployment
23. Development Roadmap
24. Future Enhancements
25. Glossary
26. Appendix

---

## 1. Executive Summary

### 1.1 Background

Pemuda Pemudi LDII Desa Condet (referred to as "Mudes") currently coordinates community life — study schedules, activities, attendance, announcements, and documentation — through fragmented channels: WhatsApp groups, printed notices, and word of mouth. There is no authoritative, searchable, permanent record of organizational structure, activity history, or study materials, and no unified way for the public (parents, prospective members, wider LDII community) to learn about the organization.

### 1.2 Problem Statement

- Information is scattered across chat groups and lost as conversations scroll.
- Attendance for study sessions (*kajian*) and activities is tracked manually on paper or spreadsheets, with no aggregated reporting.
- There is no public-facing profile of the organization, its structure, or its activities.
- Study materials (recordings, documents, guidance) are not centrally archived or searchable.
- Announcements reach only members already in the relevant chat group, excluding parents and the wider community.
- No data exists to measure engagement, growth, or participation trends over time.

### 1.3 Objectives

- Provide one authoritative public website presenting the organization, its structure, schedule, and activities.
- Provide an internal admin CMS for managing all content and organizational data.
- Digitize attendance tracking for study sessions and activities.
- Centralize a searchable digital library of study materials.
- Give organizational leadership data-driven visibility into participation and engagement.

### 1.4 Expected Outcome

A production-grade, maintainable platform consisting of a public website, an admin dashboard, and a REST API backend, built on Clean Architecture principles, that becomes the permanent operational and informational backbone of the organization.

### 1.5 Project Vision

To be the definitive digital home of Pemuda Pemudi LDII Desa Condet — trusted, always current, and accessible to members and the public alike.

### 1.6 Project Mission

- Digitize organizational operations that are currently manual or informal.
- Preserve institutional knowledge (structure, history, study materials) in a durable system.
- Increase transparency and engagement between leadership and members.
- Present a professional public identity for the organization.

---

## 2. Business Requirements

### 2.1 Business Goals

- Replace ad-hoc communication channels with an authoritative information system.
- Reduce administrative overhead of attendance and reporting.
- Improve public perception and discoverability of the organization.
- Preserve and make searchable all study materials and activity documentation.

### 2.2 Target Users

| User Group | Description |
|---|---|
| Members (Pemuda Pemudi) | Attend study sessions and activities, view schedules, access library |
| Parents / Guardians | View organizational profile, schedule, and activity transparency |
| Organization Leadership | Manage content, view statistics, oversee attendance |
| Content Officers (Humas, Multimedia, Editor) | Publish articles, galleries, and announcements |
| General Public / Wider LDII Community | Learn about the organization, browse public content |

### 2.3 Benefits

- Single source of truth for schedules, structure, and announcements.
- Reduced manual attendance reconciliation effort.
- Searchable historical archive of activities and study materials.
- Improved organizational credibility through a professional public presence.

### 2.4 Success Metrics

| Metric | Target |
|---|---|
| Monthly active public visitors | Growth quarter over quarter |
| Attendance sessions digitized vs. paper-based | 100% within 2 months of launch |
| Admin content published per month | Sustained baseline post-launch |
| Digital library items accessed per month | Positive trend |
| Average API response time (p95) | < 300 ms |

### 2.5 Project Scope

- Public informational website.
- Admin CMS dashboard with role-based access.
- REST API backend serving both.
- Digital attendance (QR-based) for study sessions and activities.
- Digital library for study materials.
- Statistics and reporting dashboard.

### 2.6 Out of Scope

- E-commerce or payment processing of any kind.
- Social media / user-to-user posting or messaging features.
- Public discussion forums or comment threads with open registration.
- Event ticketing or public self-service registration workflows.
- Mobile native applications (see Future Enhancements).

---

## 3. Functional Requirements

Each module below is described with Purpose, Features, Workflow, Business Rules, Validation Rules, Permissions, Dependencies, and Future Improvements.

### 3.1 Public Website

**Purpose:** Present the organization and its content to the public without requiring authentication.

**Features:** Home landing, organization profile, structure chart, study schedule, activities feed, articles/blog, gallery, digital library (public-readable items), announcements, contact/footer, sitewide search, SEO metadata.

**Workflow:** Visitor requests a page → Next.js server component fetches from public REST endpoints (cached) → page renders with SSR/ISR for SEO.

**Business Rules:** Only content with `status = published` and `deleted_at IS NULL` is publicly visible. Draft/archived content is never exposed via public endpoints.

**Validation Rules:** N/A (read-only for anonymous users), except contact form submissions which require server-side validation (name, email, message, honeypot/rate-limit).

**Permissions:** Guest (read-only, public content only).

**Dependencies:** Public REST API, CDN/edge caching, SEO metadata service.

**Future Improvements:** Multi-language toggle, PWA offline caching of key pages.

### 3.2 Admin Dashboard

**Purpose:** Central management console for all organizational content and data.

**Features:** Role-scoped navigation, module CRUD screens, media manager, statistics, settings, activity/audit log viewer.

**Workflow:** Authenticated staff logs in via Sanctum → dashboard loads modules permitted by their role → CRUD operations call authenticated REST endpoints.

**Business Rules:** Every mutating action must be attributable to a user (`created_by`/`updated_by`) and logged.

**Validation Rules:** All create/update requests validated via Form Request classes server-side; client-side validation is UX-only, never trusted.

**Permissions:** Staff roles only (Super Admin, Ketua, Sekretaris, Humas, Multimedia, Editor) — see Section 5.

**Dependencies:** Authenticated REST API, Spatie Permission, Spatie Media Library.

**Future Improvements:** Customizable per-role dashboard widgets.

### 3.3 Authentication

**Purpose:** Verify identity of staff/admin users. Public visitors remain anonymous — there is no public self-registration.

**Features:** Login, logout, token/session issuance (Sanctum), password reset via email, "remember me", session invalidation.

**Workflow:** Admin submits credentials → backend validates → Sanctum issues SPA session cookie (stateful, same-site) or personal access token (for non-browser clients) → subsequent requests authenticated via cookie/token.

**Business Rules:** Accounts are provisioned by Super Admin only; there is no public signup form. Passwords are hashed (bcrypt/argon2, Laravel default).

**Validation Rules:** Email format, password minimum complexity (min 10 chars, mixed case + number), rate-limited login attempts (5/minute per IP+email).

**Permissions:** N/A (this module gates access to everything else).

**Dependencies:** Laravel Sanctum, Mail (password reset), Redis (rate limiting).

**Future Improvements:** Two-factor authentication (TOTP), SSO with Google Workspace.

### 3.4 Authorization

**Purpose:** Enforce role- and permission-based access to every resource and action.

**Features:** Role assignment, permission-to-role mapping, per-resource Policy checks.

**Workflow:** Request hits controller → Policy (via `authorize()`) checks the acting user's role/permissions against the target resource → allowed or 403.

**Business Rules:** Deny by default; every controller action must have an explicit Policy method. No implicit "authenticated = allowed."

**Validation Rules:** N/A.

**Permissions:** Managed exclusively by Super Admin.

**Dependencies:** Spatie Laravel-Permission, Laravel Policies.

**Future Improvements:** Fine-grained field-level permissions if a role ever needs partial-record edit rights.

### 3.5 Organization Profile

**Purpose:** Present who the organization is — history, vision/mission, contact details, location.

**Features:** Editable profile record (single-row "settings-like" entity), logo/banner via Media Manager, embedded map location.

**Workflow:** Ketua/Sekretaris edits profile in admin → change reflected on public "About" page immediately (cache invalidated on save).

**Business Rules:** Exactly one active organization profile record exists.

**Validation Rules:** Required: name, short description, address. Optional: social links, map coordinates.

**Permissions:** Read: all staff + public. Write: Super Admin, Ketua, Sekretaris.

**Dependencies:** Media Manager, Google Maps integration.

**Future Improvements:** Versioned profile history.

### 3.6 Organization Structure

**Purpose:** Represent the hierarchical leadership/member structure (*struktur organisasi*).

**Features:** Positions (Ketua, Sekretaris, Bendahara, Koordinator Bidang, etc.), member assignment to positions, hierarchy/reporting lines, period (e.g., "Periode 2025–2027").

**Workflow:** Admin creates a structure "period," assigns members to positions with optional parent position for hierarchy rendering (org chart).

**Business Rules:** Only one structure period may be marked `is_active` at a time; public site always shows the active period, with past periods viewable as history.

**Validation Rules:** Position name required; member reference required; self-referential parent cannot create a cycle.

**Permissions:** Read: all + public. Write: Super Admin, Ketua, Sekretaris.

**Dependencies:** Member/User directory.

**Future Improvements:** Photo-based org chart export (PDF/PNG).

### 3.7 Study Schedule

**Purpose:** Publish the recurring Islamic study (*kajian*) schedule.

**Features:** Recurring schedule entries (day of week, time, topic/theme, ustadz/instructor name, location), one-off schedule overrides (holidays, special sessions), calendar view, list view.

**Workflow:** Admin defines recurring schedule template → system (or admin) generates dated occurrences → each occurrence can be individually rescheduled/cancelled without altering the template.

**Business Rules:** A cancelled occurrence remains visible (marked cancelled) rather than deleted, to preserve attendance/history continuity.

**Validation Rules:** Day/time required; end time after start time; ustadz name required.

**Permissions:** Read: all + public. Write: Super Admin, Ketua, Sekretaris.

**Dependencies:** Attendance module (each occurrence is an attendance session), Google Calendar integration.

**Future Improvements:** Automatic reminder notifications before each session.

### 3.8 Activities

**Purpose:** Document one-time or periodic organizational activities/events (*kegiatan*) — informational only, no registration.

**Features:** Title, description, date/time, location, cover image, linked gallery, linked attendance (optional), status (upcoming/ongoing/completed/cancelled).

**Workflow:** Admin creates an activity ahead of time (upcoming) → updates status as it progresses → attaches gallery/report after completion.

**Business Rules:** No public registration or RSVP capability — this is documentation, not event management.

**Validation Rules:** Title, date required; end date/time after start.

**Permissions:** Read: all + public. Write: Super Admin, Ketua, Sekretaris, Humas.

**Dependencies:** Gallery, Attendance, Media Manager.

**Future Improvements:** Activity calendar export (iCal).

### 3.9 Articles

**Purpose:** Publish blog-style informational/educational content.

**Features:** Rich-text article body, category, tags, cover image, author attribution, draft/published/archived status, scheduled publishing.

**Workflow:** Editor drafts article → optional review by Humas/Ketua → publish (immediately or scheduled) → appears on public site and sitemap.

**Business Rules:** Scheduled articles auto-publish via queued job at the scheduled time.

**Validation Rules:** Title (required, max 255), slug (unique, auto-generated, editable), body (required), category (required).

**Permissions:** Read: all + public (published only). Write/Create: Editor, Humas, Super Admin. Publish approval: Ketua, Super Admin (configurable).

**Dependencies:** Media Manager, SEO metadata service, Queue (scheduled publish).

**Future Improvements:** Full-text search via Postgres `tsvector`, related-articles recommendation.

### 3.10 Gallery

**Purpose:** Photo documentation of activities and organizational life.

**Features:** Albums (linked optionally to an Activity), multi-photo upload, captions, cover photo selection.

**Workflow:** Multimedia officer creates an album → bulk-uploads photos via Media Manager → optionally links album to an Activity record.

**Business Rules:** Photos are always processed into multiple sizes (thumbnail, medium, full) on upload.

**Validation Rules:** Album title required; accepted image types: jpg, png, webp; max file size enforced (e.g., 10 MB per photo).

**Permissions:** Read: all + public. Write: Multimedia, Super Admin.

**Dependencies:** Media Manager, Spatie Media Library (image conversions).

**Future Improvements:** Face/scene tagging, downloadable album ZIP.

### 3.11 Digital Library

**Purpose:** Centralized, searchable archive of study materials and organizational documents.

**Features:** Categorized documents (PDF, audio, video, or external link), tags, description, download/view counter, preview.

**Workflow:** Admin uploads a document with metadata → categorized and tagged → publicly listed (if marked public) or internal-only.

**Business Rules:** Each item has a visibility flag: `public` or `internal` (members/staff only — enforced at the API layer even though there is no public member login; internal items are simply omitted from public endpoints and only visible in admin).

**Validation Rules:** Title, category, file (or external URL) required; file type restricted to pdf/mp3/mp4/common doc formats.

**Permissions:** Read (public items): public. Read (internal items) + Write: Super Admin, Sekretaris, Multimedia, Editor.

**Dependencies:** Media Manager, Cloud Storage.

**Future Improvements:** Full-text search inside PDF content.

### 3.12 Announcements

**Purpose:** Broadcast time-sensitive information (*pengumuman*) to members and public.

**Features:** Title, body, priority (normal/urgent), audience (public/internal), start/expiry date, pinning.

**Workflow:** Admin publishes an announcement → shown on homepage/announcement board until expiry → auto-hidden after expiry (soft, not deleted).

**Business Rules:** Expired announcements are automatically excluded from active listings by a scheduled query filter (`expires_at >= now()` or null), not a destructive job.

**Validation Rules:** Title, body required; expiry date (if set) must be after start date.

**Permissions:** Read: audience-scoped. Write: Super Admin, Ketua, Sekretaris, Humas.

**Dependencies:** None beyond core CMS.

**Future Improvements:** Push notification delivery on urgent announcements.

### 3.13 Attendance

See Section 15 for full detail. Summary: digital attendance for study sessions and activities via QR code check-in or manual entry, with per-member and per-session reporting.

### 3.14 Dashboard Analytics

See Section 17 for full detail. Summary: charts and reports aggregating attendance, content activity, and (optionally) visitor analytics.

### 3.15 Website Settings

**Purpose:** Configure sitewide, non-content settings.

**Features:** Site name/tagline, SEO defaults, social links, integration API keys (Google Maps, Analytics), maintenance mode toggle, email sender settings.

**Workflow:** Super Admin edits a single settings record (key-value store) via a dedicated settings screen.

**Business Rules:** Sensitive values (API keys, SMTP credentials) are encrypted at rest.

**Validation Rules:** Per-field validation matching expected type (URL, email, boolean).

**Permissions:** Super Admin only.

**Dependencies:** Laravel encrypted casts.

**Future Improvements:** Settings change history/versioning.

### 3.16 Media Manager

See Section 18 for full detail. Summary: centralized upload, organization, and optimization of all media assets used across modules.

---

## 4. Non-Functional Requirements

| Category | Requirement |
|---|---|
| Performance | Public pages: LCP < 2.5s on 4G. API: p95 response time < 300ms, p99 < 800ms. |
| Availability | Target 99.5% uptime for production. |
| Reliability | Automated daily database + media backups, retained 30 days minimum. |
| Maintainability | Clean Architecture layering, PSR-12, typed code (`declare(strict_types=1)`, TypeScript strict mode), enforced per Section 9. |
| Security | See Section 20 in full. |
| Accessibility | WCAG 2.1 AA on public-facing pages (semantic HTML, alt text, keyboard navigation, color contrast). |
| SEO | Server-rendered public pages, per-page meta tags, Open Graph, sitemap.xml, robots.txt, JSON-LD structured data for articles/events. |
| Scalability | Stateless API instances behind a load balancer; horizontal scaling of queue workers; Redis for cache/session/queue. |
| Responsiveness | Mobile-first layout, breakpoints for mobile/tablet/desktop, tested down to 360px width. |
| Localization | Bahasa Indonesia as primary language; architecture allows future i18n (English) without schema changes (translatable fields deferred until needed). |

---

## 5. User Roles

| Role | Description |
|---|---|
| Super Admin | Full system access; manages users, roles, settings, and all content. |
| Ketua (Chairman) | Oversees organization; approves/publishes key content; manages structure and schedule. |
| Sekretaris (Secretary) | Manages schedule, attendance, organization data, and internal documents. |
| Humas (Public Relations) | Manages announcements, articles (publish-level), public-facing communication. |
| Multimedia | Manages gallery, media library, video/audio content. |
| Editor | Drafts articles and digital library content; limited publish rights. |
| Guest | Anonymous public visitor; read-only access to published public content. |

### 5.1 Permission Matrix

| Module | Super Admin | Ketua | Sekretaris | Humas | Multimedia | Editor | Guest |
|---|---|---|---|---|---|---|---|
| Users & Roles | CRUD | – | – | – | – | – | – |
| Organization Profile | CRUD | CRUD | CRUD | Read | Read | Read | Read (public) |
| Organization Structure | CRUD | CRUD | CRUD | Read | Read | Read | Read (public) |
| Study Schedule | CRUD | CRUD | CRUD | Read | Read | Read | Read (public) |
| Activities | CRUD | CRUD | CRUD | CRUD | Read/Update media | Read | Read (public) |
| Articles | CRUD + Publish | CRUD + Publish | Read | CRUD + Publish | Read | Create/Update (draft) | Read (published) |
| Gallery | CRUD | Read | Read | Read | CRUD | Read | Read (public) |
| Digital Library | CRUD | Read | CRUD | Read | CRUD (media) | Create/Update | Read (public items) |
| Announcements | CRUD | CRUD | CRUD | CRUD | Read | Read | Read (audience-scoped) |
| Attendance | CRUD | Read | CRUD | Read | – | – | – |
| Dashboard Analytics | Full | Full | Scoped | Scoped | Scoped | Scoped | – |
| Website Settings | CRUD | Read | – | – | – | – | – |
| Media Manager | Full | Read | Read/Upload | Read/Upload | Full | Read/Upload | – |
| Activity/Audit Logs | Read | Read (own scope) | – | – | – | – | – |

*(CRUD = Create, Read, Update, Delete)*

---

## 6. Technology Stack

### 6.1 Backend

| Component | Choice |
|---|---|
| Framework | Laravel 12 |
| Language | PHP 8.4 |
| Database | PostgreSQL 16+ |
| Cache / Session / Queue Broker | Redis 7+ |
| Authentication | Laravel Sanctum |
| Authorization | Spatie Laravel-Permission |
| Media Handling | Spatie Media Library |
| Queue | Laravel Queue (Redis driver) + Horizon (monitoring) |
| API Style | REST (JSON) |

### 6.2 Frontend

| Component | Choice |
|---|---|
| Framework | Next.js 15 (App Router) |
| Library | React 19 |
| Language | TypeScript (strict) |
| Styling | Tailwind CSS |
| Component Library | Shadcn UI |
| Animation | Framer Motion |

### 6.3 Supporting Infrastructure

| Concern | Choice |
|---|---|
| Object Storage | S3-compatible (e.g., Cloudflare R2 / AWS S3) for production media |
| Monitoring | Laravel Pulse (app metrics) + Sentry (error tracking, both backend & frontend) |
| Deployment | Docker containers, reverse-proxied via Nginx |
| CI/CD | GitHub Actions (lint, test, build, deploy) |
| Testing | Pest/PHPUnit (backend), Playwright (frontend E2E), Vitest/Jest (frontend unit) |

---

## 7. System Architecture

Mudes.co backend follows **Clean Architecture**, adapted to Laravel conventions, with a strict one-directional dependency rule: outer layers depend on inner layers, never the reverse.

| Layer | Responsibility | Laravel Artifact |
|---|---|---|
| Presentation | HTTP I/O, request/response shaping | Controllers, Form Requests, API Resources, Route middleware |
| Application | Orchestrates business use cases | Services, DTOs |
| Domain | Core business rules and entities | Eloquent Models (as domain entities), Policies, domain Events |
| Infrastructure | Data persistence and external systems | Repositories (Eloquent-backed), Observers, external API clients, Jobs |

### 7.1 Request Flow

1. Route → Middleware (auth, throttle) → Controller.
2. Controller resolves a Form Request (validation happens before the controller method body executes).
3. Controller calls a Service method, passing a DTO built from validated input.
4. Service applies business rules, calls one or more Repository methods.
5. Repository performs persistence via Eloquent, returns domain models.
6. Service returns result to Controller.
7. Controller wraps the result in an API Resource and returns the standard envelope response.

### 7.2 Cross-Cutting Concerns

- **Policies** gate every Controller action (`$this->authorize()`), keyed to the acting user's role/permissions.
- **Observers** attached to Models handle: setting `created_by`/`updated_by`/`deleted_by` automatically, cache invalidation on save/delete, and audit log entries.
- **Events & Listeners** decouple side effects from core flow (e.g., `ArticlePublished` → invalidate public cache tag; `AttendanceRecorded` → update session statistics asynchronously).
- **Jobs** (queued) handle anything slow or non-critical-path: image optimization, scheduled article publishing, email sending, report generation.
- **Caching**: Redis, tag-based, applied to public read-heavy endpoints (organization profile, schedule, published articles/gallery listings). Invalidated via Observer/Event on writes — never time-only expiry for content that must reflect edits immediately.

### 7.3 Repository Pattern Contract

Every Repository is defined behind an Interface (`XRepositoryInterface`) bound in a Service Provider, so Services depend on the abstraction, not the Eloquent implementation — enabling test doubles and future swap of persistence technology without touching Services.

---

## 8. Folder Structure

### 8.1 Backend (Laravel)

```
app/
  Console/
  Events/
  Exceptions/
    Handler.php
  Http/
    Controllers/Api/          (thin controllers, one per resource)
    Middleware/
    Requests/                 (Form Requests: StoreXRequest, UpdateXRequest)
    Resources/                (API Resources: XResource, XCollection)
  Jobs/
  Listeners/
  Models/
  Observers/
  Policies/
  Providers/
  Repositories/
    Contracts/                (XRepositoryInterface)
    Eloquent/                 (XRepository implements XRepositoryInterface)
  Services/
    DTOs/                     (CreateXDTO, UpdateXDTO — readonly value objects)
    XService.php
database/
  factories/
  migrations/
  seeders/
routes/
  api.php
tests/
  Feature/
  Unit/
```

**Folder responsibilities:** `Requests` owns all validation; `Resources` owns all outbound shaping (never expose raw Models); `Repositories/Contracts` decouples Services from Eloquent; `Services` owns business logic exclusively — Controllers must not contain conditional business logic; `Observers` owns audit-column population and cache invalidation so it is never duplicated per-Controller.

### 8.2 Frontend (Next.js App Router)

```
app/
  (public)/
    page.tsx                  (Home)
    tentang/                  (About)
    jadwal/                   (Study Schedule)
    kegiatan/                 (Activities)
    artikel/
    galeri/
    perpustakaan/              (Digital Library)
    pengumuman/                (Announcements)
    kontak/
  (admin)/
    dashboard/
    articles/
    gallery/
    schedule/
    activities/
    attendance/
    library/
    announcements/
    settings/
    users/
components/
  ui/                          (Shadcn primitives)
  shared/                      (Navbar, Footer, PageHeader, etc.)
  admin/                       (dashboard-only components)
lib/
  api/                         (typed fetch clients per resource)
  hooks/
  utils/
types/
  api.ts                       (types generated/mirrored from API Resources)
```

**Folder responsibilities:** `(public)` and `(admin)` route groups enforce the frontend/backend and public/private separation at the routing level; `lib/api` is the only layer permitted to call the backend, so no component fetches directly.

---

## 9. Coding Standards

### 9.1 Naming Convention

| Artifact | Convention | Example |
|---|---|---|
| Controller | Singular, suffixed `Controller` | `ArticleController` |
| Repository Interface | Suffixed `RepositoryInterface` | `ArticleRepositoryInterface` |
| Repository Implementation | Suffixed `Repository` | `ArticleRepository` |
| Service | Suffixed `Service` | `ArticleService` |
| DTO | Verb-prefixed, suffixed `DTO` | `CreateArticleDTO`, `UpdateArticleDTO` |
| Form Request | Verb-prefixed, suffixed `Request` | `StoreArticleRequest`, `UpdateArticleRequest` |
| API Resource | Suffixed `Resource` | `ArticleResource` |
| Policy | Suffixed `Policy` | `ArticlePolicy` |
| Observer | Suffixed `Observer` | `ArticleObserver` |
| Model | Singular, PascalCase | `Article` |
| Migration table | Plural, snake_case | `articles` |

### 9.2 Principles Enforced

- **SOLID** across Services and Repositories: each Service has one reason to change; new content types extend via new classes, not conditionals inside existing ones; Repositories are substitutable via their Interface; consumers depend only on the methods they use; high-level Services depend on Repository abstractions.
- **PSR-12** code style, enforced by Laravel Pint in CI.
- **Strict types**: every PHP file declares `strict_types=1`; every TypeScript file compiles under `strict: true`.
- **Dependency Injection**: constructor injection only, resolved via the container — no `new` of a Repository/Service inside a Controller.
- **Repository Pattern**: Controllers and Services never call Eloquent directly; all persistence goes through a Repository.
- **Service Pattern**: business rules live only in Services.
- **DTO Pattern**: data crossing the Controller→Service boundary is a typed, immutable (readonly) DTO — never a raw array or Request object passed into a Service.
- **API Resource Pattern**: every API response is shaped by a Resource class; Models are never returned directly.
- **Validation**: exclusively via Form Request classes; Controllers never call `$request->validate()` inline.
- **Error Handling**: exceptions are handled centrally in the Exception Handler, converted to the standard JSON error envelope.
- **Logging**: structured logging via named channels (`api`, `security`, `queue`); no `dd()`/`var_dump` in committed code.
- **Testing**: every module ships a Feature test (API behavior) and Unit test (Service/DTO logic).
- **Documentation**: every new module updates Database, API, and Architecture documentation plus the README, per Section 26.

---

## 10. Database Design

All tables use a **UUID primary key** (`id`), **soft deletes** (`deleted_at`), and **audit columns** (`created_by`, `updated_by`, `deleted_by` — nullable UUID foreign keys to `users`), plus `created_at`/`updated_at` timestamps. This baseline is omitted from each table's column list below for brevity and implied throughout.

| Table | Purpose | Key Columns (beyond baseline) | Relationships | Indexes / Constraints |
|---|---|---|---|---|
| `users` | Staff/admin accounts | name, email (unique), password, is_active | Has many roles (via Spatie pivot) | Unique index on email |
| `organization_profiles` | Single organization identity record | name, tagline, description, address, lat, lng, logo_media_id | Belongs to Media (logo/banner) | Single-row enforced at Service layer |
| `organization_periods` | Structure period ("Periode 2025–2027") | label, start_date, end_date, is_active | Has many organization_positions | Unique partial index: only one `is_active = true` |
| `organization_positions` | Position within a period | title, member_name, parent_position_id (self-ref), order | Belongs to organization_periods; self-referential parent | FK cycle prevented at Service layer |
| `study_schedules` | Recurring schedule template | day_of_week, start_time, end_time, topic, ustadz_name, location | Has many study_schedule_occurrences | — |
| `study_schedule_occurrences` | Concrete dated instance of a schedule | schedule_id, date, status (scheduled/cancelled/completed), override_note | Belongs to study_schedules; has one attendance_session | Unique (schedule_id, date) |
| `activities` | One-time/periodic events | title, slug (unique), description, start_at, end_at, location, status, cover_media_id | Has many galleries (optional link), has one attendance_session (optional) | Unique index on slug |
| `articles` | Blog/informational content | title, slug (unique), excerpt, body, category_id, cover_media_id, status, published_at | Belongs to article_categories; belongs to author (users) | Unique index on slug; index on status+published_at |
| `article_categories` | Article taxonomy | name, slug (unique) | Has many articles | Unique index on slug |
| `galleries` | Photo albums | title, description, activity_id (nullable), cover_photo_media_id | Belongs to activities (optional); has many gallery_photos | — |
| `gallery_photos` | Individual photos in an album | gallery_id, media_id, caption, order | Belongs to galleries; belongs to Media | Index on gallery_id |
| `library_documents` | Digital library items | title, description, category_id, file_media_id (nullable), external_url (nullable), visibility (public/internal), download_count | Belongs to library_categories; belongs to Media | Index on visibility, category_id |
| `library_categories` | Library taxonomy | name, slug (unique) | Has many library_documents | Unique index on slug |
| `announcements` | Time-boxed notices | title, body, priority (normal/urgent), audience (public/internal), pinned, starts_at, expires_at | — | Index on audience, expires_at |
| `attendance_sessions` | A concrete attendance-taking event | source_type (schedule/activity), source_id, qr_token (unique), opens_at, closes_at | Polymorphic to study_schedule_occurrences or activities | Unique index on qr_token |
| `attendances` | A single member's check-in record | attendance_session_id, member_name, member_reference (nullable link to a member/user), method (qr/manual), checked_in_at | Belongs to attendance_sessions | Index on attendance_session_id; unique (attendance_session_id, member_reference) where member_reference is not null |
| `settings` | Sitewide key-value configuration | key (unique), value (encrypted where sensitive), type | — | Unique index on key |
| `activity_logs` | Audit trail of mutating actions | user_id, action, subject_type, subject_id, changes (json) | Belongs to users | Index on subject_type+subject_id |

**Media** (files: images, PDFs, audio, video) is handled by the Spatie Media Library package's own `media` table (polymorphic `model_type`/`model_id`), not a custom table — this is a deliberate reuse of an already-adopted dependency rather than a bespoke media table (see Section 6 and Appendix design principles).

**Roles & Permissions** (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`) are provided by Spatie Laravel-Permission's standard schema and are not redefined here.

---

## 11. Entity Relationship Description

**One-to-One**
- `users` ↔ (none custom; auth-only entity)
- `organization_profiles` is a singleton conceptually one-to-none/one, enforced by application logic rather than a literal FK relationship.
- `study_schedule_occurrences` ↔ `attendance_sessions` (an occurrence has at most one attendance session).
- `activities` ↔ `attendance_sessions` (an activity has at most one attendance session, optional).

**One-to-Many**
- `organization_periods` → `organization_positions`
- `study_schedules` → `study_schedule_occurrences`
- `article_categories` → `articles`
- `library_categories` → `library_documents`
- `galleries` → `gallery_photos`
- `attendance_sessions` → `attendances`
- `users` → `activity_logs` (as actor)
- `organization_positions` → `organization_positions` (self-referential, parent/child for org chart hierarchy)

**Many-to-Many**
- `users` ↔ `roles` (via Spatie `model_has_roles`)
- `roles` ↔ `permissions` (via Spatie `role_has_permissions`)
- `activities` ↔ `galleries` is modeled as one-to-many from `galleries.activity_id`, i.e., an activity may have multiple albums, but each album belongs to at most one activity (not true many-to-many).

---

## 12. REST API Specification

All endpoints are versioned under `/api/v1`. Public (unauthenticated) and admin (Sanctum-authenticated) endpoints are separated by route group and middleware, not by convention alone.

### 12.1 Authentication

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/v1/auth/login` | Authenticate, issue session/token |
| POST | `/api/v1/auth/logout` | Invalidate current session/token |
| POST | `/api/v1/auth/forgot-password` | Send password reset email |
| POST | `/api/v1/auth/reset-password` | Reset password with token |
| GET | `/api/v1/auth/me` | Current authenticated user profile |

### 12.2 Users, Roles, Permissions

| Method | Endpoint |
|---|---|
| GET/POST | `/api/v1/admin/users` |
| GET/PUT/DELETE | `/api/v1/admin/users/{id}` |
| GET/POST | `/api/v1/admin/roles` |
| GET/PUT/DELETE | `/api/v1/admin/roles/{id}` |

### 12.3 Organization

| Method | Endpoint |
|---|---|
| GET | `/api/v1/public/organization/profile` |
| GET/PUT | `/api/v1/admin/organization/profile` |
| GET | `/api/v1/public/organization/structure` |
| GET/POST | `/api/v1/admin/organization/periods` |
| GET/PUT/DELETE | `/api/v1/admin/organization/periods/{id}` |
| GET/POST | `/api/v1/admin/organization/positions` |
| GET/PUT/DELETE | `/api/v1/admin/organization/positions/{id}` |

### 12.4 Articles

| Method | Endpoint |
|---|---|
| GET | `/api/v1/public/articles` |
| GET | `/api/v1/public/articles/{slug}` |
| GET/POST | `/api/v1/admin/articles` |
| GET/PUT/DELETE | `/api/v1/admin/articles/{id}` |
| POST | `/api/v1/admin/articles/{id}/publish` |
| GET/POST | `/api/v1/admin/article-categories` |

### 12.5 Gallery

| Method | Endpoint |
|---|---|
| GET | `/api/v1/public/galleries` |
| GET | `/api/v1/public/galleries/{id}` |
| GET/POST | `/api/v1/admin/galleries` |
| GET/PUT/DELETE | `/api/v1/admin/galleries/{id}` |
| POST/DELETE | `/api/v1/admin/galleries/{id}/photos` |

### 12.6 Activities

| Method | Endpoint |
|---|---|
| GET | `/api/v1/public/activities` |
| GET | `/api/v1/public/activities/{slug}` |
| GET/POST | `/api/v1/admin/activities` |
| GET/PUT/DELETE | `/api/v1/admin/activities/{id}` |

### 12.7 Study Schedule

| Method | Endpoint |
|---|---|
| GET | `/api/v1/public/schedule` |
| GET/POST | `/api/v1/admin/schedule` |
| GET/PUT/DELETE | `/api/v1/admin/schedule/{id}` |
| PUT | `/api/v1/admin/schedule/occurrences/{id}` |

### 12.8 Announcements

| Method | Endpoint |
|---|---|
| GET | `/api/v1/public/announcements` |
| GET/POST | `/api/v1/admin/announcements` |
| GET/PUT/DELETE | `/api/v1/admin/announcements/{id}` |

### 12.9 Attendance

| Method | Endpoint |
|---|---|
| GET/POST | `/api/v1/admin/attendance/sessions` |
| GET | `/api/v1/admin/attendance/sessions/{id}` |
| POST | `/api/v1/public/attendance/check-in` (QR-token-scoped, no login required) |
| POST | `/api/v1/admin/attendance/sessions/{id}/manual-check-in` |
| GET | `/api/v1/admin/attendance/reports/monthly` |

### 12.10 Dashboard

| Method | Endpoint |
|---|---|
| GET | `/api/v1/admin/dashboard/summary` |
| GET | `/api/v1/admin/dashboard/attendance-trend` |
| GET | `/api/v1/admin/dashboard/content-stats` |

### 12.11 Settings

| Method | Endpoint |
|---|---|
| GET/PUT | `/api/v1/admin/settings` |

### 12.12 Media

| Method | Endpoint |
|---|---|
| POST | `/api/v1/admin/media` (upload) |
| GET | `/api/v1/admin/media` |
| DELETE | `/api/v1/admin/media/{id}` |

### 12.13 Digital Library

| Method | Endpoint |
|---|---|
| GET | `/api/v1/public/library` (public items only) |
| GET/POST | `/api/v1/admin/library` |
| GET/PUT/DELETE | `/api/v1/admin/library/{id}` |
| GET/POST | `/api/v1/admin/library-categories` |

### 12.14 Standard Response Envelope

Every endpoint returns:

```json
{
  "success": true,
  "message": "string",
  "data": {},
  "meta": {},
  "errors": null
}
```

`meta` carries pagination (`current_page`, `per_page`, `total`) where applicable. `errors` is populated (and `success: false`) on 4xx/5xx responses, with per-field validation messages when relevant.

---

## 13. Public Website Specification

| Page | Content | SEO Notes |
|---|---|---|
| Home | Hero, latest announcements, upcoming activities, latest articles, quick links | Primary meta title/description, Organization JSON-LD |
| About (Tentang) | Organization profile, vision/mission, structure chart | Organization schema |
| Study Schedule (Jadwal) | Weekly recurring schedule + upcoming occurrences/overrides | Event JSON-LD per occurrence |
| Activities (Kegiatan) | List + detail pages, linked gallery | Event JSON-LD |
| Articles (Artikel) | List (paginated, filter by category) + detail | Article JSON-LD, Open Graph |
| Organization Structure | Org chart by period | — |
| Announcements (Pengumuman) | Active public announcements, pinned first | — |
| Digital Library (Perpustakaan) | Public documents, filterable by category, search | — |
| Gallery (Galeri) | Album grid + lightbox detail | ImageObject JSON-LD |
| Contact (Kontak) | Address, map embed, contact form | LocalBusiness/Organization schema |
| Footer | Quick links, social links, copyright | — |
| Navigation | Sticky header, mobile drawer menu | — |
| Search | Sitewide search across articles/library/activities | — |
| SEO | Global: sitemap.xml, robots.txt, canonical URLs, Open Graph defaults | — |

---

## 14. Admin Dashboard Specification

| Module | Screens |
|---|---|
| Dashboard | Overview cards (attendance rate, published content, pending items), recent activity feed |
| Banner | Homepage hero banner image/text management |
| Organization Profile Management | Single-record edit form |
| Organization Structure | Period list, position tree editor (drag-drop reorder) |
| Study Schedule | Template list/edit, calendar of occurrences, cancel/reschedule occurrence |
| Activities | List, create/edit form, status transitions, linked gallery/attendance |
| Articles | List (filter by status/category), rich-text editor, publish workflow |
| Gallery | Album list, bulk photo uploader, reorder photos |
| Media Library | Global asset browser, folder-like tagging, search/filter by type |
| Digital Library | Document list, upload/edit form, visibility toggle |
| Announcements | List, create/edit form, pin toggle, audience selector |
| Attendance | Session list, QR display for a session, live check-in list, manual entry |
| Statistics | Charts per Section 17 |
| Users | User list, invite/create, role assignment, deactivate |
| Roles | Role list, permission assignment matrix |
| Website Settings | Grouped settings form (general, SEO, integrations, mail) |
| Logs | Audit log viewer, filterable by user/module/date |
| Backups | Backup status/history, manual trigger (Super Admin only) |

---

## 15. Attendance System

**Workflow:** An `attendance_session` is created (automatically when a study schedule occurrence is generated, or manually for an activity). Each session has a unique, time-bound `qr_token`. Members check in either by scanning the session's QR code with their own device (hitting a public, token-scoped check-in endpoint that requires no login) or via manual entry by an admin/secretary at the venue.

**QR Code:** Generated server-side per session (token embedded in a URL, rendered as a QR image on the admin session-detail screen for display/projection at the venue). The token is valid only within the session's `opens_at`–`closes_at` window.

**Manual Attendance:** Admin/Sekretaris can add a check-in by name directly from the session's live check-in screen, for members without a device or connectivity issues.

**Reports:** Per-session check-in list (exportable), monthly summary per member (sessions attended vs. total held), organization-wide attendance trend (see Section 17).

**Monthly Summary:** Aggregated count of attended sessions per member per month, computed from `attendances` grouped by `member_reference` and month of `checked_in_at`.

**Permissions:** Create/manage sessions and manual entries: Super Admin, Sekretaris. View reports: Super Admin, Ketua, Sekretaris. Public check-in endpoint: token-scoped, no role required, rate-limited per token to prevent abuse.

---

## 16. Digital Library

**Categories:** Hierarchically flat categories (e.g., "Materi Kajian", "Khutbah", "Panduan Organisasi") managed via `library_categories`.

**Documents:** Each item stores either an uploaded file (PDF/audio/video) or an external URL (e.g., a YouTube link), never both.

**PDF:** Inline preview via a PDF.js-based viewer component on the frontend; download always available unless visibility is internal and viewer lacks access.

**Video / Audio:** HTML5 native player for uploaded media; embedded player for external YouTube links.

**Download:** Every download/view increments `download_count`, feeding Section 17 statistics.

**Search:** Filter by category, tag, and free-text title/description search (Postgres `ILIKE` initially; upgrade path to `tsvector` full-text if volume grows — see Appendix design principles on deferring optimization).

**Preview:** Thumbnail/cover for each item (auto-generated for PDFs where feasible, manually set otherwise).

---

## 17. Statistics Dashboard

**Charts:**
- Attendance trend over time (line chart, per week/month), filterable by schedule vs. activity sessions.
- Content publication volume (articles/announcements per month, bar chart).
- Digital library engagement (downloads/views per category, bar chart).
- Activity participation (attendance count per activity, bar chart).

**Reports:**
- Attendance: session-level and member-level monthly summary (Section 15).
- Visitors: public site traffic via the integrated analytics provider (Section 19), surfaced as a summary card, not re-implemented server-side.
- Articles: views per article (basic counter incremented on public detail-page load).
- Activities: count and attendance rate per activity.
- Downloads: per digital-library item and category.
- Growth: month-over-month percentage change for each of the above, computed at query time — not a stored/precomputed metric until performance requires it.

---

## 18. Media Manager

**Upload:** Drag-and-drop and click-to-browse, multi-file, progress indication, backed by Spatie Media Library's upload handling.

**Folders:** Logical grouping via a `collection` name per Spatie Media Library conventions (e.g., `gallery`, `library`, `organization`) rather than a bespoke folder table — reusing the already-adopted package instead of building custom hierarchy (Appendix design principle: prefer the dependency already in the stack).

**Image Optimization:** Automatic conversions on upload (thumbnail, medium, WebP) via Spatie Media Library's conversion pipeline; original preserved.

**File Types:** Images (jpg/png/webp), documents (pdf), audio (mp3), video (mp4), size-limited per type in configuration.

**Metadata:** Alt text and caption fields stored alongside each media item, required for images used in public-facing content (accessibility, Section 4).

**Search:** Filter by collection/type/date in the admin Media Library screen.

---

## 19. Integrations

| Integration | Purpose | Notes |
|---|---|---|
| Google Calendar | Optional sync/export of study schedule and activities | One-way export (ICS feed) initially; two-way sync deferred |
| Google Maps | Embed organization location on About/Contact pages | Static embed via API key; no live tracking |
| Instagram | Optional embed of the organization's public feed on the homepage | Read-only embed, no posting capability |
| YouTube | Embed videos in Gallery/Digital Library | oEmbed, no server-side download |
| Email | Password resets, urgent announcement digests | SMTP via queued Mailable jobs |
| Cloud Storage | Production media storage | S3-compatible driver via Laravel Filesystem |
| Analytics | Visitor tracking for the public site | Privacy-respecting provider (e.g., Plausible) or Google Analytics, script-only, no PII stored server-side |
| Search Console | SEO indexing/verification | Sitemap submission, meta verification tag in Settings |

---

## 20. Security

- **Authentication:** Laravel Sanctum, SPA-stateful cookie auth for the admin dashboard (same-site, `SameSite=Lax`/`Strict`), personal access tokens reserved for future non-browser integrations.
- **Authorization:** Every action gated by a Policy, backed by Spatie Permission roles (Section 5).
- **CSRF:** Sanctum's stateful-domain CSRF cookie mechanism for the SPA; API-token clients are exempt by design (stateless, no cookies).
- **XSS:** All user-supplied rich text (articles) sanitized server-side before storage; frontend renders via a sanitizing renderer, never raw `dangerouslySetInnerHTML` on unsanitized input.
- **SQL Injection:** Eloquent/query builder parameter binding exclusively; no raw string-concatenated queries.
- **Rate Limiting:** Laravel's throttle middleware on auth endpoints (5/min), public check-in endpoint (per-token limit), and contact form (per-IP limit).
- **Validation:** Form Requests at every mutating endpoint (Section 9).
- **Audit Log:** `activity_logs` table populated via Observers for every create/update/delete on sensitive modules (users, roles, settings, organization data).
- **Backup:** Scheduled nightly database dump + media snapshot to cloud storage, retained 30 days.
- **Encryption:** Sensitive `settings` values encrypted via Laravel's encrypted cast; all traffic served over HTTPS/TLS in production (enforced at the reverse proxy).

---

## 21. Testing Strategy

| Test Type | Scope |
|---|---|
| Unit Test | Services (business logic branches), DTOs (construction/validation), helper utilities |
| Feature Test | Full HTTP request/response cycle per API endpoint, including auth/authz and validation error paths |
| Integration Test | Repository-to-database behavior (query correctness, soft delete, audit columns) |
| API Test | Response envelope shape and status codes match the standard defined in Section 12.14 |
| Security Test | Policy boundary tests (role X cannot perform action Y), rate-limit enforcement |
| Performance Test | Load test on the highest-traffic public endpoints (schedule, articles list) before production launch |

Every module listed in Section 3 must ship at minimum one Feature test file and one Unit test file before it is considered complete, per the Development Rule in the project's `CLAUDE.md`.

---

## 22. Deployment

| Environment | Description |
|---|---|
| Development | Local Docker Compose: `php-fpm` (Laravel), `node` (Next.js dev server), `postgres`, `redis` |
| Staging | Mirrors production topology on isolated infrastructure and database, used for pre-release verification |
| Production | Dockerized services behind Nginx reverse proxy; managed PostgreSQL and Redis; object storage for media; queue workers as a separate long-running container/process |

**Server Requirements:** PHP 8.4 with extensions (pdo_pgsql, redis, gd/imagick, bcmath, mbstring, zip), Node.js 20+, PostgreSQL 16+, Redis 7+.

**Environment Variables (representative, not exhaustive):** `APP_ENV`, `APP_KEY`, `APP_URL`, `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `REDIS_HOST`, `REDIS_PASSWORD`, `SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN`, `MAIL_MAILER`, `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `FILESYSTEM_DISK`, `AWS_ACCESS_KEY_ID`/`AWS_SECRET_ACCESS_KEY`/`AWS_BUCKET` (or R2 equivalents), `QUEUE_CONNECTION`, `NEXT_PUBLIC_API_URL`.

---

## 23. Development Roadmap

| Phase | Focus | Deliverable |
|---|---|---|
| 1 | Documentation | This specification, database/API/architecture docs finalized |
| 2 | Backend Foundation | Laravel project scaffold, base folder structure, CI pipeline, database connection, base migrations (users, audit baseline) |
| 3 | Authentication | Sanctum setup, login/logout/password reset, roles/permissions seeded |
| 4 | Core Modules | Organization profile/structure, study schedule, activities, announcements (models, repositories, services, policies, migrations) |
| 5 | REST API | Controllers, Form Requests, API Resources for all modules; Postman/OpenAPI collection published |
| 6 | Admin Dashboard | Next.js admin route group, CRUD screens per module, media manager UI |
| 7 | Frontend (Public) | Next.js public route group, all pages in Section 13, SEO wiring |
| 8 | Testing | Full Feature/Unit test coverage per module, security/performance pass |
| 9 | Deployment | Docker images, staging verification, production launch, backup/monitoring wired up |

---

## 24. Future Enhancements

- Progressive Web App (offline caching, installability for the public site).
- Native mobile app (Flutter or React Native) consuming the same REST API.
- Push notifications for urgent announcements and schedule changes.
- Live streaming integration for large study sessions (*kajian akbar*).
- Multi-language support (Bahasa Indonesia + English).
- Advanced analytics (attendance prediction, engagement scoring).
- AI assistant / chatbot for FAQ and schedule lookup, grounded in the digital library and schedule data.

---

## 25. Glossary

| Term | Definition |
|---|---|
| Mudes | Short name for Pemuda Pemudi LDII Desa Condet, the organization this platform serves |
| Kajian | Islamic study session |
| Ustadz | Islamic teacher/instructor leading a study session |
| Pengurus | Organization officers/administrators |
| Kegiatan | Activity/event, generally documentation-only (no registration) |
| Pengumuman | Announcement |
| Perpustakaan Digital | Digital Library |
| RBAC | Role-Based Access Control |
| DTO | Data Transfer Object — an immutable typed object carrying data between architectural layers |
| Policy | Laravel authorization class determining whether a user may perform an action on a resource |
| Observer | Laravel class reacting to Eloquent model lifecycle events (created, updating, deleting, etc.) |
| Sanctum | Laravel's lightweight authentication package for SPAs and API tokens |
| Clean Architecture | Layered architecture separating Presentation, Application, Domain, and Infrastructure concerns |

---

## 26. Appendix

### 26.1 Coding Guidelines Recap

Follow Section 9 exactly: Repository + Service + DTO + Resource + Form Request + Policy + Observer for every module, PSR-12, strict types, constructor DI, centralized exception handling.

### 26.2 Architecture Notes Recap

Clean Architecture layering (Section 7) is non-negotiable: business logic never lives in a Controller; persistence never happens outside a Repository.

### 26.3 Design Principles

- **YAGNI at the feature level, discipline at the architecture level:** the mandated layering (Repository/Service/DTO/etc.) applies to every module regardless of size, per the project's `CLAUDE.md`, but speculative *features* beyond what's specified here are out of scope until requested.
- **Reuse before build:** Spatie Permission and Spatie Media Library are used as-is for RBAC and media/folders rather than hand-rolling equivalents (Sections 6, 18).
- **Defer optimization until measured:** e.g., digital library search starts with simple `ILIKE` filtering, with a documented upgrade path to Postgres full-text search only if volume demands it (Section 16).
- **SOLID** applied pragmatically at Service/Repository boundaries, not as ceremony inside every trivial class.

### 26.4 References

- Laravel 12 Documentation — https://laravel.com/docs
- Next.js 15 Documentation — https://nextjs.org/docs
- PHP-FIG PSR-12 Coding Style — https://www.php-fig.org/psr/psr-12/
- Spatie Laravel-Permission — https://spatie.be/docs/laravel-permission
- Spatie Laravel Media Library — https://spatie.be/docs/laravel-medialibrary
- Laravel Sanctum — https://laravel.com/docs/sanctum
- WCAG 2.1 — https://www.w3.org/TR/WCAG21/

---

*End of PROJECT_SPECIFICATION.md — this document is the single source of truth for Mudes.co development. Any deviation during implementation must be reflected back into this document per the Documentation rule in the project's CLAUDE.md.*

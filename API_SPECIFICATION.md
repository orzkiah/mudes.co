# Mudes.co — API Specification

| Field | Value |
|---|---|
| Document | API_SPECIFICATION.md |
| Companion Documents (source of truth, unmodified) | PROJECT_SPECIFICATION.md v1.0.0, DATABASE_SPECIFICATION.md **v1.1.0**, BACKEND_ARCHITECTURE.md v1.0.0 |
| Version | **1.1.0** (synchronized with `DATABASE_SPECIFICATION.md` v1.1.0 — see §14 Revision History) |
| Status | Draft for Development |
| Base URL | `https://api.mudes.co/api/v1` |
| Format | JSON only, UTF-8 |

This is an **API contract**, not Laravel documentation. No Laravel code, controllers, routes, requests, resources, or migrations are included. The response envelope, authentication flow, URL naming conventions (`/public`/`/admin` prefixing, plural-kebab resource names), and REST style established in v1.0.0 are **unchanged** — this revision only synchronizes resource shapes and adds resources that now exist in `DATABASE_SPECIFICATION.md` v1.1.0.

---

## Table of Contents

1. API Style
2. Response Format
3. Authentication
4. Global Query Conventions
5. File Uploads
6. Bulk Operations
7. Error Catalog
8. Standard Resource Contracts
9. Modules (29)
10. Enum Reference
11. Versioning Strategy
12. Webhooks (Future)
13. Appendix — Reconciliation Notes
14. Revision History

---

## 1. API Style

*(Unchanged from v1.0.0.)*

| Property | Value |
|---|---|
| Style | REST over HTTPS |
| Versioning | URI-based, current version `/api/v1` |
| Payload format | JSON only; file uploads use `multipart/form-data` |
| Statefulness | Stateless per request |
| Authentication | Laravel Sanctum — SPA cookie session for the admin dashboard; no public account system |
| Resource identifiers | UUID (v7) for every resource `id`; public detail routes additionally accept a `slug` where one exists |
| Response envelope | One consistent envelope for every response (§2) — **unchanged by this revision** |
| Error format | RFC 7807-inspired structure nested in `errors` (§2.3) |
| Pagination | Cursor for public/high-volume lists; offset for bounded admin tables (§4.4) |
| Route grouping | `/api/v1/public/...` vs `/api/v1/admin/...` — **unchanged, applied to every new resource in this revision** |
| JSON key casing | `camelCase` in every request/response body |

---

## 2. Response Format

*(Unchanged from v1.0.0 — envelope shape, success/error examples, and status-code table are not modified by this revision. See below for the unmodified reference.)*

### 2.1 Envelope

```json
{ "success": true, "message": "string", "data": {}, "meta": {}, "errors": null }
```

### 2.2–2.4 Success/Error Shapes and Standard Status Responses

Unchanged — the envelope, the RFC 7807-inspired `errors` object (`type`, `title`, `status`, `detail`, `instance`, optional `fields`), and the 200/201/401/403/404/409/500 examples from v1.0.0 apply identically to every resource in this document, including all newly added ones.

---

## 3. Authentication

Unchanged from v1.0.0 in every respect — login, logout, current user, change password, reserved forgot-password, profile, token lifecycle. This revision does not touch authentication.

---

## 4. Global Query Conventions

### 4.1 Search

Unchanged mechanics. **New searchable resources** added by this revision: Departments, Gallery Categories, Activity Categories, Study Categories (all matched on `name`, per the Taxonomy Resource Contract, §8.2), and Notifications (best-effort match against `data.message` where present — not a general JSON search, since `data` is a free-form payload; documented explicitly so it isn't mistaken for full-payload search).

### 4.2 Filtering

Unchanged mechanics (`?filter[key]=value`, unrecognized keys ignored). **New/changed common filter keys:**

| Filter Key | Meaning | Applies To |
|---|---|---|
| `filter[isActive]` | Taxonomy enabled/disabled | Departments, Gallery/Activity/Study/Article/Library Categories |
| `filter[status]` | Member lifecycle status (**replaces** the v1.0.0 `filter[isActive]` on Members — see §14 Breaking Changes) | Members |
| `filter[gender]` | Member gender | Members |
| `filter[departmentId]` | Organization position's department | Organization Positions |
| `filter[positionType]` | Organization position's functional type | Organization Positions |
| `filter[activityCategory]` | Activity's category id | Activities |
| `filter[studyCategory]` | Study Schedule's category id | Study Schedule |
| `filter[galleryCategory]` | Gallery's category id (**replaces** `filter[activityId]` as the primary Gallery filter — `filter[activityId]` remains supported as a secondary filter, see §14) | Gallery |
| `filter[isRead]` | Notification read state | Notifications |
| `filter[type]` | Notification type (§10) | Notifications |

All filter keys already documented in v1.0.0 and not listed above are unchanged.

### 4.3 Sorting

Unchanged mechanics. The manual-`order` rule ("only sortable field on modules with manual ordering; combining it with any other sort key is rejected with 422") now explicitly also governs Organization Positions' `displayOrder` and every taxonomy resource's `displayOrder` — restated, not new behavior.

### 4.4 Pagination

Unchanged strategy table. **Newly classified:** Notifications use **cursor** pagination (infinite-scroll notification list, per the existing "public-facing, high-volume, or infinite-scroll-style" criterion — notifications are per-user but unboundedly accumulating, the same shape problem cursor pagination already solves for Attendance Logs). Departments, Gallery/Activity/Study Categories use **offset** pagination (bounded admin management tables, same classification as Article/Library Categories already had).

---

## 5. File Uploads

Unchanged mechanics and limits table from v1.0.0. **New collection:** `member-photo` (image, same limits as other image collections in the table — `image/jpeg`/`image/png`/`image/webp`, 10 MB), used by the Members module (§9.5). No other collection changes.

---

## 6. Bulk Operations

The mechanics (§6 body — request/response shape, per-id partial-success reporting, 100-id cap, permission-mirrors-single-action rule) are unchanged. **The applicability table is extended:**

| Operation | Method | URL Pattern | Applies To |
|---|---|---|---|
| Bulk Delete | POST | `/admin/{resource}/bulk-delete` | Articles, Gallery, Digital Library, Announcements, Media, **and now every Taxonomy resource (§8.2)** |
| Bulk Publish | POST | `/admin/articles/bulk-publish` | Articles only — unchanged |
| Bulk Restore | POST | `/admin/{resource}/bulk-restore` | Same set as Bulk Delete |
| **Bulk Activate** | POST | `/admin/{resource}/bulk-activate` | **New — every Taxonomy resource only** (sets `isActive = true`) |
| **Bulk Deactivate** | POST | `/admin/{resource}/bulk-deactivate` | **New — every Taxonomy resource only** |
| **Bulk Reorder** | PUT | `/admin/{resource}/bulk-reorder` | **New — every Taxonomy resource, and Organization Positions** (body: `{ "order": ["id1", "id2", ...] }`, sets `displayOrder` sequentially in one call) |
| Bulk Export | GET | `/admin/{resource}/export` | Attendance Logs, Users — unchanged |

**Permissions** for the three new bulk operations mirror the single-item equivalent exactly, per the existing rule: bulk-activate/deactivate requires the same permission as a normal `PATCH` on that resource; bulk-reorder requires the same permission as the single-item reorder action.

---

## 7. Error Catalog

Unchanged — every row from v1.0.0 (`validation-failed`, `unauthenticated`, `forbidden`, `duplicate`, `dependency-conflict`, `file-invalid`, `storage-failure`, `database-error`, `not-implemented`, `rate-limited`, `internal`) applies identically to every resource added in this revision. No new error categories were needed — the new resources' failure modes (duplicate slug/name, RESTRICT-blocked delete, invalid enum) all already map to an existing category.

---

## 8. Standard Resource Contracts

v1.0.0 defined one contract ("Standard CRUD Contract"). This revision **adds a second**, because Revision 1 of `DATABASE_SPECIFICATION.md` introduced a genuinely different resource shape (the Taxonomy Table Shape, DB §2.9) shared by six tables that all need to behave identically at the API layer, per the explicit Taxonomy Standardization requirement. The two contracts coexist; a module's section in §9 states which one it follows.

### 8.1 Standard CRUD Contract (unchanged from v1.0.0)

| Step | Method | URL | Auth | Notes |
|---|---|---|---|---|
| List (public) | GET | `/public/{resource}` | None | Published/public items only; cursor-paginated |
| Detail (public) | GET | `/public/{resource}/{slug\|id}` | None | 404 if not published/public or soft-deleted |
| List (admin) | GET | `/admin/{resource}` | Session + `{resource}.view` | All statuses/visibility |
| Detail (admin) | GET | `/admin/{resource}/{id}` | Session + `{resource}.view` | |
| Create | POST | `/admin/{resource}` | Session + `{resource}.create` | 201 |
| Update | PUT | `/admin/{resource}/{id}` | Session + `{resource}.update` | 200, full-object semantics |
| Delete | DELETE | `/admin/{resource}/{id}` | Session + `{resource}.delete` | Soft delete |
| Restore | POST | `/admin/{resource}/{id}/restore` | Session + `{resource}.delete` | |

Applies to: Users, Members, Organization Profile/Structure, Study Schedule, Activities, Articles, Gallery, Digital Library, Announcements, Attendance Sessions.

### 8.2 Taxonomy Resource Contract (new)

Applies uniformly to all six taxonomy resources — **Departments, Gallery Categories, Activity Categories, Study Categories, Article Categories, Library Categories** — per `DATABASE_SPECIFICATION.md` §2.9's shared shape. This is the single place their common behavior is defined; each module's own section in §9 states only its relationship and any resource-specific deviation.

**Resource shape:**

```json
{
  "id": "uuid",
  "name": "string",
  "slug": "string",
  "description": "string | null",
  "icon": "string | null",
  "color": "string | null",
  "displayOrder": "integer",
  "isActive": "boolean",
  "itemCount": "integer",
  "createdAt": "datetime",
  "updatedAt": "datetime"
}
```

`itemCount` is computed at read time (never stored) and is named per resource for clarity in each module's own examples (`positionCount`, `articleCount`, `documentCount`, `activityCount`, `scheduleCount`, `galleryCount`) — the field is always present, only the name differs.

**Endpoints:**

| Method | URL | Auth | Description |
|---|---|---|---|
| GET | `/public/{resource}` | None | Active (`isActive = true`) only, sorted by `displayOrder` |
| GET | `/admin/{resource}` | `{resource}.view` | All (active + inactive), offset-paginated |
| GET | `/admin/{resource}/{id}` | `{resource}.view` | Detail |
| POST | `/admin/{resource}` | `{resource}.create` | Create |
| PATCH | `/admin/{resource}/{id}` | `{resource}.update` | **Partial** update — any subset of `name`/`slug`/`description`/`icon`/`color`/`displayOrder`/`isActive` |
| DELETE | `/admin/{resource}/{id}` | `{resource}.delete` | Soft delete; **409** if `itemCount > 0` (mirrors the DB's `RESTRICT`, `DATABASE_SPECIFICATION.md` §9) |
| POST | `/admin/{resource}/{id}/restore` | `{resource}.delete` | Restore |
| POST | `/admin/{resource}/bulk-activate` \| `bulk-deactivate` \| `bulk-delete` \| `bulk-restore` | same as single-item | §6 |
| PUT | `/admin/{resource}/bulk-reorder` | `{resource}.update` | Body: `{ "order": [id, id, ...] }` |

**Why `PATCH` instead of `PUT` for taxonomy resources:** taxonomy edits are typically single-field (toggle `isActive`, rename, recolor) — requiring the full object on every edit (as `PUT` does in §8.1) would make the common case clumsy. This is a deliberate, scoped deviation from the Standard CRUD Contract for this one resource class, not a general API-wide change — see §14 Architectural Decisions.

**Validation Rules (common to all six):**

| Field | Rule |
|---|---|
| `name` | Required, max 100 chars |
| `slug` | Optional on create (auto-generated from `name` if omitted); if supplied, validated unique among non-deleted rows of that table — **422 on collision, never silently suffixed** (same policy as Articles) |
| `description` | Optional, free text |
| `icon` | Optional, max 100 chars, opaque string (frontend-interpreted) |
| `color` | Optional, must match `^#[0-9A-Fa-f]{6}$` |
| `displayOrder` | Optional integer; if omitted on create, appended after the current maximum |
| `isActive` | Optional boolean, default `true` |

**Departments' one addition to this common rule set:** `name` must also be unique among non-deleted rows (422 `fields.name: ["A department with this name already exists."]`) — the only taxonomy resource with this extra constraint, mirroring `DATABASE_SPECIFICATION.md` §4.6's `uq_departments_name`.

**Filtering:** `filter[isActive]`. **Search:** `search` matches `name`. **Sorting:** `displayOrder` (default), `name`, `createdAt` — never combined with `displayOrder` (§4.3). **Pagination:** offset (§4.4).

---

## 9. Modules

### 9.1 Authentication

Covered in §3 — unchanged.

### 9.2 Users

Unchanged from v1.0.0.

### 9.3 Roles

Unchanged from v1.0.0.

### 9.4 Permissions

Unchanged from v1.0.0.

### 9.5 Members — modified

**Purpose:** unchanged — stable member identity, independent of staff `users` accounts (`DATABASE_SPECIFICATION.md` §4.2, reaffirmed in Revision 1).

**Resource shape (updated):**

```json
{
  "id": "uuid",
  "fullName": "string",
  "gender": "male | female | null",
  "birthDate": "date | null",
  "phone": "string | null",
  "photo": "MediaObject | null",
  "joinDate": "date | null",
  "status": "active | inactive | alumni | moved_out",
  "notes": "string | null",
  "userId": "uuid | null",
  "createdAt": "datetime",
  "updatedAt": "datetime"
}
```

**Removed field:** `isActive` (boolean) — see §14 Breaking Changes.

**Endpoints:** Standard CRUD Contract (§8.1), admin-only — unchanged.

**Create — request (updated example):**
```json
POST /api/v1/admin/members
{
  "fullName": "Ahmad Fauzi",
  "gender": "male",
  "birthDate": "2005-03-12",
  "phone": "+6281234567890",
  "joinDate": "2023-01-15",
  "photoMediaId": "0191f444-..."
}
```

**Validation Rules:** `fullName` required, max 150; `gender` optional, one of `male`/`female`; `birthDate` optional, valid past date; `joinDate` optional, valid date; `status` optional, one of `active`/`inactive`/`alumni`/`moved_out`, default `active`; `photoMediaId` optional, must reference an existing, non-deleted media item in the `member-photo` collection (§5).

**Query Parameters:** `search` (`fullName`), `filter[status]` (**replaces** `filter[isActive]`), `filter[gender]`, `sort` (`fullName`, `joinDate`, `createdAt`), offset pagination.

**Relationship with Users:** unchanged — `userId` is read-only on this resource (set only via the Users module's linking flow, per v1.0.0's existing rule; not writable through `PATCH`/`PUT` on Members directly, to avoid two write paths for the same relationship).

**Permissions:** unchanged (`members.view` — Sekretaris, Super Admin, Ketua; `members.create`/`update`/`delete` — Sekretaris, Super Admin).

**Edge Cases:** unchanged — deleting a member preserves the denormalized `memberName` snapshot on historical `attendances` rows.

### 9.6 Organization Profile

Unchanged from v1.0.0.

### 9.7 Departments — new (replaces the v1.0.0 "logical view" reconciliation)

**Purpose:** dedicated organizational grouping (`DATABASE_SPECIFICATION.md` §4.6). In v1.0.0 this was documented as an alias for top-level Organization Positions; that framing is now **obsolete** — see §14.

**Follows:** Taxonomy Resource Contract (§8.2), `itemCount` named `positionCount`.

**Relationship:** `departments` 1—* `organizationPositions`.

**Endpoints (concrete, per §8.2):**

| Method | URL |
|---|---|
| GET | `/public/departments` |
| GET | `/admin/departments` |
| GET | `/admin/departments/{id}` |
| POST | `/admin/departments` |
| PATCH | `/admin/departments/{id}` |
| DELETE | `/admin/departments/{id}` |
| POST | `/admin/departments/{id}/restore` |
| POST | `/admin/departments/bulk-activate` \| `bulk-deactivate` \| `bulk-delete` \| `bulk-restore` |
| PUT | `/admin/departments/bulk-reorder` |

**Create — request:**
```json
POST /api/v1/admin/departments
{ "name": "Bidang Humas", "description": "Hubungan masyarakat dan komunikasi", "icon": "megaphone", "color": "#4F46E5" }
```

**Create — response (201):**
```json
{ "success": true, "message": "Department created successfully.", "data": {
  "id": "0191f555-...", "name": "Bidang Humas", "slug": "bidang-humas",
  "description": "Hubungan masyarakat dan komunikasi", "icon": "megaphone", "color": "#4F46E5",
  "displayOrder": 3, "isActive": true, "positionCount": 0,
  "createdAt": "2026-07-19T02:00:00Z", "updatedAt": "2026-07-19T02:00:00Z"
}, "meta": {}, "errors": null }
```

**Validation:** Taxonomy Resource Contract common rules (§8.2), **plus** `name` unique among non-deleted departments (422 on duplicate).

**Business Rules:** a department can be disabled (`isActive = false`) without deleting it — this hides it from the public org-chart's department grouping but preserves its position assignments (`DATABASE_SPECIFICATION.md` §4.6). Deleting a department with `positionCount > 0` returns **409** (`urn:mudes:error:dependency-conflict`) — positions must be reassigned to another department (or unassigned) first.

**Permissions:** view — all + public; write — Super Admin, Ketua, Sekretaris (same as Organization Structure, §9.8).

### 9.8 Organization Structure — modified

**Purpose:** unchanged — leadership/member hierarchy per period (`DATABASE_SPECIFICATION.md` §4.7–4.8).

**Resource shapes (updated):**
- Period: `{ id, label, startDate, endDate, isActive }` — unchanged.
- Position: `{ id, organizationPeriodId, departmentId, department: { id, name, icon, color } | null, parentPositionId, positionType, level, title, member: { id, fullName } | null, displayOrder }`

**Endpoints:**

| Method | URL | Description |
|---|---|---|
| GET | `/public/organization/structure` | The active period's positions, tree-shaped (nested `children[]`), now including each node's `department` and `level` |
| GET/POST | `/admin/organization/periods` | Unchanged |
| GET/PUT/DELETE | `/admin/organization/periods/{id}` | Unchanged |
| POST | `/admin/organization/periods/{id}/activate` | Unchanged |
| GET/POST | `/admin/organization/positions` | List (filterable by `organizationPeriodId`, **new:** `departmentId`, `positionType`) / create |
| GET | `/admin/organization/positions/tree?organizationPeriodId=...` | **New** — full nested tree for any period (not only the active one), for the admin drag-and-drop management UI |
| GET/PUT/DELETE | `/admin/organization/positions/{id}` | Standard CRUD |
| PUT | `/admin/organization/positions/{id}/reorder` | Body: `{ "displayOrder": 3, "parentPositionId": "...", "departmentId": "..." }` — **`departmentId` is new** on this existing endpoint |

**Reorder / drag-and-drop semantics:** reordering is scoped to siblings sharing the same `(organizationPeriodId, parentPositionId)` — moving a position to a new `parentPositionId` recomputes `level` for that position and its **entire subtree** in the same request (mirrors the Service-layer rule in `DATABASE_SPECIFICATION.md` §4.8). The reorder response includes `affectedDescendantCount` so the frontend knows how many nested items had their `level` updated:

```json
{ "success": true, "message": "Position reordered successfully.", "data": {
  "id": "...", "parentPositionId": "0191f666-...", "departmentId": "0191f555-...", "level": 2, "displayOrder": 1,
  "affectedDescendantCount": 4
}, "meta": {}, "errors": null }
```

**Departments and Management (reconciliation, updated from v1.0.0):**
- **Departments** is now its own resource (§9.7) — the v1.0.0 note that it was "an alias for top-level positions, no separate resource" is **superseded** and removed from this document.
- **Management** (assigning a member to a position) remains an alias for a position's `member` field, set via `PATCH`... actually via the existing `PUT /admin/organization/positions/{id}` with body `{ "memberId": "..." }` — **unchanged**, still no separate "Management" resource, since `DATABASE_SPECIFICATION.md` v1.1.0 did not add a dedicated table for it.

**Validation:** unchanged cycle-prevention rule for `parentPositionId`; **new:** `positionType` required, one of the six values (§10); `departmentId` optional, must reference an existing, non-deleted department.

**Permissions:** unchanged — view all + public (active period); write Super Admin, Ketua, Sekretaris.

**Implementation note:** Organization Positions (this section's endpoints under `/organization/positions`) shipped as its own step with the full stack described above, including cycle prevention and the reorder/level-recompute semantics. `organization_periods` exists only as the minimal table this module's required `organizationPeriodId` FK points at (seeded with one active period) — the `/admin/organization/periods` endpoints listed above are **not yet implemented**; periods can only be created via seeder/factory until that follow-up module ships.

### 9.9 Study Categories — new

**Follows:** Taxonomy Resource Contract (§8.2), `itemCount` named `scheduleCount`.

**Relationship:** `studyCategories` 1—* `studySchedules`.

**Endpoints:** exactly the Taxonomy Resource Contract's endpoint set at `/{public|admin}/study-categories`.

**Seed note:** examples of expected values (`Weekly Study`, `Monthly Study`, `Youth Study`, `Special Study`, `Ramadan`, `National Event`) are admin-managed data, not fixed API-level constants — the API places no constraint on which category names exist, only that every Study Schedule must reference one.

**Permissions:** view — all + public; write — Super Admin, Ketua, Sekretaris (same as Study Schedule, §9.10).

### 9.10 Study Schedule — modified

**Resource shape (updated):** `{ id, studyCategoryId, category: { id, name, slug, icon, color }, dayOfWeek, startTime, endTime, topic, ustadzName, location, isActive }` — `category` is the expanded object, matching the pattern already used for Articles' category.

**Endpoints:** unchanged from v1.0.0 (`GET /public/schedule`, `GET/POST /admin/schedule`, `GET/PUT/DELETE /admin/schedule/{id}`, occurrence endpoints).

**Validation:** existing `dayOfWeek`/`endTime` rules unchanged; **new:** `studyCategoryId` required, must reference an existing, non-deleted study category.

**Query Parameters:** existing filters unchanged, **plus new** `filter[studyCategory]`.

**Note:** occurrences (`study_schedule_occurrences`) do not carry their own category — they inherit it via their parent schedule; the occurrence resource shape is unchanged.

### 9.11 Activity Categories — new

**Follows:** Taxonomy Resource Contract (§8.2), `itemCount` named `activityCount`.

**Relationship:** `activityCategories` 1—* `activities`.

**Endpoints:** the Taxonomy Resource Contract's endpoint set at `/{public|admin}/activity-categories`.

**Permissions:** view — all + public; write — Super Admin, Ketua, Sekretaris, Humas (same as Activities, §9.13).

### 9.12 Activity Calendar

Unchanged from v1.0.0 — still a read-only computed aggregation over Activities and Study Schedule Occurrences, no dedicated table (`DATABASE_SPECIFICATION.md` v1.1.0 did not add one). Each item's minimal projection is unaffected by this revision; a client wanting category information follows up with the Activities (§9.13) or Study Schedule (§9.10) detail endpoint, both of which now include an expanded `category`.

### 9.13 Activities — modified

**Resource shape (updated):** `{ id, activityCategoryId, category: { id, name, slug, icon, color }, title, slug, description, startAt, endAt, location, status, cover }`.

**Endpoints:** unchanged (Standard CRUD Contract, §8.1).

**Validation:** existing `endAt`-after-`startAt` rule unchanged; **new:** `activityCategoryId` required, must reference an existing, non-deleted activity category.

**Query Parameters:** existing filters unchanged, **plus new** `filter[activityCategory]`.

### 9.14 Article Categories — modified (Taxonomy Standardization)

**Follows:** Taxonomy Resource Contract (§8.2) as of this revision — in v1.0.0 this module followed the Standard CRUD Contract with a bare `{ id, name, slug, articleCount }` shape. `itemCount` is named `articleCount` (unchanged name, now with the full taxonomy shape's `description`/`icon`/`color`/`displayOrder`/`isActive` fields alongside it).

**Endpoints:** now `/{public|admin}/article-categories` per §8.2, including the new `PATCH` update, restore, and bulk endpoints.

**Deprecated behavior:** the v1.0.0 `PUT /admin/article-categories/{id}` full-update endpoint **remains functional** (full-object replace semantics) for backward compatibility, but is deprecated in favor of `PATCH` — see §14.

**Business Rules:** unchanged — `DELETE` on a category with `articleCount > 0` → 409, mirroring `ON DELETE RESTRICT`.

**Permissions:** unchanged (write — Super Admin, Ketua, Humas).

### 9.15 Articles

Unchanged from v1.0.0, except the expanded `category` object on the Article resource now additionally carries `icon`/`color` (passthrough of Article Categories' enriched shape, §9.14) — no endpoint, validation, or permission change.

### 9.16 Gallery Categories — new (replaces the v1.0.0 flagged gap)

**Purpose:** in v1.0.0, this module did not exist — galleries were organized only through an optional Activity link, and the gap was explicitly flagged rather than worked around. `DATABASE_SPECIFICATION.md` §4.16 (v1.1.0) closes it.

**Follows:** Taxonomy Resource Contract (§8.2), `itemCount` named `galleryCount`.

**Relationship:** `galleryCategories` 1—* `galleries` (the gallery's **primary, required** organizing relationship — see §9.17). A gallery may **additionally** link to an Activity, independently and optionally, unchanged from v1.0.0.

**Endpoints:** the Taxonomy Resource Contract's endpoint set at `/{public|admin}/gallery-categories`.

**Permissions:** view — all + public; write — Multimedia, Super Admin (same as Gallery, §9.17).

### 9.17 Gallery — modified

**Resource shape (updated):** `{ id, galleryCategoryId, category: { id, name, slug, icon, color }, activityId, title, description, coverPhoto, photoCount, photos: [...] }` — `galleryCategoryId`/`category` are **new and required**; `activityId` is unchanged and remains optional.

**Endpoints:** unchanged (album CRUD per §8.1, plus the existing photo-attach/remove/reorder endpoints).

**Validation:** **new** — `galleryCategoryId` required, must reference an existing, non-deleted gallery category. `activityId`, if present, unchanged validation (must exist).

**Query Parameters:** `filter[galleryCategory]` (**new, primary filter**), `filter[activityId]` (unchanged, now a secondary/complementary filter rather than the only one), `search` (title), `sort` (`createdAt`, `title`).

**Create — request (updated example):**
```json
POST /api/v1/admin/galleries
{ "title": "Dokumentasi Bakti Sosial 2026", "galleryCategoryId": "0191f777-...", "activityId": "0191f888-..." }
```

### 9.18 Digital Library

Unchanged from v1.0.0 in endpoints/validation. The resource's expanded `category` object now carries `icon`/`color` (passthrough of Library Categories' enriched shape, §9.19). See §10 for the new computed `libraryType` field.

### 9.19 Library Categories — modified (Taxonomy Standardization)

Identical change pattern to Article Categories (§9.14): now follows the Taxonomy Resource Contract (§8.2), `itemCount` named `documentCount`, `PATCH` added, v1.0.0's `PUT` kept as deprecated-but-functional. Business rule (409 on delete-with-references) unchanged.

### 9.20 Announcements

Unchanged from v1.0.0.

### 9.21 Notifications — new

**Purpose:** in-admin notifications backed by `DATABASE_SPECIFICATION.md` §4.5 (v1.1.0) and the channel-agnostic design already specified in `BACKEND_ARCHITECTURE.md` §16. Every endpoint here is implicitly scoped to the **authenticated caller's own notifications** (`notifiable = current user`) — there is no cross-user notification listing, and no separate permission is required beyond being authenticated, since a user always has access to their own notifications regardless of role.

**Resource shape:**

```json
{
  "id": "uuid",
  "type": "announcement | attendance_reminder | study_reminder | content_approval | system",
  "data": { "message": "string", "resourceType": "string | null", "resourceId": "uuid | null", "actionUrl": "string | null" },
  "isRead": "boolean",
  "readAt": "datetime | null",
  "createdAt": "datetime"
}
```

`type` is a stable, API-facing enum (§10) — it is **not** the raw internal Notification class name stored in the database column of the same name; the API Resource layer translates the internal class to this public value, so the internal implementation can change without breaking clients.

**Endpoints:**

| Method | URL | Description |
|---|---|---|
| GET | `/admin/notifications` | List, cursor-paginated, `filter[isRead]`, `filter[type]`, `search` (matches `data.message`) |
| GET | `/admin/notifications/unread-count` | `{ "count": 5 }` |
| POST | `/admin/notifications/{id}/mark-read` | Sets `readAt = now()`; idempotent if already read |
| POST | `/admin/notifications/mark-all-read` | Marks every unread notification for the caller; response `data.updatedCount` |
| DELETE | `/admin/notifications/{id}` | Hard delete (notifications are not soft-deleted, per `DATABASE_SPECIFICATION.md` §4.5's documented exception) — 404 if the notification does not belong to the caller |

**Notification Types (behavioral notes):**

| Type | Triggered By | Payload (`data`) Contents |
|---|---|---|
| `announcement` | An `urgent`-priority Announcement is created | `message`, `resourceType: "announcement"`, `resourceId`, `actionUrl` |
| `attendance_reminder` | Scheduled job, ahead of an upcoming attendance session | `message`, `resourceType: "attendance_session"`, `resourceId` |
| `study_reminder` | Scheduled job, ahead of an upcoming study schedule occurrence | `message`, `resourceType: "study_schedule_occurrence"`, `resourceId` |
| `content_approval` | Editor-authored content awaiting Ketua/Super Admin publish approval | `message`, `resourceType: "article"`, `resourceId`, `actionUrl` |
| `system` | Any other system-generated notice | `message` only, `resourceType`/`resourceId` may be `null` |

**Future Delivery Channels:** this REST list represents the **database** notification channel only. A future `push` channel (device notifications) does not add new API surface here — per `BACKEND_ARCHITECTURE.md` §16, delivery channel is transparent to the client fetching this list; adding push is an internal delivery-mechanism change, not a contract change.

**Response Example (list):**
```json
{ "success": true, "message": "Notifications retrieved successfully.", "data": [
  { "id": "...", "type": "announcement", "data": { "message": "Pengumuman penting: Libur kajian Ahad ini.", "resourceType": "announcement", "resourceId": "...", "actionUrl": "/pengumuman/..." }, "isRead": false, "readAt": null, "createdAt": "2026-07-18T10:00:00Z" }
], "meta": { "pagination": { "strategy": "cursor", "perPage": 20, "nextCursor": null, "prevCursor": null, "hasMore": false } }, "errors": null }
```

**Permissions:** self-scoped only, as stated above — no role-based permission beyond authentication.

### 9.22 Attendance

Unchanged from v1.0.0.

### 9.23 Attendance Sessions

Unchanged from v1.0.0.

### 9.24 Attendance Logs

Unchanged from v1.0.0.

### 9.25 Dashboard Analytics

Unchanged from v1.0.0 — no new endpoint added by this revision (out of scope per the instruction not to invent unrelated features). Existing `content-stats` responses may incidentally include the new taxonomy `category`/`department` objects wherever they already return the underlying resource, with no change to the endpoint's own contract.

### 9.26 Website Settings — modified (implemented as full CRUD)

**Implementation note:** when this module was actually built, it was implemented as a full CRUD resource (Standard CRUD Contract, §8.1) rather than the bulk-key-value-bag shape originally described here — settings turned out to warrant independent create/delete of individual rows (not just a fixed set edited in bulk), plus search/filter/sort/pagination like any other admin-managed list. This section is corrected to match the implementation.

**Resource shape:** `{ id, key, value, type, group, description, isEncrypted, autoload, createdAt, updatedAt }`. `autoload` is **read-only** — returned for admin visibility, never accepted in a request body (it reflects a deployment-time classification of the setting, not an admin-editable behavior). Encrypted values (`isEncrypted: true`) are always returned masked as `"••••••••"`, never round-tripped in plaintext.

**Endpoints (Standard CRUD Contract, admin-only, `auth:sanctum` + `settings.*` permissions):**

| Method | URL | Permission |
|---|---|---|
| GET | `/admin/settings` | `settings.view` — paginated (offset), filterable, searchable, sortable |
| GET | `/admin/settings/{id}` | `settings.view` |
| POST | `/admin/settings` | `settings.create` |
| PUT | `/admin/settings/{id}` | `settings.update` — full-object replace, matching the Standard CRUD Contract (not a partial `PATCH`) |
| DELETE | `/admin/settings/{id}` | `settings.delete` — soft delete |
| POST | `/admin/settings/{id}/restore` | `settings.restore` |

**Query Parameters:** `filter[group]`, `filter[type]`, `filter[is_encrypted]`; `search` (matches `key`/`description`); `sort` (`key`, `group`, `created_at`, `updated_at` — **note:** sort/filter keys here are the literal snake_case database columns, a deliberate, documented exception to this document's general camelCase-in-body convention, which applies to JSON payloads, not query-string keys); `perPage` (default 20, capped at 100).

**Validation:** `key` required, unique among non-deleted settings; `type` required, one of `string`/`number`/`boolean`/`json`/`encrypted`; `value`, `group`, `description` optional; `isEncrypted` optional boolean, default `false`.

**Permissions:** unchanged in spirit — Super Admin only, now expressed as five granular permissions (`settings.view`/`create`/`update`/`delete`/`restore`) all assigned to the `super-admin` role, per the one-enforcement-path convention (`BACKEND_ARCHITECTURE.md` §11) rather than a hardcoded role check.

### 9.27 Media Manager

Unchanged from v1.0.0, aside from the new `member-photo` collection (§5).

### 9.28 Audit Logs

Unchanged from v1.0.0.

### 9.29 Health Check

Unchanged from v1.0.0.

---

## 10. Enum Reference

Every enumerated value exposed by this API, in one place. Where the API-facing enum differs from the raw database `CHECK` value set (`DATABASE_SPECIFICATION.md` §5), the mapping is stated.

| Enum | Values | Used By | DB Source |
|---|---|---|---|
| **Publication Status** (`status` on Articles) | `draft`, `scheduled`, `published`, `archived` | Articles | `articles.status` (identical) |
| **Activity Status** (`status` on Activities) | `upcoming`, `ongoing`, `completed`, `cancelled` | Activities | `activities.status` (identical) |
| **Study Occurrence Status** (`status` on Study Schedule Occurrences) | `scheduled`, `cancelled`, `completed` | Study Schedule Occurrences | `study_schedule_occurrences.status` (identical) |
| **Visibility** (`visibility` on Digital Library) | `public`, `internal` | Digital Library | `library_documents.visibility` (identical) |
| **Announcement Priority** | `normal`, `urgent` | Announcements | `announcements.priority` (identical) |
| **Announcement Audience** | `public`, `internal` | Announcements | `announcements.audience` (identical) |
| **Attendance Method** | `qr`, `manual` | Attendance Logs | `attendances.method` (identical) |
| **Attendance Session Source Type** | `schedule_occurrence`, `activity` | Attendance Sessions | `attendance_sessions.source_type` (identical) |
| **Setting Type** | `string`, `number`, `boolean`, `json`, `encrypted` | Website Settings | `settings.type` (identical) |
| **Member Status** | `active`, `inactive`, `alumni`, `moved_out` | Members | `members.status` (identical, new in DB v1.1.0) |
| **Member Gender** | `male`, `female` | Members | `members.gender` (identical, new in DB v1.1.0) |
| **Position Type** | `chairman`, `vice_chairman`, `secretary`, `treasurer`, `coordinator`, `member` | Organization Positions | `organization_positions.position_type` (identical, new in DB v1.1.0) |
| **Notification Type** | `announcement`, `attendance_reminder`, `study_reminder`, `content_approval`, `system` | Notifications | **derived** — API-facing translation of the internal `notifications.type` PHP class-name column (§9.21); not a 1:1 passthrough |
| **Library Type** *(computed, not stored)* | `pdf`, `audio`, `video_link` | Digital Library | derived from `library_documents.file_media_id`'s `mimeType` or the presence of `external_url` — a convenience field for frontend icon selection, not a database column |
| **Media Type** *(computed, not stored)* | `image`, `pdf`, `audio` | Media Manager | derived from `media.mime_type` — same convenience-field rationale as Library Type |

---

## 11. Versioning Strategy

Unchanged from v1.0.0. This revision adds resources to `v1` additively (new endpoints, new optional fields on existing resources) with two narrow exceptions treated as documented breaking changes rather than silent ones (Members' `isActive`→`status`, Gallery's category becoming required) — both are non-additive by necessity (they reflect a genuine schema change in `DATABASE_SPECIFICATION.md`, not an API design choice) and are called out in full in §14, consistent with the Deprecation Policy already defined in v1.0.0.

---

## 12. Webhooks (Future)

Unchanged from v1.0.0 — not implemented at launch. The candidate event list is unaffected by this revision (no new webhook-worthy domain event was introduced; `notifications` is itself the in-app delivery mechanism, not a webhook trigger).

---

## 13. Appendix — Reconciliation Notes

Updated from v1.0.0. Two of the four original reconciliation notes are now **resolved** by dedicated tables/resources; two remain valid.

| Originally Requested Module | v1.0.0 Resolution | v1.1.0 Status |
|---|---|---|
| Departments | Alias for top-level `organization_positions` | **Resolved** — dedicated resource, §9.7 |
| Management | Alias for a position's `member` field | **Still an alias** — no dedicated table was added in `DATABASE_SPECIFICATION.md` v1.1.0; unchanged, see §9.8 |
| Activity Calendar | Computed aggregation, no dedicated table | **Still computed** — unchanged, see §9.12 |
| Gallery Categories | Flagged as a genuine gap, no table existed | **Resolved** — dedicated resource, §9.16 |

---

## 14. Revision History

### Version 1.1.0 — 2026-07-18 (synchronized with `DATABASE_SPECIFICATION.md` v1.1.0)

Performed as a scoped synchronization, not a redesign. The response envelope, authentication flow, and URL naming/prefixing conventions are unmodified.

#### Added Endpoints

| Resource | Endpoints |
|---|---|
| Departments (§9.7) | Full Taxonomy Resource Contract set at `/{public\|admin}/departments` |
| Gallery Categories (§9.16) | Full Taxonomy Resource Contract set at `/{public\|admin}/gallery-categories` |
| Activity Categories (§9.11) | Full Taxonomy Resource Contract set at `/{public\|admin}/activity-categories` |
| Study Categories (§9.9) | Full Taxonomy Resource Contract set at `/{public\|admin}/study-categories` |
| Notifications (§9.21) | `GET /admin/notifications`, `GET /admin/notifications/unread-count`, `POST /admin/notifications/{id}/mark-read`, `POST /admin/notifications/mark-all-read`, `DELETE /admin/notifications/{id}` |
| Organization Positions (§9.8) | `GET /admin/organization/positions/tree` |
| Article Categories, Library Categories (§9.14, §9.19) | `PATCH` update, `restore`, and all four bulk endpoints (previously had only the Standard CRUD Contract's `PUT`/delete) |
| All six Taxonomy resources | `bulk-activate`, `bulk-deactivate`, `bulk-reorder` (§6) |

#### Modified Endpoints

| Endpoint | Change |
|---|---|
| `POST`/`PUT` Members | Resource shape adds `gender`, `birthDate`, `photo`, `joinDate`, `notes`, `status`; removes `isActive` |
| `POST`/`PUT` Study Schedule | `studyCategoryId` now required |
| `POST`/`PUT` Activities | `activityCategoryId` now required |
| `POST`/`PUT` Gallery | `galleryCategoryId` now required; `activityId` demoted from primary to secondary/optional filter |
| `PUT /admin/organization/positions/{id}/reorder` | Body gains optional `departmentId`; response gains `affectedDescendantCount` |
| `GET/PUT /admin/settings` | Response gains `group`, `description`, `autoload` (`autoload` read-only) |
| Article/Library Category endpoints | Resource shape gains `description`/`icon`/`color`/`displayOrder`/`isActive`; update method changes from exclusively `PUT` to `PATCH`-preferred (`PUT` retained, deprecated) |

#### Deprecated Behavior

| Behavior | Status | Removal Plan |
|---|---|---|
| `PUT /admin/article-categories/{id}` and `PUT /admin/library-categories/{id}` (full-object update) | Deprecated, still functional | Minimum 6-month overlap per the existing Deprecation Policy (§11); clients should migrate to `PATCH` |
| `filter[isActive]` on Members | **Removed, not merely deprecated** — no `isActive` field exists on the Member resource to filter on anymore | None — this is a hard break, since the underlying column no longer exists (`DATABASE_SPECIFICATION.md` §11's Migration Note #1 is a coordinated, single-deployment change, not a gradual one); `filter[status]` is the only replacement |
| `filter[activityId]` on Gallery | Still functional, demoted to secondary filter | No removal planned — remains valid indefinitely as a legitimate secondary filter |

#### Backward Compatibility Notes

- Every new resource (Departments, Gallery/Activity/Study Categories, Notifications) is additive — no existing client integration is affected by their introduction.
- The Article/Library Category shape enrichment (`icon`/`color`/`displayOrder`/`isActive`) is additive; existing clients reading only `id`/`name`/`slug`/`articleCount` (or `documentCount`) continue to work unmodified.
- **Two genuine breaking changes**, both required by non-additive changes already made in `DATABASE_SPECIFICATION.md` v1.1.0 (its own Migration Notes, §11 of that document), not introduced independently here:
  1. **Members:** `isActive` (boolean) → `status` (enum). Any client request body or query filter using `isActive` on this resource will now be silently ignored (unknown field) rather than erroring — clients must update to `status` explicitly. Documented here and in `DATABASE_SPECIFICATION.md` §11 as the same coordinated change.
  2. **Gallery:** `galleryCategoryId` is now required on create. Any existing client `POST /admin/galleries` call omitting it will now receive a 422, where it previously succeeded.
- All other v1.0.0 endpoints, examples, and behaviors not mentioned in this section are unchanged and remain valid.

#### Migration Notes

For frontend/backend teams implementing against this revision:

1. Update any Member create/update form to send `status` instead of `isActive`, and `gender`/`birthDate`/`joinDate`/`notes`/`photoMediaId` as new optional fields.
2. Update Activity/Study Schedule/Gallery create forms to require selecting a category — these forms cannot submit successfully without one as of this revision; populate the category picker from the corresponding new Taxonomy endpoint's `/public/{resource}` (or `/admin/{resource}` for the admin create form, to include inactive-but-still-assignable categories if that's the desired UX — the choice belongs to the frontend team based on whether disabled categories should remain selectable).
3. Any UI reading Article/Library Categories and expecting only `{ id, name, slug, articleCount }` should be updated to also render the new `description`/`icon`/`color` fields, or may safely ignore them (additive).
4. Implement the Notifications bell/list UI against §9.21; poll `unread-count` or call it alongside the main dashboard load, per `BACKEND_ARCHITECTURE.md` §16's "no websocket layer" decision (unchanged, restated here for the frontend team's awareness).

#### Known Limitations

- Notifications are **not real-time** — there is no websocket/push channel yet (`BACKEND_ARCHITECTURE.md` §16, unchanged); the frontend must poll `GET /admin/notifications/unread-count` on an interval or on navigation, not expect live updates.
- `organization_positions.level`, returned by this API, is a denormalized value maintained server-side; per `DATABASE_SPECIFICATION.md` §11's Known Limitations, it should be treated by API consumers as display-only, never as an authorization signal.
- The `PATCH` vs. `PUT` split (taxonomy resources vs. everything else) is a deliberate, documented inconsistency across resource classes in this same API — a developer working across both must be aware which contract a given module follows (§8 states it per module); this is called out explicitly rather than left for a developer to discover by trial and error.

#### Architectural Decisions

| Decision | Rationale |
|---|---|
| Introduce a second contract (§8.2 Taxonomy Resource Contract) rather than retrofit `PATCH` onto the existing Standard CRUD Contract globally | The explicit requirement was uniform behavior *across the six taxonomy resources*, not a change to every resource in the API; scoping the new contract to exactly the resources that need it avoids an unrequested, broader API redesign |
| Keep `PUT` functional (deprecated) on the two pre-existing taxonomy endpoints (Article/Library Categories) rather than a hard cutover | "Preserve backward compatibility whenever possible" — these two endpoints had real v1.0.0 clients-in-spec; the four brand-new taxonomy resources have no prior clients, so they launch with `PATCH` only from day one, with no deprecation burden to carry |
| `notifications.type` translated to a stable API-facing enum rather than passed through as the internal PHP class name | The database column stores an implementation detail (`BACKEND_ARCHITECTURE.md` §16); exposing it directly would couple every API client to internal class naming, breaking on a harmless internal rename — the same reasoning already applied to `articles.status` and every other DB-`CHECK`-backed enum in this document |
| Members' `isActive`→`status` and Gallery's new required category are treated as unavoidable breaking changes, not redesigned around | Both stem directly from a genuine schema change already made and justified in `DATABASE_SPECIFICATION.md` v1.1.0; papering over them with a compatibility shim (e.g., silently deriving a fake `isActive` from `status`, or defaulting an omitted `galleryCategoryId` to some category) would hide a real, intentional data-model change from API consumers who need to know about it |
| `libraryType`/`mediaType` added as computed, non-stored enum fields | A small, clearly-justified addition for frontend convenience (icon selection), not present in `DATABASE_SPECIFICATION.md` — kept computed-only so it never needs its own migration or can drift from the actual `mimeType`/`external_url` source of truth |

---

*End of API_SPECIFICATION.md — read together with `PROJECT_SPECIFICATION.md`, `DATABASE_SPECIFICATION.md` v1.1.0, and `BACKEND_ARCHITECTURE.md`, this is the complete, synchronized contract for backend implementation and frontend consumption. Any further change must be reflected back into this document as Revision 2.*

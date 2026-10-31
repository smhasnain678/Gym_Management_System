# WarmUp — Gym Management System
## Product Requirements Document (PRD) — Revision Review Draft
**Document Version:** 2.0-DRAFT  •  **FYP Final Target:** February 2027  •  **Status:** Proposed Review Draft  
**Project Path:** `J:\xampp\htdocs\FYP`  •  **Base Document:** `PRD.md` (v1.5, Sept 2026)

---

## Document Control & Revision Notice

> **Important Notice on Document Status & Testing Baseline:**  
> This document (`PRD_REVIEW_DRAFT.md`) is a comprehensive proposed revision of the original `PRD.md` (v1.5). It incorporates the finalized architecture decisions for the Final Year Project (FYP) submission and presentation (February 2027), expanding WarmUp from a single-user Gym Owner dashboard into a secure, multi-role platform with role-based access control (RBAC), an explicit three-option entry portal, unique Member Code authentication, dedicated Member and Staff experiences, Workout and Diet planning modules, client-side mutation deduplication, and encrypted offline storage namespaces.
>
> **Testing Baseline Statement:** PRD v1.5 documents a historical baseline of **242 automated tests and 699 assertions with 0 failures** (recorded in repository cache as of Phase 15, September 2026). This baseline is pending formal execution in the current environment. All 242 historical tests must be preserved and kept green as core functionality is extended.
>
> **Schema & SQL Synchronization:** The `database/warmup.sql` baseline script remains deferred and frozen until the new migration files are finalized, executed, and validated against both MySQL and the SQLite in-memory test runner.

| Field | Detail |
|---|---|
| Product Name | WarmUp |
| Document Type | Product Requirements Document (PRD) — Proposed Revision |
| Version | 2.0-DRAFT |
| Base Version | 1.5 (Synchronized Edition) |
| Author / Maintainer | FYP Development Team & Antigravity Pair Programmer |
| Primary Platform | Web (Responsive Blade Dashboard + Service Worker PWA) |
| Target Deployment | Local Server Evaluation (XAMPP / PHP 8.2+ / MySQL 8.0) |
| Target Roles | Admin (Gym Owner), Trainer, Receptionist, Member |
| Revision Date | October 2026 |

---

## Implementation Status Legend

| Symbol | Meaning |
|:---:|---|
| ✅ | **Completed in Baseline (v1.5)** — Fully implemented and documented in the baseline codebase (242 automated tests recorded in repository cache). |
| 🔶 | **Partial / Scoped in v2.0** — Base functionality exists in v1.5, but extension is required to meet v2.0 multi-role or authorization rules. |
| ⬜ | **Planned for v2.0** — New module or architectural capability scheduled for implementation before February 2027. |
| 💤 | **Out of Scope (Post-FYP / Easy2Gym SaaS)** — Future multi-tenant SaaS capabilities intentionally excluded from FYP evaluation. |

---

## 1. Product Overview

WarmUp is a modern, responsive, offline-first Gym Management System engineered to digitize daily fitness club operations and provide tailored, role-specific portals for administrators, operational staff, trainers, and gym members.

The application operates as a full-featured single-gym platform with an architecture that cleanly separates administrative oversight, front-desk reception workflows, trainer-assigned member management, and member self-service. While running locally and independently for FYP evaluation, its underlying data models, security boundaries, and modular structure serve as the foundation for the future multi-tenant SaaS platform (*Easy2Gym*).

---

## 2. Product Vision & Architecture Principles

To provide an accessible, secure, and resilient gym management ecosystem that eliminates paper registers, enforces strict accountability through granular role-based permissions, ensures continuity of operations without internet connectivity, and delivers high-value fitness tools (custom workout and nutrition plans) to gym members.

### Core Architecture Principles
1. **Offline Continuity with Encrypted User Namespaces:** Staff and trainers must be able to perform essential front-desk and floor operations without network access. Pending actions are preserved in encrypted user-specific IndexedDB namespaces across logouts, tab reloads, and browser restarts.
2. **Strict Administrative Hierarchy:** The Gym Owner (Admin) retains sole authority over user onboarding. Public self-registration is strictly disallowed to prevent unverified accounts.
3. **Least-Privilege Scoping:** Trainers operate in a scoped sandbox containing only their assigned members. Receptionists are restricted to operational desks without financial expense or system configuration tampering rights.
4. **Non-Disruptive Transition:** Existing member records without linked user credentials remain fully functional as managed entities. User account linking is purely additive and optional.
5. **Mutation Deduplication & Revalidation:** Every offline action is stamped with a client-generated UUID. Synchronization strictly revalidates role permissions, input validation, and record ownership on the server before committing.

---

## 3. Goals & Objectives

- ✅ **Operations Digitization:** End-to-end management of members, attendance, plans, fee collection, and gym expenses.
- ✅ **Offline-First Resilience:** Client-side queuing via IndexedDB and Service Worker for uninterrupted daily operations.
- ✅ **Automated Financial Reporting:** Immediate computation of revenue, operational expenses, net profit, and exportable PDF/Excel audits.
- ✅ **Trilingual Interface:** Instant client-side localization across English, Urdu (اردو), and Sindhi (سنڌي).
- ⬜ **Multi-Role User Portals:** Tailored entry flows and dashboards for Admin, Trainer, Receptionist, and Member.
- ⬜ **Member Code Authentication:** Secure member access via unique member codes (e.g., `MEM-0001`) and passwords.
- ⬜ **Granular Authorization Engine:** Native Laravel Policies and Gates enforcing fixed action rules, complemented by per-user module permission configurations.
- ⬜ **Assigned Member Privacy:** Strict scoping ensuring trainers only access and record data for members under their direct assignment.
- ⬜ **Workout & Diet Prescription Engine:** Reusable master template libraries with member-specific customized assignments.
- ⬜ **Client-Side Encrypted Storage & Mutation Deduplication:** User-isolated encrypted storage in IndexedDB, resilient offline recovery, and server-side deduplication via client mutation UUIDs.
- ⬜ **Comprehensive Test Suite Maintenance:** Maintain the documented baseline of 242 tests and add complete unit/feature suites for RBAC, member codes, workout/diet modules, and role-based offline sync.

---

## 4. Target User Personas & Permissions Overview

| Role | Classification | Primary Responsibilities | Scope of Access | Status |
|---|---|---|---|:---:|
| **Admin** *(Gym Owner)* | System Owner | Full platform control, user account provisioning, financial reporting, expense tracking, settings, backup/restore. | Global across all records, reports, and configurations. | ✅ Base / 🔶 RBAC |
| **Trainer** | Staff | Track assigned member fitness progress, mark assigned attendance, create/assign workout and diet plans. | **Strictly scoped** to members explicitly assigned to this trainer (`members.trainer_id`). | ⬜ Planned |
| **Receptionist** | Staff | Member check-in/out, new member intake, fee collection, receipt printing, daily attendance monitoring. | Front-desk operational modules; no access to settings, system logs, or financial expense sheets. | ⬜ Planned |
| **Member** | Customer | View personal membership status, review payment receipts, inspect check-in logs, follow workout and diet plans. | **Strictly self-scoped** to the authenticated member's personal record. Read-only on assignments. | ⬜ Planned |

---

## 5. Entry Screen & Authentication Architecture

### 5.1 Three-Option Entry Screen ⬜
To deliver a clean, intuitive UX without confusing multi-role login forms, the public entry screen (`/login` or landing `/`) provides a branded three-card portal:

1. **Gym Owner (Admin):**  
   - Dedicated portal card leading to administrative credential authentication.
   - Requires registered administrator email and password.
   - Redirects to `/dashboard` upon successful authentication.
2. **Gym Staff (Trainer / Receptionist):**  
   - Unified operational staff login portal.
   - Authenticates staff via registered email and password.
   - Automatically detects role upon login: redirects Trainers to `/trainer/dashboard` and Receptionists to `/staff/dashboard`.
3. **Gym Member:**  
   - Dedicated member login portal.
   - Authenticates members using their unique **Member Code** (e.g., `MEM-0001`) and personal password.
   - Directs authenticated members directly to `/member/dashboard`.

### 5.2 Member Authentication Architecture ⬜
- **Identifier Strategy:** Members authenticate using a unique, system-generated **Member Code** (format: `MEM-XXXX`, where `XXXX` is a zero-padded sequential number, e.g., `MEM-0001`) plus a secure password.
- **Strictly No Code-Only Authentication:** Member codes alone NEVER grant access. A password hashed with Bcrypt is strictly mandatory.
- **Code Generation & Uniqueness:**
  - A unique `code` column (`VARCHAR(20) UNIQUE`) is added to the `members` table.
  - Codes are generated using a dedicated `member_code_sequences` counter table with atomic increments. Eloquent model hooks or `MAX(id)` / `MAX(code)` queries are strictly prohibited to prevent concurrency collisions and string lexicographical sorting overflow (e.g., `'MEM-9999' > 'MEM-10000'`).
  - When migrating existing member records, a migration script backfills sequential unique codes (`MEM-0001`, `MEM-0002`, etc.) based on `id` order and initializes the `member_code_sequences` counter table.
- **Optional Login Linking for Existing Records:**
  - Existing member records in the `members` table do NOT require a linked user account to remain operational. They remain fully manageable by the Gym Owner and staff as offline/desk entities.
  - Generating a login account for an existing member is optional and performed on-demand by the Admin.
- **Behavior for Unlinked or Disabled Records:**
  - If a member record has no linked `user_id`, login attempts with that member code are rejected with an explicit message directing the member to the front desk.
  - If a member is suspended (`status = 'suspended'`) or soft-deleted, login authentication is immediately blocked by authentication middleware.

### 5.3 Account Provisioning, Linkage & Lifecycle ⬜
- **Strictly No Public Self-Registration:** There is no public `/register` route. All user accounts (Admin, Trainer, Receptionist, Member) are provisioned exclusively by the Admin (Gym Owner) via the User Management module.
- **Account Linkage Schema:**
  - `members.user_id`: Nullable foreign key referencing `users.id`, with a `UNIQUE` constraint and `nullOnDelete()`.
  - `trainers.user_id`: Nullable foreign key referencing `users.id`, with a `UNIQUE` constraint and `nullOnDelete()`.
- **Safe Linking & Deletion Rules:**
  - **`nullOnDelete()` Guarantee:** If an Admin deletes a `users` login account, the foreign key on `members` or `trainers` is set to `NULL`. **The underlying business record, attendance history, membership history, and fee transactions are preserved completely.**
  - **Duplicate Link Prevention:** Unique database constraints ensure one user account can never be attached to multiple members or trainers, and vice versa.
  - **Soft Deletion Handling:** If a member or trainer record is soft-deleted, their linked user account is automatically deactivated (`users.is_active = false`).
  - **Unlinking:** An Admin can unlink a user account from a member or trainer record at any time without deleting either entity.
- **Credential Lifecycle:**
  - Initial passwords are set by the Admin during account creation.
  - All authenticated users retain the capability to change their password from their respective profile settings.

### 5.4 Baseline Authentication Features (Preserved from v1.5) ✅
- ✅ Session-based authentication via Laravel auth guards and secure HTTP-only cookies.
- ✅ Password hashing using Bcrypt (`Hash::make`).
- ✅ CSRF protection on all web mutation forms via `@csrf`.
- ✅ Rate-limiting on login endpoints to mitigate brute-force attempts.
- ✅ Password reset token workflow (`password_reset_tokens` table) for administrative accounts.

---

## 6. Role-Based Access Control (RBAC) & Security Rules

### 6.1 Fixed Four-Role Architecture ⬜
WarmUp implements a clean, fixed four-role model stored directly on the `users` table:
`$table->enum('role', ['admin', 'trainer', 'receptionist', 'member'])->default('member');`

To prevent unnecessary complexity and avoid schema conflicts:
- There is **no dynamic `roles` master table**.
- There is **no many-to-many `role_permissions` join table**.
- User Management is strictly restricted to the **Admin** role.

### 6.2 Permissions Architecture ⬜
WarmUp enforces a robust two-tier authorization system governed by strict precedence:
1. **Configurable Module-Level Permissions (Per User):**  
   Staff accounts have configurable module access toggles stored in a dedicated `user_permissions` table:
   `user_id`, `module_name`, `is_enabled`.  
   This allows an Admin to grant or revoke access to specific operational modules (e.g., granting a specific Receptionist read-only access to Member Reports).
2. **Explicit Fixed Action Rules (Laravel Policies & Gates):**  
   Fine-grained action security is implemented using native Laravel Policies (`MemberPolicy`, `AttendancePolicy`, `FeePolicy`, `WorkoutPlanPolicy`, `DietPlanPolicy`). These enforce hardcoded security invariants that module toggles cannot override.
3. **Permission Precedence Invariant (Deny Overrides Allow):**  
   Fixed role restrictions strictly supersede any individual user module permission toggles. A user-level toggle in `user_permissions` must NEVER grant access that the user's base role is prohibited from having. Deny takes precedence across all checks. Administrative modules (`User Management`, `Expenses`, `System Settings`, `Database Backup`, `Activity Logs`) are fixed invariants and can never be granted to non-Admin accounts under any circumstances.

### 6.3 Role Permissions Matrix

| Module / Action | Admin (Owner) | Trainer | Receptionist | Member | Enforcement Mechanism |
|---|:---:|:---:|:---:|:---:|---|
| **Executive Dashboard** | ✅ Full Access | ❌ Scoped | ❌ Scoped | ❌ Scoped | `DashboardController` role routing |
| **User & Staff Management** | ✅ Full CRUD | ❌ Denied | ❌ Denied | ❌ Denied | `AdminMiddleware` |
| **Member Management (View)** | ✅ All Members | 🔶 Assigned Only | ✅ All Members | 🔶 Self Record Only | `MemberPolicy@view` |
| **Member Management (Create/Edit)** | ✅ Full Access | ❌ Denied | ✅ Allowed | 🔶 Profile Only (Phone/Emergency) | `MemberPolicy@create`, `update` |
| **Member Management (Delete)** | ✅ Soft-Delete | ❌ Denied | ❌ Denied | ❌ Denied | `MemberPolicy@delete` |
| **Attendance (Mark / Checkout)** | ✅ All Members | 🔶 Assigned Only | ✅ All Members | ❌ Denied (Read-Only Log) | `AttendancePolicy@mark` |
| **Membership Plans (Config)** | ✅ Full CRUD | ❌ Denied | 🔶 View Only | 🔶 View Available | `PlanPolicy` |
| **Fee Collection & Receipts** | ✅ Full CRUD | ❌ Denied | 🔶 Collect & Print | 🔶 View Own Receipts | `FeePolicy@create`, `view` |
| **Expense Management** | ✅ Full CRUD | ❌ Denied | ❌ Denied | ❌ Denied | `ExpensePolicy` (Admin Gate) |
| **Workout Plans (Templates)** | ✅ Full CRUD | ✅ Full CRUD | ❌ Denied | 🔶 View Assigned | `WorkoutTemplatePolicy` |
| **Workout Plans (Assign)** | ✅ Allowed | 🔶 Assigned Only | ❌ Denied | ❌ Denied | `WorkoutPlanPolicy@assign` |
| **Diet Plans (Templates)** | ✅ Full CRUD | ✅ Full CRUD | ❌ Denied | 🔶 View Assigned | `DietTemplatePolicy` |
| **Diet Plans (Assign)** | ✅ Allowed | 🔶 Assigned Only | ❌ Denied | ❌ Denied | `DietPlanPolicy@assign` |
| **Financial & System Reports** | ✅ Full Exports | ❌ Denied | ❌ Denied | ❌ Denied | `ReportPolicy` (Admin Gate) |
| **System Settings & Branding** | ✅ Full Control | ❌ Denied | ❌ Denied | ❌ Denied | `SettingsPolicy` (Admin Gate) |
| **Database Backup & Restore** | ✅ Full Control | ❌ Denied | ❌ Denied | ❌ Denied | `BackupPolicy` (Admin Gate) |
| **Activity Log Inspection** | ✅ Full Audit | ❌ Denied | ❌ Denied | ❌ Denied | `ActivityLogPolicy` (Admin Gate) |

### 6.4 Explicit Action-Level Enforcement Rules ⬜
1. **Trainer Scoping Invariant:** Any query fetching members, attendance, or plan assignments for a user with `role = 'trainer'` MUST append `where('trainer_id', $user->trainer->id)`. Unauthorized access attempts immediately return HTTP `403 Forbidden`.
2. **Receptionist Financial Guard:** Receptionists can create `fee_payments` records and print receipts, but are strictly barred from deleting payments, altering closed transaction records, or accessing expense sheets and net profit figures.
3. **Member Self-Isolation Invariant:** Members can query only records where `member_id == Auth::user()->member->id`. Any URL manipulation attempting to access another member's profile or plans fails policy authorization.
4. **Precedence Enforcement Invariant:** Role denial checks execute prior to querying `user_permissions`. If a user's base role prohibits an action or module, authorization immediately aborts with `403 Forbidden` without evaluating permissive toggles.

---

## 7. Core Modules — Functional Requirements (Preserved & Scoped)

### 7.1 Dashboard Module ✅ / 🔶
- **Admin Dashboard (Preserved ✅):** Renders all 13 existing executive widgets:
  1. Total Members
  2. Active Members
  3. Today's New Members
  4. Active Trainers count
  5. Today's Check-ins (Attendance)
  6. Monthly Revenue (current month)
  7. Monthly Expenses (current month)
  8. Net Profit (Revenue − Expenses)
  9. Pending Fees (remaining balance on active/expiring_soon memberships)
  10. Memberships Expiring Soon (next 7 days, with quick Renew links)
  11. Membership Statistics chart (ApexCharts donut chart by plan)
  12. Recent Activities feed (last 5 from `ActivityLog`)
  13. Quick Actions panel (Add Member, Receive Fee, Attendance, Add Expense)
  - Welcome banner with owner's name and dynamic date format (`gymDateFormat()`).
  - Light/dark mode chart styling auto-detected from `data-theme`.
- **Trainer Dashboard (Planned ⬜):** Scoped metric cards: Total Assigned Members, Today's Assigned Check-ins, Pending Workout Reviews, Assigned Members Expiring Soon, Quick Action to Mark Assigned Attendance.
- **Receptionist Dashboard (Planned ⬜):** Desk metric cards: Today's Total Check-ins, Active Members on Floor, Pending Fee Collections Today, Quick Actions (Add Member, Mark Attendance, Record Fee).
- **Member Dashboard (Planned ⬜):** Personal fitness hub: Active Membership status card with real-time remaining days counter, Last Check-in timestamp, Current Assigned Workout Routine preview, Current Assigned Diet Schedule preview, Latest Fee Payment receipt link.

### 7.2 Global Search Module ✅ / 🔶
- **Baseline Capabilities (Preserved ✅):** Persistent navbar search searching Members, Trainers, and Membership Plans via `GET /api/search?q=` (`SearchController@search`). Live dropdown with up to 5 results per category.
- **v2.0 Scoping Rule (🔶):** For Trainers, search results in the member category are strictly restricted to assigned members only. For Members, global search is hidden or restricted to exercise/plan information.

### 7.3 Member Management Module ✅ / 🔶
- **Baseline Capabilities (Preserved ✅):**
  - Add, Edit, and Soft-Delete members (`SoftDeletes` trait preserves historical data).
  - Profile photo upload (stored in `storage/app/public/members/`).
  - Medical notes, emergency contact info (name + phone), physical attributes (height, weight, blood group, date of birth, address, gender).
  - Membership assignment and renewal (renewal start date defaults to day after old end date).
  - Payment recording directly from member detail page.
  - Paginated listing (15 per page) with search and status filters query persistence.
  - Four membership status states: `active`, `expired`, `expiring_soon`, `suspended`.
  - Toggle member status (`active` ↔ `suspended`). Suspended members are excluded from attendance marking.
- **v2.0 Extensions (⬜):**
  - Display and manage unique **Member Code** (`code`).
  - Optional linkage field to associate a `users` login account.
  - Explicit trainer assignment dropdown.
  - Tabbed detail view displaying Assigned Workout Plans and Assigned Diet Plans alongside Attendance and Payment tabs.

### 7.4 Trainer & Staff Management Module ✅ / 🔶
- **Baseline Capabilities (Preserved ✅):** Add, Edit, Delete trainer records; track specialization, salary, phone, email, address, gender, date of birth, joining date, profile photo upload; active/inactive status toggle; Trainer Show page with assigned member list and active count.
- **v2.0 Extensions (⬜):**
  - Link trainer record to an authenticated `users` staff account (`role = 'trainer'`).
  - Staff management console allowing Admins to manage Receptionist staff accounts.

### 7.5 Attendance Management Module ✅ / 🔶
- **Baseline Capabilities (Preserved ✅):** Mark attendance (manual, per member: present/absent), check-in and check-out recording, quick checkout action button, daily view, monthly paginated view, duplicate check-in protection (re-marking same member on same date updates existing record), automatic exclusion of suspended members.
- **v2.0 Extensions (🔶):**
  - **Trainer Filter:** Trainers view and mark attendance only for their assigned members.
  - **Receptionist Access:** Front-desk receptionists retain full access to mark attendance for all active gym members.
  - **Member View:** Members have read-only access to their personal monthly attendance calendar.

### 7.6 Membership Plan Management Module ✅
- **Baseline Capabilities (Preserved ✅):** Full CRUD via `MembershipPlanController`, plan name, price, duration in days, description, color picker, active/inactive toggle, sort order sorting.

### 7.7 Fee Management Module ✅ / 🔶
- **Baseline Capabilities (Preserved ✅):**
  - Record payments (`FeeController@pay`).
  - Overpayment protection guard: **payment cannot exceed remaining membership balance**.
  - Pending fee list (sorted: overdue first, then by remaining balance).
  - Renewal reminders for memberships expiring in the next 7 days.
  - Recent payments list and full payment history page (filterable by member, payment method, month).
  - Summary statistics widgets: Total Expected, Total Paid, Total Pending, This Month Revenue.
  - Supported payment methods: Cash, Bank Transfer, EasyPaisa, JazzCash, Card.
  - Printable fee receipt view at `/fees/receipt/{payment}`.
- **v2.0 Extensions (🔶):**
  - Receptionists can record payments and print receipts, but cannot alter or delete past payment records.
  - Members can download and view their personal payment history and digital receipts.

### 7.8 Expense Management Module ✅
- **Baseline Capabilities (Preserved ✅):** Add/Edit/Delete operational expenses, category management (seeded defaults: Rent, Utilities, Salaries, Equipment, Maintenance), inline category creation from expense forms, optional receipt image attachment, monthly expense filtering, category breakdown chart.
- **v2.0 Access Policy (✅):** Restricted exclusively to the **Admin** role. Blocked for Trainers, Receptionists, and Members.

### 7.9 Reports & Analytics Module ✅
- **Baseline Capabilities (Preserved ✅):** All 8 reports with PDF export (`barryvdh/laravel-dompdf`), Excel export (`maatwebsite/laravel-excel`), and print-optimized views:
  1. Revenue Report (PDF, Excel, Print)
  2. Attendance Report (PDF, Excel, Print)
  3. Member Report (PDF, Excel)
  4. Member Detail Print (Print)
  5. Membership Report (PDF, Excel)
  6. Trainer Report (PDF, Excel)
  7. Fee Collection Report (PDF, Excel)
  8. Expense Report (PDF, Excel)
- **v2.0 Access Policy (✅):** Restricted exclusively to the **Admin** role.

### 7.10 Notifications & Reminders Module ✅ / 🔶
- **Baseline Capabilities (Preserved ✅):** Notification bell in navbar with unread count badge (polling `/api/notifications/unread-count`), `/notifications` page with paginated listing, mark individual/all as read, auto-generated reminders (Upcoming Expiry, Pending Fee, Renewal) synced on dashboard load.
- **v2.0 Extensions (🔶):** Notifications routed by role: Trainers receive alerts regarding assigned members; Members receive personal renewal reminders.

### 7.11 Activity Log Module ✅ / 🔶
- **Baseline Capabilities (Preserved ✅):** Paginated audit trail at `/activity-logs` tracking 16 confirmed CRUD events: Member Added/Updated/Deleted, Attendance Marked/Updated/Checked Out, Fee Received, Expense Added/Updated/Deleted, Trainer Added/Updated, Settings Updated, Backup Downloaded/Restored, Offline Sync.
- **v2.0 Extensions (🔶):** Audit log entries record the actor's user ID, role, client IP address, and mutation UUID.

### 7.12 Settings & Custom Branding Module ✅
- **Baseline Capabilities (Preserved ✅):**
  - Gym name, logo upload (`storage/app/public/logos/`), owner name, contact info (email, phone, address, city, country).
  - Currency code and symbol, timezone.
  - Date format configuration (applied globally via custom macro `gymDateFormat()`).
  - Time format configuration (12-hour / 24-hour).
  - Theme mode (Light / Dark mode persisted to database, applied via CSS variables).
  - Brand colors: Primary Color (`--gym-primary`) and Secondary Color (`--gym-secondary`) with live preview.
  - Brand Split Position (controls two-color gym name rendering across sidebar and navbar).
  - Backup & Restore: Download full JSON backup; upload and restore data transactionally using `updateOrCreate` with **WarmUp app signature validation**.
- **v2.0 Access Policy (✅):** Restricted exclusively to the **Admin** role.

---

## 8. Multi-Language Support ✅

- ✅ English (`lang/en.json`)
- ✅ اردو — Urdu (`lang/ur.json`)
- ✅ سنڌي — Sindhi (`lang/sd.json`)

Language selection is persisted to settings and applied immediately across the UI. Urdu and Sindhi utilize the Noto Nastaliq Urdu font loaded conditionally from Google Fonts. Multi-language switching is fully preserved.

---

## 9. New Core Modules (v2.0 Requirements)

### 9.1 User & Staff Management Console ⬜
An administrative console for provisioning and governing system access.
- Provision user accounts with designated roles: Admin, Trainer, Receptionist, Member.
- Link accounts to existing `members` or `trainers` records via nullable unique foreign keys.
- Deactivate user access without affecting historical attendance, fee, or membership records.
- Reset user credentials or trigger password reset workflows.

### 9.2 Workout Plan Management Module ⬜
A two-tier exercise prescription engine (Online-Only during FYP evaluation):
- **Execution Scope:** Workout plan creation, assignment, and member viewing operate strictly as online modules for the February 2027 FYP. Client-side offline creation and background synchronization are deferred.
- **Tier 1: Master Templates (`workout_templates` & `workout_template_exercises`)**
  - Reusable routines created by Admins or Trainers (e.g., "Beginner 3-Day Split", "Hypertrophy Upper/Lower").
  - Configurable attributes: Routine name, fitness objective (Bulking, Cutting, Maintenance), difficulty level (Beginner, Intermediate, Advanced), notes.
  - Exercise items: Name, target muscle group (Chest, Back, Legs, Shoulders, Arms, Core), sets, reps, rest interval (seconds).
- **Tier 2: Member Assignments (`member_workout_plans` & `member_workout_exercises`)**
  - Assigned to a specific member with tailored start/end dates, active status, and custom trainer notes.
  - Trainers can assign plans **only to their assigned members**.
  - Members have read-only access to their routine via the Member Portal.

### 9.3 Diet & Nutrition Plan Management Module ⬜
A structured dietary guidance module (Online-Only during FYP evaluation):
- **Execution Scope:** Diet plan creation, assignment, and member viewing operate strictly as online modules for the February 2027 FYP. Client-side offline creation and background synchronization are deferred.
- **Tier 1: Master Templates (`diet_templates` & `diet_template_meals`)**
  - Reusable nutritional templates created by Admins or Trainers (e.g., "2500 kcal Lean Bulk", "Keto Fat Loss").
  - Configurable attributes: Plan name, goal, target calories, macronutrient split (protein g, carbs g, fat g).
  - Meal schedules: Meal name (Breakfast, Lunch, Pre-Workout, Dinner), timing, food items, portion size, calories, macros.
- **Tier 2: Member Assignments (`member_diet_plans` & `member_diet_meals`)**
  - Assigned to an individual member with custom dietary adjustments and allergy notes.
  - Trainers can assign plans **only to their assigned members**.
  - Members have read-only access to their daily meal plan via the Member Portal.

### 9.4 Member Portal Module ⬜
A dedicated self-service hub for enrolled members:
- Active membership status card with real-time remaining days countdown.
- Personal attendance calendar and monthly attendance percentage.
- Digital fee payment history and printable/downloadable payment receipts.
- Interactive view of assigned Workout Plan with exercise details, sets, and reps.
- Daily Diet Schedule displaying meals, food portions, and calorie goals.
- Profile view allowing updates to emergency contact information and password.

---

## 10. Offline-First Architecture & Security Enhancements

### 10.1 Baseline Capabilities Preserved ✅
WarmUp's proven offline architecture from v1.5 is preserved:
- Client-side storage via IndexedDB (`warmup_offline` DB, `action_queue` store).
- Service Worker caching App Shell (`public/sw.js`, `public/offline.html`).
- Background synchronization upon network reconnect (`POST /api/offline/sync`, batch size 50).
- Last-Write-Wins (LWW) conflict resolution using `client_updated_at` timestamps.
- Persistent sync status pill (Online, Offline, Syncing..., Synced Successfully).
- **File Upload Offline Constraints:** Profile photo uploads and gym logo uploads require an active internet connection; file uploads are excluded from offline queuing by design.
- **JavaScript Helper API:** Exported queue helpers in `public/js/offline.js` (`queueAction`, `queueMemberDelete`, `queueTrainerCreate`, `queueTrainerUpdate`, `queueSettingsUpdate`, `queueMemberUpdate`).

### 10.2 Client-Side Mutation Deduplication & Idempotency ⬜
To eliminate duplicate records caused by network disconnects during batch sync:
1. **Client-Generated Mutation UUIDs:** Every queued offline mutation MUST generate a unique UUIDv4 (`mutation_id`) upon creation in IndexedDB.
2. **Server-Side Deduplication Table (`synced_mutations`):**
   - The server records processed `mutation_id`, `user_id`, and `created_at`.
   - If an action with an already-processed `mutation_id` is re-received during retry, the server skips re-execution and returns the cached successful result.
3. **True Idempotency for Critical Entities:**
   - `member_create`: Deduplicated via `mutation_id`.
   - `fee_payment_create`: Deduplicated via `mutation_id` to strictly prevent double payments.
   - `expense_create`: Deduplicated via `mutation_id`.

### 10.3 Role-Aware Offline Sync & Server-Side Revalidation ⬜
Every action submitted in the offline sync payload is individually authorized and validated on the server:

| Offline Action | Admin | Trainer | Receptionist | Server-Side Sync Revalidation Rules |
|---|:---:|:---:|:---:|---|
| `member_create` | ✅ | ❌ | ✅ | Revalidates staff role and input schema; checks `mutation_id`. |
| `member_update` | ✅ | ❌ | ✅ | LWW check (`client_updated_at >= updated_at`); verifies staff permissions. |
| `member_delete` | ✅ | ❌ | ❌ | Strictly rejected if initiated by non-Admin. |
| `attendance_create` | ✅ | ✅ | ✅ | **If Trainer:** Strictly enforces current `member.trainer_id == auth_user.trainer_id`. Idempotent on `(member_id, date)`. |
| `fee_payment_create`| ✅ | ❌ | ✅ | Verifies staff collection permission; enforces overpayment guard; checks `mutation_id`. |
| `expense_create` | ✅ | ❌ | ❌ | Strictly rejected if initiated by non-Admin; checks `mutation_id`. |
| `trainer_create` | ✅ | ❌ | ❌ | Admin only. |
| `trainer_update` | ✅ | ❌ | ❌ | Admin only. |
| `settings_update` | ✅ | ❌ | ❌ | Admin only. |
| `workout_assign` | 💤 Deferred | 💤 Deferred | ❌ | Deferred post-FYP; online-only during FYP evaluation. |
| `diet_assign` | 💤 Deferred | 💤 Deferred | ❌ | Deferred post-FYP; online-only during FYP evaluation. |

### 10.4 Stale Offline Trainer Action Resolution ⬜
- **Revalidation at Sync:** When a trainer syncs offline attendance, the server verifies current record assignment: `member.trainer_id == auth_user.trainer_id`.
- **Handling Reassigned Members:** If a member was reassigned to another trainer on the server while the offline trainer was disconnected:
  - The server **rejects the stale action** with a structured conflict response:
    `{ status: 'conflict', code: 'STALE_TRAINER_ASSIGNMENT', message: 'Member assignment has changed on server.' }`
  - The action is **retained in the trainer's local queue** marked as conflict.
  - The action is NOT silently executed under outdated authorization, and is NOT silently destroyed.
  - The client UI presents an alert notifying the trainer of the reassignment so they can review or dismiss the item.

### 10.5 Offline Data Encryption & Key Lifecycle ⬜
> [!IMPORTANT]
> **Technical Key Management Requirement:** Per-user encryption key isolation is adopted as the selected architectural direction. However, final implementation is strictly conditional on a successful Proof of Concept (POC) validating key persistence across browser restarts, offline decryption without network, and isolation preventing cross-user queue decryption on shared workstations.

#### Security & Lifecycle Requirements:
1. **At-Rest Encryption in IndexedDB:** All sensitive offline data (queued actions in `action_queue`, cached member lists) must be encrypted using **AES-GCM-256** via the native **Web Crypto API** (`window.crypto.subtle`).
2. **Per-User Key Isolation & Storage Namespaces:**
   - IndexedDB object stores must be partitioned or namespaced by `user_id` (e.g., `user_queue_{user_id}`).
   - Cryptographic keys are isolated per user (e.g., password-derived key wrapping or per-user non-extractable CryptoKey storage). User B cannot decrypt or inspect the offline queue of User A on the same shared workstation.
3. **Authorized Offline Recovery (POC Conditional):**
   - The cryptographic key must survive page refreshes, tab closures, and browser restarts during network outages.
   - Key persistence across browser restarts and offline recovery must be validated via a formal technical POC prior to code implementation.
4. **Queue Preservation on Logout:**
   - **Un-synced actions are NEVER automatically purged on logout or user switching.**
   - If a receptionist or trainer logs out during an internet outage, their pending queue remains safely encrypted in their user namespace.
   - When the user logs back in or network connectivity returns, the pending queue is decrypted and synchronized.
5. **Explicit Discard Safety:**
   - If a user manually chooses to clear un-synced actions, the UI must require a two-step explicit confirmation dialog clearly warning of irreversible data loss.

---

## 11. Database Schema Evolution & Planned Migrations

To support v2.0 capabilities while preserving the existing 15 migrations, the schema additions are specified below:

```
Existing Tables (Preserved):
  users, gym_settings, membership_plans, trainers, members,
  member_memberships, attendances, fee_payments, expense_categories,
  expenses, notifications, activity_logs, password_reset_tokens

Planned v2.0 Schema Migrations:
  1. Add `code` (VARCHAR(20) UNIQUE) to `members` table (with sequential backfill)
  2. Add `user_id` (nullable FK, UNIQUE) to `members` table (nullOnDelete)
  3. Add `user_id` (nullable FK, UNIQUE) to `trainers` table (nullOnDelete)
  4. Add `role` enum ('admin', 'trainer', 'receptionist', 'member') to `users` table
  5. Create `user_permissions` table (user_id FK, module_name, is_enabled)
  6. Create `synced_mutations` table (mutation_id UUID primary, user_id, action_type, timestamps)
  7. Create `workout_templates` table
  8. Create `workout_template_exercises` table
  9. Create `member_workout_plans` table
  10. Create `member_workout_exercises` table
  11. Create `diet_templates` table
  12. Create `diet_template_meals` table
  13. Create `member_diet_plans` table
  14. Create `member_diet_meals` table
  15. Create `member_code_sequences` table (atomic counter table for race-condition-safe sequential member codes)
```

### Foreign-Key Deletion & SQLite Compatibility Rules:
1. **Non-Cascading Account Deletions:**
   - `members.user_id` constraint: `$table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();`
   - `trainers.user_id` constraint: `$table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();`
   - Deleting a login account sets the foreign key to NULL, fully protecting member records, trainer records, attendance, memberships, and payments from cascade deletion.
2. **SQLite In-Memory Test Compatibility:**
   - Tests run using `<env name="DB_CONNECTION" value="sqlite"/>` and `<env name="DB_DATABASE" value=":memory:"/>`.
   - Migration `down()` methods must drop foreign keys before dropping columns, wrapping constraint operations defensively to ensure clean rollbacks under SQLite.
3. **Deferred SQL File:**
   - `database/warmup.sql` remains deferred until all new migrations are executed, verified, and tested against both MySQL and SQLite.

---

## 12. Technology Stack & Package Manifest (Restored & Updated)

### Frontend ✅
- HTML5 (Laravel Blade templates)
- Tailwind CSS (via Vite build pipeline, `resources/css/app.css`)
- JavaScript (ES6 modules via Vite, `resources/js/app.js` + `public/js/offline.js`)
- Lucide Icons (loaded via CDN)
- ApexCharts (loaded via CDN — used on Dashboard)
- SweetAlert2 (included in `app.js` for confirmation dialogs)
- IndexedDB (client-side offline storage)
- Web Crypto API (`window.crypto.subtle` for AES-GCM-256 encryption)
- Service Worker (`public/sw.js` and `public/offline.html`)

### Backend ✅
- PHP 8.2+
- Laravel Framework (v11.x)
- Laravel Blade templating engine
- Laravel Middleware (auth, guest, locale, role gates)
- RESTful APIs (`/api/search`, `/api/offline/sync`, `/api/notifications/unread-count`)
- Eloquent ORM (parameterized queries, relationships, soft deletes)
- `barryvdh/laravel-dompdf` (PDF report exports)
- `maatwebsite/laravel-excel` (Excel spreadsheet exports)

### Database ✅
- MySQL 8.0 (production / local XAMPP runtime)
- SQLite in-memory (automated testing runtime via `phpunit.xml`)

### Development & Evaluation Environment ✅
- XAMPP (Apache, MySQL)
- Composer & NPM / Vite
- Git & GitHub

---

## 13. UI / UX & Responsive Design Requirements (Restored from v1.5)

### Design Tokens & Styling ✅
- **Theme Modes:** Light Theme (default) and Dark Theme (persisted to database, applied via CSS variables and `data-theme`).
- **Brand Colors:** Configurable Primary Color (`--gym-primary`, default `#22C55E`) and Secondary Color (`--gym-secondary`, default `#16A34A`) with live preview in Settings.
- **Typography:** Inter font for Latin scripts; Noto Nastaliq Urdu font loaded conditionally for Urdu (`ur`) and Sindhi (`sd`).
- **Layout:** Persistent dark sidebar (`#0F172A`), persistent top navigation bar with global search, notifications bell, and network sync pill.
- **Gym Name Rendering:** Two-color gym name display with configurable split position.

### Responsive Breakpoints Status (Restored from v1.5 § 13) 🔶
WarmUp is engineered with desktop and laptop workstations as its primary evaluation target:
- ✅ **Desktop (≥ 1280px):** Fully supported and verified.
- ✅ **Laptop (1024px – 1279px):** Fully supported and verified.
- 🔶 **Tablet (768px – 1023px):** Basic responsiveness via Tailwind grid breakpoints; complex data tables scroll horizontally.
- 🔶 **Mobile (< 768px):** Functional; collapsible sidebar hamburger toggle exists; stacked forms collapse to single column; full mobile UX is secondary for FYP evaluation.

---

## 14. Security & Compliance Requirements

- ✅ **Password Hashing:** Bcrypt encryption on all passwords via `Hash::make()`.
- ✅ **CSRF Protection:** `@csrf` enforced on all POST/PUT/DELETE forms.
- ✅ **SQL Injection Safeguard:** Parameterized queries via Eloquent ORM.
- ✅ **XSS Protection:** Blade auto-escaping `{{ }}` on all user inputs.
- ⬜ **Role-Based Authorization:** Native Laravel Policies and Gates applied on all resource routes.
- ⬜ **Assigned Record Scoping:** Controller and Policy-level tenant check enforcing trainer-member assignment.
- ⬜ **Encrypted Offline Queue:** AES-GCM-256 encryption on client-side IndexedDB stores.
- ⬜ **Deduplicated Offline Sync:** Client mutation UUIDs preventing duplicate database insertions.
- ⬜ **Non-Cascading Deletions:** `nullOnDelete()` foreign keys protecting business records.
- ⬜ **Audit Trail Attribution:** Comprehensive `ActivityLog` entries tracking user ID, role, action, and target entity.

---

## 15. Testing & Quality Assurance Roadmap

### 15.1 Baseline Test Suite Status (Historical Inventory)
PRD v1.5 documents a historical baseline of **242 automated tests and 699 assertions with 0 failures**, pending execution in the current environment.

The 16 test files comprising the baseline suite are:
1. `AttendanceTest.php` — Attendance Management (daily/monthly, check-in/out, duplicate protection)
2. `AuthTest.php` — Authentication (login, logout, password reset, rate-limiting)
3. `BackupRestoreTest.php` — Backup export and transactional restore with signature check
4. `DashboardTest.php` — Executive metrics, widgets, and soft-delete exclusions
5. `ExpenseTest.php` — Expense CRUD, categories, and monthly summaries
6. `FeeTest.php` — Payment recording, overpayment guard, and pending fee calculations
7. `LocaleTest.php` — Trilingual switching (English, Urdu, Sindhi) and RTL styles
8. `MemberTest.php` — Member CRUD, soft deletes, renewals, and payment attachments
9. `MembershipPlanTest.php` — Plan CRUD, sorting, and deletion guards
10. `NotificationTest.php` — Auto-generated reminders and read state toggles
11. `OfflineSyncTest.php` — 37 tests covering baseline sync actions, LWW, and conflict handling
12. `ProfileTest.php` — Profile update and password change flows
13. `ReportTest.php` — Report filtering, PDF exports, and Excel exports
14. `SearchTest.php` — Global live search across entities
15. `SettingsTest.php` — Gym settings, branding colors, and split position
16. `TrainerTest.php` — Trainer CRUD, photo upload, and assigned member counter

### 15.2 Planned v2.0 Test Suites ⬜
- `MemberCodeAuthTest.php`: Validate login via Member Code + password; reject code-only attempts; reject suspended members.
- `RoleAccessTest.php`: Verify Trainer scoping, Receptionist financial guards, Member self-isolation, and Admin unrestricted access.
- `WorkoutPlanTest.php`: Master template CRUD, member assignment, trainer scoping checks.
- `DietPlanTest.php`: Master template CRUD, member assignment, macro target calculations.
- `OfflineRoleSyncTest.php`: Revalidation of roles, rejection of stale trainer assignments, and mutation UUID deduplication.
- `MigrationRollbackTest.php`: Validate clean migration execution and rollback under SQLite in-memory runner.

---

## 16. Quantified Success Metrics (Restored from v1.5 § 20)

- ✅ **Member Onboarding Speed:** Admin or Receptionist can complete member registration and plan assignment in under 2 minutes.
- ✅ **Attendance Speed:** Attendance marking for a 20-member class completes in under 1 minute.
- ✅ **Offline Sync Integrity:** 100% of valid queued offline actions sync accurately upon reconnect; 0 duplicate records created.
- ✅ **Report Generation:** PDF and Excel exports generate in under 3 seconds.
- ✅ **Search Latency:** Global search returns categorized results in under 500ms.
- ⬜ **Authentication Gate Reliability:** 100% of unauthorized cross-role or cross-trainer requests return HTTP `403 Forbidden`.
- ⬜ **Offline Recovery:** 100% of un-synced actions survive page refresh, browser restart, and user logout during network outages.

---

## 17. Assumptions & Technical Constraints (Restored & Updated)

1. **Local Evaluation Runtime:** Evaluated locally via XAMPP (Apache, PHP 8.2+, MySQL 8.0). Cloud deployment is out of scope.
2. **Browser Standards:** Client browsers must support modern ECMAScript, Service Workers, IndexedDB, and the Web Crypto API (`window.crypto.subtle`).
3. **Admin Provisioning:** The Gym Owner is the root administrator responsible for provisioning staff and member credentials.
4. **Member Linkage Independence:** Gyms may manage member records as offline entities without generating web logins.
5. **No Paid Dependencies:** All notifications, reports, and calculations execute entirely in-house without paid external API subscriptions.
6. **File Upload Constraint:** Member photos and gym logos cannot be uploaded while offline (file uploads excluded from offline sync by design).

---

## 18. Future Roadmap & Excluded Scope (Post-FYP SaaS) 💤

The following enterprise capabilities remain intentionally excluded from the FYP evaluation:
- Full Multi-Tenant Architecture (`gym_id` database scoping across distinct gym tenants).
- Live Payment Gateways (Stripe, JazzCash live gateway, EasyPaisa live gateway).
- Automated SMS / WhatsApp messaging gateways (requires paid third-party APIs).
- Native mobile applications (iOS / Android).
- Cloud deployment and managed hosting infrastructure.
- Automatic scheduled remote cloud backups.
- Offline creation and synchronization of workout and diet plan assignments (online-only during FYP evaluation; deferred to post-FYP SaaS).

---

## Appendix: Version 2.0 Changelog

| Version | Scope / Change Description | Impacted Sections |
|---|---|---|
| **2.0-DRAFT** | **Multi-Role Expansion:** Transitioned from single-user (Owner) to 4 fixed roles (Admin, Trainer, Receptionist, Member). | § 4, § 5, § 6 |
| **2.0-DRAFT** | **Three-Option Entry Portal:** Added dedicated entry cards for Owner, Staff, and Member. Prohibited public self-registration. | § 5.1 |
| **2.0-DRAFT** | **Member Code Authentication:** Implemented unique generated member codes (`MEM-XXXX`) + mandatory password. Maintained optional account linking for existing records. | § 5.2, § 5.3 |
| **2.0-DRAFT** | **Fixed RBAC Engine:** Replaced dynamic roles table with fixed `users.role` enum and per-user module permissions backed by Laravel Policies and Gates. | § 6.1, § 6.2 |
| **2.0-DRAFT** | **Trainer Scoping Invariant:** Enforced strict assignment scoping limiting Trainers to their assigned members across all views and sync actions. | § 6.4, § 7, § 10.3 |
| **2.0-DRAFT** | **Workout & Diet Plan Modules:** Added functional specifications for reusable templates and member assignments. | § 9.2, § 9.3 |
| **2.0-DRAFT** | **Member Portal:** Added functional requirements for member dashboard, receipts, routines, and meal plans. | § 9.4 |
| **2.0-DRAFT** | **Encrypted Offline Namespaces:** Specified user-isolated encrypted IndexedDB namespaces, surviving refresh/restart, with no automated queue purge on logout. | § 10.5 |
| **2.0-DRAFT** | **Mutation Deduplication:** Added client mutation UUIDs (`mutation_id`) and server deduplication to prevent duplicate insertions on sync retry. | § 10.2 |
| **2.0-DRAFT** | **Stale Trainer Action Resolution:** Revalidates trainer assignment at sync, returning structured conflict responses for reassigned members without data loss. | § 10.4 |
| **2.0-DRAFT** | **Safe Account Linkages:** Specified nullable, unique foreign keys (`members.user_id`, `trainers.user_id`) with `nullOnDelete()` to prevent cascade deletion of business records. | § 5.3, § 11 |
| **2.0-DRAFT** | **Restored v1.5 Requirements:** Restored Technology Stack (§ 12), Responsive Design status (§ 13), Success Metrics (§ 16), 16 Test Files inventory (§ 15.1), and business logic rules. | § 7, § 12, § 13, § 15, § 16 |
| **2.0-DRAFT** | **Corrected Test Baseline Claim:** Clarified that the 242 tests represent a historical documented baseline from v1.5 cache, pending live execution. | Document Control, § 15.1 |

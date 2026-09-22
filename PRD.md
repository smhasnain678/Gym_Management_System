WarmUp
Gym Management Dashboard
Product Requirements Document (PRD)
Version 1.5  •  Final Year Project  •  Synchronized Edition
A modern, offline-first, SaaS-ready gym management platform — built to digitize gym operations today and scale into the Easy2Gym multi-tenant SaaS platform tomorrow.

---

## Implementation Status Legend

| Symbol | Meaning |
|--------|---------|
| ✅ | **Completed** — Fully implemented and verified in the current codebase |
| 🔶 | **Partial / In Progress** — Partially implemented; some sub-features missing |
| ⬜ | **Planned / Remaining** — Not yet implemented; planned for future work |

---

## Document Control

| Field | Detail |
|-------|--------|
| Product Name | WarmUp |
| Document Type | Product Requirements Document (PRD) |
| Version | 1.5 |
| Status | Synchronized — Final Year Project (FYP) |
| Parent Brand (Future SaaS) | Easy2Gym |
| Primary Platform | Web (Responsive Dashboard) |
| Owner | Gym Owner (single-tenant, Version 1) |
| Last Synchronized | September 2026 |

---

## 1  Product Overview

WarmUp is a modern, responsive Gym Management Dashboard designed for gym owners to digitize and manage their daily operations efficiently. The system enables gym owners to manage members, trainers, attendance, memberships, fee collection, expenses, reports, and gym settings from a single, unified dashboard.

WarmUp is designed as an offline-first, SaaS-ready application. It is built to work independently for a single gym in its first version, and is architected so it can later be transformed into a multi-tenant SaaS platform under the Easy2Gym brand without a ground-up rewrite.

---

## 2  Product Vision

To build a modern, affordable, and user-friendly Gym Management System that simplifies day-to-day gym operations for small and independent gym owners, while providing a scalable technical and architectural foundation for a future white-label SaaS platform (Easy2Gym).

---

## 3  Goals & Objectives

- ✅ Digitize gym operations end-to-end, replacing manual registers and spreadsheets.
- ✅ Reduce manual paperwork for member records, attendance, and fee tracking.
- ✅ Simplify fee management with clear visibility into paid, pending, and due amounts.
- ✅ Track expenses and calculate net profit for each month.
- ✅ Improve attendance tracking accuracy with daily and monthly views.
- ✅ Generate reports automatically, exportable to PDF and Excel.
- ✅ Support multiple languages for wider regional accessibility (English, Urdu, Sindhi).
- ✅ Work even without internet, using an offline-first architecture with background sync.
- ✅ Log all important activities for auditing and accountability.
- ✅ Prepare the system's data model and architecture for future multi-tenant SaaS deployment.

---

## 4  Target Users

**Primary User**
- Gym Owner — full access to the dashboard and all management modules.

**Managed Entities (No Login Access)**
- Members — records managed by the Gym Owner; no self-service login in V1.
- Trainers — records managed by the Gym Owner; no self-service login in V1.
- Staff — records managed by the Gym Owner; no self-service login in V1.

> Note: Version 1 supports authentication for the Gym Owner role only. Member, Trainer, and Receptionist logins are planned for future versions.

---

## 5  User Authentication ✅

Version 1 implements a single-role authentication system scoped to the Gym Owner.

| Feature | Description | Status |
|---------|-------------|--------|
| Login | Gym Owner signs in with registered email and password. | ✅ Implemented (`LoginController`) |
| Forgot Password | Owner can request a password reset flow if credentials are lost. | ✅ Implemented (`ForgotPasswordController`, `password_reset_tokens` table) |
| Change Password | Owner can update their password from within the app (requires current password; logs out after change). | ✅ Implemented (`ProfileController@updatePassword`) |
| Profile Management | Owner can view and edit their name, email, and phone from the profile page. | ✅ Implemented (`ProfileController@updateProfile`, `profile/edit` view) |

No member login. No trainer login. No receptionist login. These roles will be introduced in future versions with role-based authorization.

---

## 6  Core Modules — Functional Requirements

### 6.1  Dashboard ✅

All widgets are implemented and actively rendered from the `DashboardController`.

**Overview Widgets — All Implemented**
- ✅ Total Members
- ✅ Active Members
- ✅ Today's New Members
- ✅ Active Trainers count
- ✅ Today's Check-ins (Attendance)
- ✅ Monthly Revenue (current month)
- ✅ Monthly Expenses (current month)
- ✅ Net Profit (Revenue − Expenses)
- ✅ Pending Fees (remaining balance on active/expiring_soon memberships)
- ✅ Memberships Expiring Soon (next 7 days, with Renew shortcut links)
- ✅ Membership Statistics chart (ApexCharts donut chart by plan)
- ✅ Recent Activities feed (last 5 from ActivityLog)
- ✅ Quick Actions panel (Add Member, Receive Fee, Attendance, Add Expense)

**Additional Details**
- Welcome banner with owner's name and current date (using configurable date format).
- Light/dark mode chart styling auto-detected from `data-theme` attribute.

---

### 6.1.1  Global Search ✅

The Gym Owner can quickly search across the entire system from a persistent search bar in the top navigation bar.

- ✅ Search Members (by name, phone, or ID)
- ✅ Search Trainers (by name, phone, or specialization)
- ✅ Search Membership Plans (by name)
- ✅ Results shown in a live dropdown (up to 5 results per category)
- ✅ API endpoint: `GET /api/search?q=` (`SearchController`)
- ✅ Rendered in top nav (`app.blade.php`) — visible on all authenticated pages

---

### 6.2  Member Management ✅

All core member management capabilities are fully implemented.

**Capabilities**
- ✅ Add Member (with optional immediate membership assignment and initial payment)
- ✅ Edit Member
- ✅ Delete Member (soft-delete via `SoftDeletes` trait; historical data preserved)
- ✅ Member Profile view (photo, contact info, membership details, attendance history)
- ✅ Upload Member Photo (stored in `storage/app/public/members/`)
- ✅ View Member Check-in History (last 10 attendance records on member detail page)
- ✅ Membership Details (plan, start date, end date, status, paid/remaining amounts)
- ✅ Emergency Contact information (name + phone)
- ✅ Medical Notes
- ✅ Additional fields: height, weight, blood group, date of birth, address, gender
- ✅ Search Members (by name, email, phone)
- ✅ Filter Members (by status: active/expired/expiring_soon/suspended; by gender)
- ✅ Toggle Member Status (active ↔ suspended)
- ✅ Assign Membership (new plan from member detail page)
- ✅ Renew Membership (creates a new membership, marks old one as expired/renewed)
- ✅ Record Payment (against existing membership from member detail page)
- ✅ Paginated listing (15 per page) with search/filter query string persistence

**Membership Status Values**
- ✅ Active
- ✅ Expired
- ✅ Expiring Soon
- ✅ Suspended

---

### 6.3  Trainer / Staff Management ✅

All trainer management capabilities are fully implemented.

**Capabilities**
- ✅ Add Trainer
- ✅ Edit Trainer
- ✅ Delete Trainer
- ✅ Trainer Details (bio, specialization, phone, email, address, gender, date of birth)
- ✅ Salary Information
- ✅ Joining Date
- ✅ Assigned Members count
- ✅ Active / Inactive Status toggle
- ✅ Trainer Show page (profile + member list)
- ✅ Profile Photo upload for trainers (stored in public storage)
- ✅ Paginated listing with search and status filter

---

### 6.4  Attendance Management ✅

Full attendance management with both daily and monthly views is implemented.

**Capabilities**
- ✅ Mark Attendance (manual, per member — present/absent)
- ✅ Check-in Time and Check-out Time recording
- ✅ Checkout Action (quick checkout for present members)
- ✅ Daily Attendance view (default; shows all non-suspended members for a date)
- ✅ Monthly Attendance view (paginated, filterable by status and member search)
- ✅ Attendance History (Member Check-in history on member detail page)
- ✅ Present / Absent status tracking
- ✅ Attendance Statistics (total members, present count, absent count, percentage)
- ✅ Duplicate-protection: re-marking the same member on the same date updates existing record
- ✅ Suspended members excluded from attendance views

**Future Scope**
- ⬜ QR-code based Attendance (scan-to-check-in)

---

### 6.5  Membership Plan Management ✅

- ✅ Create Membership Plans (name, price, duration in days, description, color, sort order)
- ✅ Edit Membership Plans
- ✅ Delete Membership Plans
- ✅ Toggle Plan Active/Inactive Status
- ✅ Plan color customization (used in dashboard chart)
- ✅ Plans ordered by `sort_order`, then alphabetically
- ✅ Full CRUD via `MembershipPlanController` with resource routes

---

### 6.6  Fee Management ✅

**Capabilities**
- ✅ Receive Fee / Record Payment (from Fee Management page — `FeeController@pay`)
- ✅ Total Fee / Paid Amount / Remaining Amount tracking per membership
- ✅ Pending Fee List (sorted: overdue first, then by remaining amount)
- ✅ Renewal Reminders (memberships expiring in next 7 days shown on Fee page)
- ✅ Recent Payments list (last 10 payments)
- ✅ Full Payment History page (filterable by member name, payment method, month)
- ✅ Printable Fee Receipt (`fees/receipt/{payment}` view)
- ✅ Overpayment guard (payment cannot exceed remaining balance)
- ✅ Payment methods supported: Cash, Bank Transfer, EasyPaisa, JazzCash, Card
- ✅ Monthly Revenue summary widget on Fee Management dashboard
- ✅ Summary statistics: Total Expected, Total Paid, Total Pending, This Month Revenue

**Future Scope**
- ⬜ Stripe integration
- ⬜ EasyPaisa integration (live gateway — currently supported as a recorded payment method label)
- ⬜ JazzCash integration (live gateway — currently supported as a recorded payment method label)
- ⬜ Debit/Credit Card payments (live gateway)

---

### 6.7  Expense Management ✅

A dedicated module for tracking gym operational expenses, enabling the Gym Owner to monitor costs and calculate net profit.

**Capabilities**
- ✅ Add Expense (title, amount, date, category, paid_to, notes, optional receipt image upload)
- ✅ Edit Expense
- ✅ Delete Expense (removes receipt image from storage)
- ✅ Expense Categories (CRUD; seeded defaults: Rent, Utilities, Salaries, Equipment, Maintenance, etc.)
- ✅ Create new category inline from Add/Edit Expense forms
- ✅ Monthly Expense Tracking (default view filtered by current month)
- ✅ Category Breakdown chart/summary per month
- ✅ Search Expenses (by title or paid_to)
- ✅ Filter by category
- ✅ Expense History (paginated, 15 per page)
- ✅ Expense Reports — see Section 6.8

---

### 6.8  Reports ✅

All reports are fully implemented with PDF export, Excel export, and print views where applicable.

**Available Reports**
| Report | Page | PDF Export | Excel Export | Print View |
|--------|------|-----------|-------------|-----------|
| Revenue Report | ✅ | ✅ | ✅ | ✅ |
| Attendance Report | ✅ | ✅ | ✅ | ✅ |
| Member Report | ✅ | ✅ | ✅ | — |
| Member Detail Print | ✅ | — | — | ✅ |
| Membership Report | ✅ | ✅ | ✅ | — |
| Trainer Report | ✅ | ✅ | ✅ | — |
| Fee Collection Report | ✅ | ✅ | ✅ | — |
| Expense Report | ✅ | ✅ | ✅ | — |

**Export Implementation**
- PDF: via `barryvdh/laravel-dompdf` (`Pdf::loadView(...)`)
- Excel: via `maatwebsite/laravel-excel` with dedicated `*Export` classes

**Print Support**
- ✅ Fee Receipts (printable receipt page at `/fees/receipt/{payment}`)
- ✅ Member Details (printable at `/reports/members/{member}/print`)
- ✅ Attendance Reports (print view at `/reports/attendance?print=1`)
- ✅ Revenue Reports (print view at `/reports/revenue?print=1`)

**Report Filters**
- Revenue: date range, month
- Attendance: date range, month, status, specific member
- Members: search, status, trainer, active plan
- Memberships: plan, status, date range
- Trainers: search, status
- Fees: date range, month, payment method
- Expenses: date range, month, expense category

---

### 6.9  Notifications & Reminders ✅

**System Notifications (In-App)**
- ✅ Notification Bell in top navigation with unread count badge (auto-polls via `/api/notifications/unread-count`)
- ✅ Notifications page (`/notifications`) with paginated listing
- ✅ Mark individual notification as read
- ✅ Mark all notifications as read
- ✅ Notification types generated automatically: Upcoming Membership Expiry, Pending Fee Reminder, Renewal Reminder (synced via `NotificationService@syncDashboardNotifications` on each dashboard load)

**Browser / Web Push Notifications**
- ⬜ Browser Web Push notifications (intentionally omitted per user feedback during Phase 12)

**Future Scope**
- ⬜ WhatsApp notifications
- ⬜ Email notifications
- ⬜ SMS notifications

---

### 6.10  Activity Log ✅

The system records all important user activities for tracking and auditing purposes. The Gym Owner can review the full activity log from the sidebar.

**Logged Activities (all confirmed in code)**
- ✅ Member Added
- ✅ Member Updated
- ✅ Member Deleted
- ✅ Attendance Marked
- ✅ Attendance Updated
- ✅ Attendance Checked Out
- ✅ Fee Received
- ✅ Expense Added
- ✅ Expense Updated
- ✅ Expense Deleted
- ✅ Trainer Added
- ✅ Trainer Updated
- ✅ Settings Updated
- ✅ Backup Downloaded
- ✅ Backup Restored
- ✅ Offline Sync (batch sync events with success/failed/conflict counts)

**Activity Log Page**
- ✅ Paginated listing at `/activity-logs`
- ✅ Displays user name, action, description, timestamp
- ✅ Linked from sidebar under Account section and from Dashboard Recent Activities feed

---

### 6.11  Settings ✅

**Configurable Options**
- ✅ Gym Name
- ✅ Gym Logo (upload, stored in `storage/app/public/logos/`)
- ✅ Owner Name
- ✅ Contact Details (email, phone, address, city, country)
- ✅ Currency (code and symbol)
- ✅ Timezone
- ✅ Language (English / Urdu / Sindhi — live switching applied to all pages)
- ✅ Theme (Light / Dark — persisted to database, applied via CSS custom properties)
- ✅ Date Format (configurable; used throughout the app via custom `gymDateFormat()` macro)
- ✅ Time Format (12h / 24h)
- ✅ Backup & Restore (see 6.11.1)
- ✅ Brand Colors — Primary Color & Secondary Color (color pickers, persisted to DB)
- ✅ Brand Split Position (controls two-color gym name rendering in sidebar and navbar)

**Additional Details**
- All settings stored in `gym_settings` table via `GymSetting` model.
- Settings are injected globally into all views via middleware/view composer.
- Settings can be updated offline (via `settings_update` offline sync action with Last-Write-Wins conflict resolution).

---

### 6.11.1  Backup & Restore ✅

- ✅ Download Backup — exports all gym data as a JSON file (`warmup_backup_YYYY-MM-DD_HHmmss.json`), including: gym settings, membership plans, trainers, members (with memberships, fee payments, attendances), expense categories, expenses.
- ✅ Restore Backup — upload a `.json` backup file; data is restored transactionally using `updateOrCreate` for all entities. Validates WarmUp app signature before restore.
- ✅ Both actions logged in Activity Log.

---

## 7  Multi-language Support ✅

- ✅ English (`lang/en.json`)
- ✅ اردو — Urdu (`lang/ur.json`)
- ✅ سنڌي — Sindhi (`lang/sd.json`)

Language can be changed from Settings. The selected language is applied immediately across the entire UI without restarting the application. Urdu and Sindhi use the Noto Nastaliq Urdu font (loaded conditionally from Google Fonts). Language is also validated in the offline `settings_update` sync action (`en`, `ur`, `sd` values supported).

---

## 8  White Label Readiness (Future — Easy2Gym SaaS)

The following branding features are partially implemented for the single-gym case and provide a foundation for future white-label SaaS:

- ✅ Gym Name — configurable per gym owner (Settings)
- ✅ Gym Logo — configurable per gym owner (Settings)
- ✅ Primary Brand Color — configurable per gym owner (Settings, with live preview)
- ✅ Secondary Brand Color — configurable (two-tone gym name support, with live preview)
- ✅ Custom Branding applied across sidebar, navbar, and login screen

**Future (multi-tenant only)**
- ⬜ Subscription Plans per tenant
- ⬜ Full Multi-Tenant Architecture supporting multiple gyms on one platform

---

## 9  Offline-First Support ✅

WarmUp works even if the internet connection is lost. This is a core architectural requirement, fully implemented as of Phase 14–15.

**Offline-Capable Features (all implemented)**

| Offline Action | Action Type | Status |
|----------------|-------------|--------|
| Add Member | `member_create` | ✅ |
| Edit Member | `member_update` (LWW) | ✅ |
| Delete Member | `member_delete` (idempotent soft-delete) | ✅ |
| Mark Attendance | `attendance_create` (idempotent) | ✅ |
| Receive Fee / Record Payment | `fee_payment_create` | ✅ |
| Add Expense | `expense_create` | ✅ |
| Add Trainer | `trainer_create` | ✅ |
| Edit Trainer | `trainer_update` (LWW) | ✅ |
| Update Settings | `settings_update` (LWW) | ✅ |

**Offline Data Storage**
- ✅ IndexedDB (`warmup_offline` database, `action_queue` object store)
- ✅ Queue helpers exported from `offline.js`: `queueAction`, `queueMemberDelete`, `queueTrainerCreate`, `queueTrainerUpdate`, `queueSettingsUpdate`, `queueMemberUpdate`

**Synchronization Behavior**
- ✅ Automatic synchronization of all pending records with the server when connectivity is restored.
- ✅ Conflict resolution using Last-Write-Wins (LWW) for all UPDATE operations — client must supply `client_updated_at`; if server's `updated_at` is newer, a structured `conflict` response is returned and the action is retained in the IndexedDB queue.
- ✅ Permanent save to MySQL database on successful sync.
- ✅ Batch sync: up to 50 actions per request (`BATCH_SIZE = 50`).
- ✅ Sync results returned per-action: `success`, `conflict`, or `error` statuses.
- ✅ Offline sync activity logged in Activity Log.

**Service Worker**
- ✅ Service Worker registered (`public/sw.js`) — Phase 14.
- ✅ App Shell cached on install; offline fallback page served (`public/offline.html`).
- ✅ Navigation requests: network-first, fallback to offline page.
- ✅ API requests (`/api/*`) not cached — let client queue them.

**Sync Status Indicator**
- ✅ Persistent status pill in top navigation bar, always visible:

| Status | Meaning |
|--------|---------|
| Online | Device is connected; app is communicating with the server normally. |
| Offline | No internet connection; changes are being queued locally in IndexedDB. |
| Syncing... | Connection restored; pending records are being uploaded. |
| Synced Successfully | All local changes have been saved to the server. |

---

## 10  Multi-Tenant Architecture (Future)

| Version | Scope |
|---------|-------|
| Current Version (V1) | Single Gym |
| Future Version | Multiple Gyms (multi-tenant) |

In the future multi-tenant model, every record will carry a `gym_id` to scope data per tenant, including: Members, Attendance, Fees, Expenses, Trainers, Reports. All of the above will belong to a specific gym, enabling strict data isolation between tenants.

> Note: The current data model is single-tenant. No `gym_id` columns exist in V1 migrations. Multi-tenant migration is planned for Phase 2.

---

## 11  SaaS Subscription Plans (Future)

| Plan | Positioning |
|------|-------------|
| Starter | Entry-level tier with core modules. |
| Professional | Mid tier with extended reporting and reminders. |
| Elite | Full-featured tier with premium/white-label capabilities. |

Feature access will be controlled according to the gym's selected subscription plan.

---

## 12  UI / UX Requirements

### Theme ✅

| Token | Value | Usage | Status |
|-------|-------|-------|--------|
| Theme Mode | Light Theme (default) / Dark Theme | Switchable via Settings; persisted to DB | ✅ |
| Primary Color | `#22C55E` (configurable) | Buttons, active states, brand accents | ✅ |
| Hover Color | `#16A34A` (configurable) | Hover / secondary interaction state | ✅ |
| Background | `#F1F1F1` | Page/canvas background | ✅ |
| Text | `#111827` | Primary heading & body text color | ✅ |

Brand colors (primary and secondary) are now fully configurable from Settings with a live preview. They are applied via CSS custom properties (`--gym-primary`, `--gym-secondary`).

### Design Style ✅
- ✅ Modern SaaS Dashboard aesthetic
- ✅ Rounded Cards and Rounded Buttons
- ✅ Soft Shadows
- ✅ Minimal Design
- ✅ Responsive Layout
- ✅ Sidebar Navigation (dark background `#0F172A`, collapsible on mobile)
- ✅ Top Navigation (with global search, network status, notifications bell)
- ✅ Clean Typography (Inter font; Noto Nastaliq Urdu for RTL languages)
- ✅ WarmUp logo/brand across the dashboard (sidebar, login screen, favicon)
- ✅ Two-color gym name rendering (configurable split position)

### Persistent Network Status Indicator ✅

The application displays the current connectivity and sync status persistently in the navigation bar.

---

## 13  Responsive Design 🔶

WarmUp is designed for desktop and laptop usage as the primary target. Responsive breakpoints exist for tablet and mobile via Tailwind CSS classes, but full mobile optimization is not the primary focus for the FYP evaluation.

- ✅ Desktop — fully supported
- ✅ Laptop — fully supported
- 🔶 Tablet — basic responsiveness via Tailwind breakpoints; not fully optimized
- 🔶 Mobile — basic responsiveness; sidebar toggle (hamburger) exists; full mobile UX not prioritized

Responsive elements implemented:
- ✅ Responsive sidebar (mobile hamburger toggle button exists in top nav)
- ✅ Responsive dashboard cards (grid breakpoints)
- ✅ Responsive forms (grid layouts collapse to single column on small screens)
- 🔶 Responsive tables (horizontal scroll where applicable; stacked view not fully implemented)
- ✅ Responsive charts (ApexCharts auto-resize)

---

## 14  Technology Stack

### Frontend ✅
- ✅ HTML5 (Laravel Blade templates)
- ✅ Tailwind CSS (via Vite build pipeline, `app.css`)
- ✅ JavaScript (ES6 modules via Vite, `app.js` + `offline.js`)
- ✅ Lucide Icons (loaded via CDN)
- ✅ ApexCharts (loaded via CDN — used on Dashboard)
- ✅ SweetAlert2 (included in `app.js`)
- ✅ IndexedDB (offline storage, implemented in `offline.js`)
- ✅ Service Worker (`public/sw.js`)
- ⬜ AOS (Animate on Scroll) — referenced in PRD but not currently found in active use
- ⬜ Flatpickr — referenced in PRD but not currently found in active use

### Backend ✅
- ✅ PHP
- ✅ Laravel (framework)
- ✅ Laravel Blade (templating)
- ✅ Laravel Middleware (auth, guest guards)
- ✅ RESTful APIs (search, offline sync, notifications unread count)
- ✅ Eloquent ORM (all models with proper relationships)
- ✅ Laravel Migrations (15 migrations in total)
- ✅ barryvdh/laravel-dompdf (PDF export)
- ✅ maatwebsite/laravel-excel (Excel export)

### Database ✅
- ✅ MySQL
- ✅ Tables: `users`, `gym_settings`, `membership_plans`, `trainers`, `members` (with SoftDeletes), `member_memberships`, `attendances`, `fee_payments`, `expense_categories`, `expenses`, `notifications`, `activity_logs`, `password_reset_tokens`

### Development Tools ✅
- ✅ VS Code
- ✅ Git
- ✅ GitHub
- ✅ Composer
- ✅ NPM / Vite
- ✅ XAMPP

---

## 15  Security Requirements

- ✅ Password Hashing (bcrypt via Laravel Hash facade)
- ✅ CSRF Protection (all forms use `@csrf`; CSRF token in meta tag for AJAX)
- ✅ Input Validation (Laravel `$request->validate()` in all controllers)
- ✅ Laravel Authentication (session-based, `auth` middleware on all protected routes)
- ✅ Secure Sessions
- ✅ SQL Injection Protection (Eloquent ORM with parameterized queries)
- ✅ XSS Protection (Blade `{{ }}` auto-escaping)
- ✅ Password change requires current password verification; session invalidated after change
- ✅ Offline sync: strict action type whitelist (only known action types processed)
- ✅ Offline sync: field filtering via `array_intersect_key` (prevents mass assignment of arbitrary fields)
- ⬜ Role-based Authorization (Future — for Trainer/Receptionist/Member roles)

---

## 16  Performance Requirements

- ✅ Fast Page Loading (Vite-built assets, CDN libraries)
- ✅ Optimized Database Queries (Eloquent eager loading with `with()` throughout)
- ✅ Pagination (15 or 20 records per page with `withQueryString()`)
- ⬜ Lazy Loading (Future)
- ⬜ Caching (Future)
- ⬜ Optimized Images (Future)

---

## 17  Testing ✅

The project has a comprehensive automated test suite covering all major modules.

| Test File | Module |
|-----------|--------|
| `AttendanceTest.php` | Attendance Management |
| `AuthTest.php` | Authentication |
| `BackupRestoreTest.php` | Backup & Restore |
| `DashboardTest.php` | Dashboard |
| `ExpenseTest.php` | Expense Management |
| `FeeTest.php` | Fee Management |
| `LocaleTest.php` | Multi-language Support |
| `MemberTest.php` | Member Management |
| `MembershipPlanTest.php` | Membership Plans |
| `NotificationTest.php` | Notifications |
| `OfflineSyncTest.php` | Offline Sync (37 tests covering all action types, LWW, conflicts) |
| `ProfileTest.php` | Profile Management |
| `ReportTest.php` | Reports & Exports |
| `SearchTest.php` | Global Search |
| `SettingsTest.php` | Settings |
| `TrainerTest.php` | Trainer Management |

**Latest test run result (as of Phase 15):** 242 tests, 699 assertions, 0 failures, 0 errors, 0 skipped.

---

## 18  Future Roadmap (Phase 2)

- ⬜ Easy2Gym White-label Website
- ⬜ Multi-Tenant SaaS Platform (with `gym_id` data isolation)
- ⬜ Multiple Branches per gym
- ⬜ Multiple User Roles (Trainer, Receptionist, Member portals)
- ⬜ QR Attendance (scan-to-check-in)
- ⬜ Payment Gateway Integration (Stripe, EasyPaisa live, JazzCash live)
- ⬜ Email Notifications
- ⬜ WhatsApp Notifications
- ⬜ SMS Notifications
- ⬜ Mobile Application (Android/iOS)
- ⬜ Public REST API
- ⬜ AI Reports & Insights
- ⬜ Cloud Deployment
- ⬜ Automatic Backups (scheduled; currently manual download only)
- ⬜ AOS (Animate on Scroll) animations
- ⬜ Flatpickr date pickers
- ⬜ Full Mobile-Optimized UX (beyond current responsive grid breakpoints)
- ⬜ Lazy Loading and Server-side Caching

---

## 19  Features NOT Included in Version 1 (Out of Scope)

To keep the FYP scope focused and avoid paid third-party integrations, the following are intentionally excluded from the MVP:

| # | Excluded Feature |
|---|-----------------|
| 1 | Stripe Payment Gateway |
| 2 | EasyPaisa / JazzCash Live Integration |
| 3 | Credit/Debit Card Payments (live gateway) |
| 4 | WhatsApp Messaging |
| 5 | Email Notifications |
| 6 | SMS Notifications |
| 7 | QR Attendance |
| 8 | Multiple User Roles (Trainer, Receptionist, Member Login) |
| 9 | Multiple Branch Management |
| 10 | Mobile App |
| 11 | Cloud Hosting (runs locally via XAMPP during FYP) |
| 12 | Browser Web Push Notifications (omitted per user feedback) |

---

## 20  Success Metrics

- ✅ Gym Owner can complete member onboarding (add member + assign plan) in under 2 minutes.
- ✅ Attendance marking for a full class/session completes in under 1 minute.
- ✅ 100% of offline actions (add/edit/delete member, attendance, fee entry, expense entry, add/edit trainer, update settings) sync successfully once connectivity is restored.
- ✅ Reports (Revenue, Attendance, Fee, Expense) export correctly to both PDF and Excel with accurate data.
- ✅ Dashboard and all core modules render correctly across Desktop and Laptop breakpoints.
- ✅ Activity log accurately records all CRUD actions performed by the Gym Owner.
- ✅ Global search returns relevant results across Members, Trainers, and Membership Plans within 1 second.
- ✅ 242 automated tests pass with 0 failures.

---

## 21  Assumptions & Constraints

- Version 1 is single-tenant; multi-tenant architecture is designed for but not activated.
- The application runs locally (via XAMPP) during the FYP evaluation period; cloud hosting is out of scope.
- Only the Gym Owner has an authenticated login in V1 — members, trainers, and staff are managed as records, not accounts.
- Payment collection is recorded manually in-app; no live payment gateway is integrated in V1.
- Expense tracking is manual; no automated accounting integrations are planned for V1.
- IndexedDB is assumed to be available in the target browsers for offline storage.
- Profile photo uploads (member/trainer) and gym logo uploads require an internet connection; they cannot be queued offline (file uploads excluded from offline sync by design).
- Gym logo cannot be updated via offline sync (requires a file upload).

---

## Appendix A — Changelog

| Version | Change | Section |
|---------|--------|---------|
| 1.1 | Added 4 new Dashboard widgets: Today's New Members, Memberships Expiring Soon, Monthly Expenses, Net Profit | 6.1 |
| 1.1 | Added Global Search module covering Members, Trainers, and Membership Plans | 6.1.1 |
| 1.1 | Added Upload Member Photo and View Member Check-in History capabilities | 6.2 |
| 1.1 | Added 'Expiring Soon' to Membership Status options | 6.2 |
| 1.1 | Added Member Check-in History to Attendance Management | 6.4 |
| 1.1 | Added new Expense Management module (6.7) with full CRUD, categories, and reports | 6.7 |
| 1.1 | Added Expense Report to Reports module | 6.8 |
| 1.1 | Added Print Support for Fee Receipts, Member Details, Attendance, and Revenue Reports | 6.8 |
| 1.1 | Added Browser Notifications (Optional) for Membership Expiry, Pending Fees, and Renewals | 6.9 |
| 1.1 | Added Activity Log module tracking all key CRUD actions | 6.10 |
| 1.1 | Added Date Format, Time Format, and Backup & Restore to Settings | 6.11 |
| 1.1 | Added Persistent Network Status Indicator to UI/UX requirements | 12 |
| 1.1 | Added IndexedDB to Frontend technology stack | 14 |
| 1.1 | Updated Success Metrics and Assumptions to reflect new modules | 19–20 |
| 1.2 | Completed Phase 11 Reports Module: PDF/Excel exports and Print views | 6.8 |
| 1.2 | Completed Phase 12 Notifications & Activity Logs: Activity Log page, Notification Bell (Web Push omitted) | 6.9, 6.10 |
| 1.3 | Completed Phase 13 Final Verification: Global Search, Member Photo Upload, Dashboard Quick Actions, tests | 6.1.1, 6.2, 6.1 |
| 1.4 | Completed Phase 14 Full Implementation: Backup & Restore, Multi-language (English, Urdu, Sindhi), Offline-First/IndexedDB sync, Service Worker, Persistent Network Status. All tests passing. | 6.11, 7, 9, 12 |
| 1.5 | Completed Phase 15 Offline-First Completion: member_delete (soft-delete, idempotent), trainer_create (full validation), trainer_update (LWW), settings_update (LWW, enum validation). Added queue helpers to offline.js. 22 new Phase 15 tests. Full suite: 242 tests, 699 assertions, 0 failures. | 9 |
| 1.5 | **PRD Synchronized** with actual GMS implementation. Added implementation status markers (✅/🔶/⬜) throughout. Added Section 17 (Testing). Added Brand Colors & Split Position to Settings (6.11). Added Trainer show page, Trainer photo, checkout action to relevant sections. Corrected Responsive Design status to 🔶 (partial). Noted AOS and Flatpickr as ⬜ (not currently active). Updated Success Metrics and Assumptions. | All |

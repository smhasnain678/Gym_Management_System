WarmUp
Gym Management Dashboard
Product Requirements Document (PRD)
Version 1.1  •  Final Year Project  •  Updated Edition
A modern, offline-first, SaaS-ready gym management platform — built to digitize gym operations today and scale into the Easy2Gym multi-tenant SaaS platform tomorrow.
Document Control
Field	Detail
Product Name	WarmUp
Document Type	Product Requirements Document (PRD)
Version	1.1
Status	Updated — Final Year Project (FYP)
Parent Brand (Future SaaS)	Easy2Gym
Primary Platform	Web (Responsive Dashboard)
Owner	Gym Owner (single-tenant, Version 1)

1  Product Overview
WarmUp is a modern, responsive Gym Management Dashboard designed for gym owners to digitize and manage their daily operations efficiently. The system enables gym owners to manage members, trainers, attendance, memberships, fee collection, expenses, reports, and gym settings from a single, unified dashboard.
WarmUp is designed as an offline-first, SaaS-ready application. It is built to work independently for a single gym in its first version, and is architected so it can later be transformed into a multi-tenant SaaS platform under the Easy2Gym brand without a ground-up rewrite.

2  Product Vision
To build a modern, affordable, and user-friendly Gym Management System that simplifies day-to-day gym operations for small and independent gym owners, while providing a scalable technical and architectural foundation for a future white-label SaaS platform (Easy2Gym).

3  Goals & Objectives
●	Digitize gym operations end-to-end, replacing manual registers and spreadsheets.
●	Reduce manual paperwork for member records, attendance, and fee tracking.
●	Simplify fee management with clear visibility into paid, pending, and due amounts.
●	Track expenses and calculate net profit for each month.
●	Improve attendance tracking accuracy with daily and monthly views.
●	Generate reports automatically, exportable to PDF and Excel.
●	Support multiple languages for wider regional accessibility (English, Urdu, Sindhi).
●	Work even without internet, using an offline-first architecture with background sync.
●	Log all important activities for auditing and accountability.
●	Prepare the system's data model and architecture for future multi-tenant SaaS deployment.

4  Target Users
Primary User
●	Gym Owner — full access to the dashboard and all management modules.
Managed Entities (No Login Access)
●	Members — records managed by the Gym Owner; no self-service login in V1.
●	Trainers — records managed by the Gym Owner; no self-service login in V1.
●	Staff — records managed by the Gym Owner; no self-service login in V1.
Note: Version 1 supports authentication for the Gym Owner role only. Member, Trainer, and Receptionist logins are planned for future versions.

5  User Authentication
Version 1 implements a single-role authentication system scoped to the Gym Owner.
Feature	Description
Login	Gym Owner signs in with registered email/username and password.
Forgot Password	Owner can request a password reset flow if credentials are lost.
Change Password	Owner can update their password from within the app.
Profile Management	Owner can view and edit their personal/account profile details.

No member login. No trainer login. No receptionist login. These roles will be introduced in future versions with role-based authorization.

6  Core Modules — Functional Requirements
6.1  Dashboard [UPDATED]
Overview Widgets
●	Total Members
●	Active Members
●	Trainers count
●	Today's Attendance
●	Monthly Revenue
●	Pending Fees
●	Membership Statistics
●	Recent Activities feed
●	Quick Actions shortcuts
●	Today's New Members
●	Memberships Expiring Soon
●	Monthly Expenses
●	Net Profit
Items in bold are newly added widgets in v1.1.

6.1.1  Global Search [NEW]
The Gym Owner can quickly search across the entire system from a persistent search bar in the top navigation. Global search covers:
●	Members
●	Trainers
●	Membership Plans

6.2  Member Management [UPDATED]
Capabilities
●	Add Member
●	Edit Member
●	Delete Member
●	Member Profile (photo, contact info, membership details)
●	Upload Member Photo
●	View Member Check-in History
●	Membership Details (plan, start/end date, status)
●	Emergency Contact information
●	Medical Notes
●	Search Members
●	Filter Members (by status, plan, trainer, etc.)

Membership Status [UPDATED]
●	Active
●	Expired
●	Expiring Soon
●	Suspended

6.3  Trainer / Staff Management
Capabilities
●	Add Trainer
●	Edit Trainer
●	Delete Trainer
●	Trainer Details (bio, specialization, contact)
●	Salary Information
●	Joining Date
●	Assigned Members
●	Active/Inactive Status

6.4  Attendance Management [UPDATED]
Capabilities
●	Mark Attendance (manual, per member)
●	Daily Attendance view
●	Monthly Attendance view
●	Attendance History
●	Present / Absent Status tracking
●	Attendance Statistics and trends
●	Member Check-in History
Future scope
●	QR-code based Attendance (scan-to-check-in)

6.5  Membership Management
Capabilities
●	Membership Plans (create/manage plan types)
●	Start Date
●	End Date
●	Membership Duration
●	Membership Status
●	Renew Membership

6.6  Fee Management
Capabilities
●	Receive Fee
●	Total Fee
●	Paid Amount
●	Remaining Amount
●	Due Date tracking
●	Payment History
●	Pending Fee List
●	Membership Renewal Reminder
Future scope
●	Stripe integration
●	EasyPaisa integration
●	JazzCash integration
●	Debit/Credit Card payments

6.7  Expense Management [NEW]
A dedicated module for tracking gym operational expenses, enabling the Gym Owner to monitor costs and calculate net profit.
Capabilities
●	Add Expense
●	Edit Expense
●	Delete Expense
●	Expense Categories (e.g. Rent, Utilities, Salaries, Equipment, Maintenance)
●	Monthly Expense Tracking
●	Expense History
●	Expense Reports (exportable to PDF and Excel)

6.8  Reports [UPDATED]
Available Reports
●	Revenue Report
●	Attendance Report
●	Member Report
●	Membership Report
●	Trainer Report
●	Fee Collection Report
●	Expense Report

Export Functionality
All reports must be exportable to PDF and Excel formats.

Print Support [NEW]
The system shall support printing of:
●	Fee Receipts
●	Member Details
●	Attendance Reports
●	Revenue Reports

6.9  Notifications & Reminders [UPDATED]
System Notifications (In-App)
●	Upcoming Membership Expiry
●	Pending Fee Reminder
●	Renewal Reminder

Browser Notifications (Optional) [NEW]
The system may display browser notifications for:
●	Membership Expiry
●	Pending Fees
●	Renewal Reminders
Future scope
●	WhatsApp notifications
●	Email notifications
●	SMS notifications

6.10  Activity Log [NEW]
The system records important user activities for tracking and auditing purposes. The Gym Owner can review the activity log to understand what changes were made and when.
Logged Activities
●	Member Added
●	Member Updated
●	Member Deleted
●	Attendance Marked
●	Fee Received
●	Expense Added
●	Trainer Added
●	Trainer Updated
●	Settings Updated

6.11  Settings [UPDATED]
Configurable Options
●	Gym Name
●	Gym Logo
●	Owner Information
●	Contact Details
●	Currency
●	Timezone
●	Language
●	Theme
●	Date Format
●	Time Format
●	Backup & Restore

7  Multi-language Support
●	English
●	اردو (Urdu)
●	سنڌي (Sindhi)
The language can be changed from Settings without restarting the application — the UI must support live/dynamic language switching.

8  White Label Readiness (Future — Easy2Gym SaaS)
●	Gym Name — configurable per tenant
●	Gym Logo — configurable per tenant
●	Primary Brand Color — configurable per tenant
●	Custom Branding across the dashboard
●	Subscription Plans per tenant
●	Multi-Tenant Architecture supporting multiple gyms on one platform

9  Offline-First Support ⭐
WarmUp must continue working even if the internet connection is lost. This is a core architectural requirement, not an enhancement.
Offline-Capable Features
●	Add Member
●	Edit Member
●	Delete Member
●	Mark Attendance
●	Receive Fee
●	Add Expense
●	Update Member
●	Add Trainer
●	Edit Trainer
●	Update Settings
Offline Data Storage
Offline data is temporarily stored in the browser. IndexedDB is preferred over LocalStorage because it supports larger, structured, queryable data — a requirement for storing member, attendance, fee, and expense records offline.
Synchronization Behavior
When the internet connection is restored, the system must:
●	Automatically synchronize all pending records with the server.
●	Resolve conflicts using timestamps (last-write-wins based on record modification time).
●	Save records permanently to the MySQL database.
Sync Status Indicator
The user must always be able to see the current synchronization state via a persistent status indicator:
Status	Meaning
Online	Device is connected; app is communicating with the server normally.
Offline	No internet connection; changes are being queued locally.
Syncing...	Connection restored; pending records are being uploaded.
Synced Successfully	All local changes have been saved to the server.

10  Multi-Tenant Architecture (Future)
Version	Scope
Current Version (V1)	Single Gym
Future Version	Multiple Gyms (multi-tenant)

In the future multi-tenant model, every record will carry a gym_id to scope data per tenant, including:
●	Members
●	Attendance
●	Fees
●	Expenses
●	Trainers
●	Reports
All of the above will belong to a specific gym, enabling strict data isolation between tenants.

11  SaaS Subscription Plans (Future)
Plan	Positioning
Starter	Entry-level tier with core modules.
Professional	Mid tier with extended reporting and reminders.
Elite	Full-featured tier with premium/white-label capabilities.

Feature access will be controlled according to the gym's selected subscription plan.

12  UI / UX Requirements
Theme
Token	Value	Usage
Theme Mode	Light Theme	Default application theme
Primary Color	#22C55E	Buttons, active states, brand accents
Hover Color	#16A34A	Hover / secondary interaction state
Background	#F1F1F1	Page/canvas background
Text	#111827	Primary heading & body text color

Design Style
●	Modern SaaS Dashboard aesthetic
●	Rounded Cards
●	Rounded Buttons
●	Soft Shadows
●	Minimal Design
●	Responsive Layout
●	Sidebar Navigation
●	Top Navigation
●	Clean Typography
Use the WarmUp logo across the dashboard (navbar, login screen, sidebar, favicon), and keep button and card corner radii visually consistent with the logo's rounded, soft-cornered style.

Persistent Network Status Indicator [NEW]
The application shall display the current connectivity and sync status persistently in the navigation bar, always visible to the Gym Owner:
Status	Meaning
Online	Device is connected; data is syncing normally.
Offline	No internet; changes are being queued in IndexedDB.
Syncing...	Connectivity restored; uploading pending local records.
Synced Successfully	All pending changes saved to the server.

13  Responsive Design
WarmUp must be fully responsive across the following devices:
●	Desktop
●	Laptop
●	Tablet
●	Mobile
Responsive requirements apply specifically to:
●	Responsive sidebar (collapsible on smaller screens)
●	Responsive tables (horizontal scroll / stacked view on mobile)
●	Responsive charts
●	Responsive forms
●	Responsive dashboard cards

14  Technology Stack
Frontend [UPDATED]
●	HTML5
●	Tailwind CSS
●	JavaScript (ES6)
●	AOS (Animate on Scroll)
●	Lucide Icons
●	ApexCharts
●	SweetAlert2
●	Flatpickr
●	IndexedDB (Offline Storage)

Backend
●	PHP
●	Laravel
●	Laravel Blade
●	Laravel Middleware
●	RESTful APIs
●	Eloquent ORM
●	Laravel Migrations

Database
●	MySQL

Development Tools
●	VS Code
●	Git
●	GitHub
●	Composer
●	NPM
●	XAMPP

15  Security Requirements
●	Password Hashing
●	CSRF Protection
●	Input Validation
●	Laravel Authentication
●	Role-based Authorization (Future — for Trainer/Receptionist/Member roles)
●	Secure Sessions
●	SQL Injection Protection
●	XSS Protection

16  Performance Requirements
●	Fast Page Loading
●	Optimized Database Queries
●	Lazy Loading (Future)
●	Caching (Future)
●	Optimized Images

17  Future Roadmap (Phase 2)
●	Easy2Gym White-label Website
●	Multi-Tenant SaaS Platform
●	Multiple Branches per gym
●	Multiple User Roles (Trainer, Receptionist, Member)
●	QR Attendance
●	Payment Gateway Integration (Stripe, EasyPaisa, JazzCash)
●	Email Notifications
●	WhatsApp Notifications
●	SMS Notifications
●	Mobile Application (Android/iOS)
●	Public REST API
●	AI Reports & Insights
●	Cloud Deployment
●	Automatic Backups

18  Features NOT Included in Version 1 (Out of Scope)
To keep the FYP scope focused and avoid paid third-party integrations, the following are intentionally excluded from the MVP:
#	Excluded Feature
1	Stripe Payment Gateway
2	EasyPaisa / JazzCash Integration
3	Credit/Debit Card Payments
4	WhatsApp Messaging
5	Email Notifications
6	SMS Notifications
7	QR Attendance
8	Multiple User Roles (Trainer, Receptionist, Member Login)
9	Multiple Branch Management
10	Mobile App
11	Cloud Hosting (runs locally during FYP)

19  Success Metrics
●	Gym Owner can complete member onboarding (add member + assign plan) in under 2 minutes.
●	Attendance marking for a full class/session completes in under 1 minute.
●	100% of offline actions (add/edit/delete member, attendance, fee entry, expense entry) sync successfully once connectivity is restored.
●	Reports (Revenue, Attendance, Fee, Expense) export correctly to both PDF and Excel with accurate data.
●	Dashboard and all core modules render correctly across Desktop, Tablet, and Mobile breakpoints.
●	Activity log accurately records all CRUD actions performed by the Gym Owner.
●	Global search returns relevant results across Members, Trainers, and Membership Plans within 1 second.

20  Assumptions & Constraints
●	Version 1 is single-tenant; multi-tenant architecture is designed for but not activated.
●	The application will run locally (e.g., via XAMPP) during the FYP evaluation period; cloud hosting is out of scope.
●	Only the Gym Owner has an authenticated login in V1 — members, trainers, and staff are managed as records, not accounts.
●	Payment collection is recorded manually in-app; no live payment gateway is integrated in V1.
●	Expense tracking is manual; no automated accounting integrations are planned for V1.
●	IndexedDB is assumed to be available in the target browsers for offline storage.

Appendix A — Changelog
Version	Change	Section
1.1	Added 4 new Dashboard widgets: Today's New Members, Memberships Expiring Soon, Monthly Expenses, Net Profit	6.1
1.1	Added Global Search module covering Members, Trainers, and Membership Plans	6.1.1
1.1	Added Upload Member Photo and View Member Check-in History capabilities	6.2
1.1	Added 'Expiring Soon' to Membership Status options	6.2
1.1	Added Member Check-in History to Attendance Management	6.4
1.1	Added new Expense Management module (6.7) with full CRUD, categories, and reports	6.7
1.1	Added Expense Report to Reports module	6.8
1.1	Added Print Support for Fee Receipts, Member Details, Attendance, and Revenue Reports	6.8
1.1	Added Browser Notifications (Optional) for Membership Expiry, Pending Fees, and Renewals	6.9
●	Delete Member
●	Mark Attendance
●	Receive Fee
●	Add Expense
●	Update Member
●	Add Trainer
●	Edit Trainer
●	Update Settings
Offline Data Storage
Offline data is temporarily stored in the browser. IndexedDB is preferred over LocalStorage because it supports larger, structured, queryable data — a requirement for storing member, attendance, fee, and expense records offline.
Synchronization Behavior
When the internet connection is restored, the system must:
●	Automatically synchronize all pending records with the server.
●	Resolve conflicts using timestamps (last-write-wins based on record modification time).
●	Save records permanently to the MySQL database.
Sync Status Indicator
The user must always be able to see the current synchronization state via a persistent status indicator:
Status	Meaning
Online	Device is connected; app is communicating with the server normally.
Offline	No internet connection; changes are being queued locally.
Syncing...	Connection restored; pending records are being uploaded.
Synced Successfully	All local changes have been saved to the server.

10  Multi-Tenant Architecture (Future)
Version	Scope
Current Version (V1)	Single Gym
Future Version	Multiple Gyms (multi-tenant)

In the future multi-tenant model, every record will carry a gym_id to scope data per tenant, including:
●	Members
●	Attendance
●	Fees
●	Expenses
●	Trainers
●	Reports
All of the above will belong to a specific gym, enabling strict data isolation between tenants.

11  SaaS Subscription Plans (Future)
Plan	Positioning
Starter	Entry-level tier with core modules.
Professional	Mid tier with extended reporting and reminders.
Elite	Full-featured tier with premium/white-label capabilities.

Feature access will be controlled according to the gym's selected subscription plan.

12  UI / UX Requirements
Theme
Token	Value	Usage
Theme Mode	Light Theme	Default application theme
Primary Color	#22C55E	Buttons, active states, brand accents
Hover Color	#16A34A	Hover / secondary interaction state
Background	#F1F1F1	Page/canvas background
Text	#111827	Primary heading & body text color

Design Style
●	Modern SaaS Dashboard aesthetic
●	Rounded Cards
●	Rounded Buttons
●	Soft Shadows
●	Minimal Design
●	Responsive Layout
●	Sidebar Navigation
●	Top Navigation
●	Clean Typography
Use the WarmUp logo across the dashboard (navbar, login screen, sidebar, favicon), and keep button and card corner radii visually consistent with the logo's rounded, soft-cornered style.

Persistent Network Status Indicator [NEW]
The application shall display the current connectivity and sync status persistently in the navigation bar, always visible to the Gym Owner:
Status	Meaning
Online	Device is connected; data is syncing normally.
Offline	No internet; changes are being queued in IndexedDB.
Syncing...	Connectivity restored; uploading pending local records.
Synced Successfully	All pending changes saved to the server.

13  Responsive Design
WarmUp must be fully responsive across the following devices:
●	Desktop
●	Laptop
●	Tablet
●	Mobile
Responsive requirements apply specifically to:
●	Responsive sidebar (collapsible on smaller screens)
●	Responsive tables (horizontal scroll / stacked view on mobile)
●	Responsive charts
●	Responsive forms
●	Responsive dashboard cards

14  Technology Stack
Frontend [UPDATED]
●	HTML5
●	Tailwind CSS
●	JavaScript (ES6)
●	AOS (Animate on Scroll)
●	Lucide Icons
●	ApexCharts
●	SweetAlert2
●	Flatpickr
●	IndexedDB (Offline Storage)

Backend
●	PHP
●	Laravel
●	Laravel Blade
●	Laravel Middleware
●	RESTful APIs
●	Eloquent ORM
●	Laravel Migrations

Database
●	MySQL

Development Tools
●	VS Code
●	Git
●	GitHub
●	Composer
●	NPM
●	XAMPP

15  Security Requirements
●	Password Hashing
●	CSRF Protection
●	Input Validation
●	Laravel Authentication
●	Role-based Authorization (Future — for Trainer/Receptionist/Member roles)
●	Secure Sessions
●	SQL Injection Protection
●	XSS Protection

16  Performance Requirements
●	Fast Page Loading
●	Optimized Database Queries
●	Lazy Loading (Future)
●	Caching (Future)
●	Optimized Images

17  Future Roadmap (Phase 2)
●	Easy2Gym White-label Website
●	Multi-Tenant SaaS Platform
●	Multiple Branches per gym
●	Multiple User Roles (Trainer, Receptionist, Member)
●	QR Attendance
●	Payment Gateway Integration (Stripe, EasyPaisa, JazzCash)
●	Email Notifications
●	WhatsApp Notifications
●	SMS Notifications
●	Mobile Application (Android/iOS)
●	Public REST API
●	AI Reports & Insights
●	Cloud Deployment
●	Automatic Backups

18  Features NOT Included in Version 1 (Out of Scope)
To keep the FYP scope focused and avoid paid third-party integrations, the following are intentionally excluded from the MVP:
#	Excluded Feature
1	Stripe Payment Gateway
2	EasyPaisa / JazzCash Integration
3	Credit/Debit Card Payments
4	WhatsApp Messaging
5	Email Notifications
6	SMS Notifications
7	QR Attendance
8	Multiple User Roles (Trainer, Receptionist, Member Login)
9	Multiple Branch Management
10	Mobile App
11	Cloud Hosting (runs locally during FYP)

19  Success Metrics
●	Gym Owner can complete member onboarding (add member + assign plan) in under 2 minutes.
●	Attendance marking for a full class/session completes in under 1 minute.
●	100% of offline actions (add/edit/delete member, attendance, fee entry, expense entry) sync successfully once connectivity is restored.
●	Reports (Revenue, Attendance, Fee, Expense) export correctly to both PDF and Excel with accurate data.
●	Dashboard and all core modules render correctly across Desktop, Tablet, and Mobile breakpoints.
●	Activity log accurately records all CRUD actions performed by the Gym Owner.
●	Global search returns relevant results across Members, Trainers, and Membership Plans within 1 second.

20  Assumptions & Constraints
●	Version 1 is single-tenant; multi-tenant architecture is designed for but not activated.
●	The application will run locally (e.g., via XAMPP) during the FYP evaluation period; cloud hosting is out of scope.
●	Only the Gym Owner has an authenticated login in V1 — members, trainers, and staff are managed as records, not accounts.
●	Payment collection is recorded manually in-app; no live payment gateway is integrated in V1.
●	Expense tracking is manual; no automated accounting integrations are planned for V1.
●	IndexedDB is assumed to be available in the target browsers for offline storage.

Appendix A — Changelog
Version	Change	Section
1.1	Added 4 new Dashboard widgets: Today's New Members, Memberships Expiring Soon, Monthly Expenses, Net Profit	6.1
1.1	Added Global Search module covering Members, Trainers, and Membership Plans	6.1.1
1.1	Added Upload Member Photo and View Member Check-in History capabilities	6.2
1.1	Added 'Expiring Soon' to Membership Status options	6.2
1.1	Added Member Check-in History to Attendance Management	6.4
1.1	Added new Expense Management module (6.7) with full CRUD, categories, and reports	6.7
1.1	Added Expense Report to Reports module	6.8
1.1	Added Print Support for Fee Receipts, Member Details, Attendance, and Revenue Reports	6.8
1.1	Added Browser Notifications (Optional) for Membership Expiry, Pending Fees, and Renewals	6.9
1.1	Added Activity Log module tracking all key CRUD actions	6.10
1.1	Added Date Format, Time Format, and Backup & Restore to Settings	6.11
1.1	Added Persistent Network Status Indicator to UI/UX requirements	12
1.1	Added IndexedDB to Frontend technology stack	14
1.1	Updated Success Metrics and Assumptions to reflect new modules	19-20
1.1	Completed Phase 11 Reports Module implementation, including PDF/Excel exports and Print functionalities	6.8
1.2	Completed Phase 12 Notifications & Activity Logs implementation, including Activity Log page and Notification Bell (omitted Web Push per user feedback)	6.9, 6.10
1.3	Completed Phase 13 Final Verification: Global Search, Member Photo Upload, Dashboard Quick Actions, and Tests without any DB changes	6.1.1, 6.2, 6.1
1.4	Completed Phase 14 Full Implementation: Backup & Restore, Multi-language (English, Urdu, Sindhi), Offline-First/IndexedDB Synchronization, and Persistent Network Status. All tests passing without any schema modifications.	6.11, 7, 9, 12
1.5	Completed Phase 15 Offline-First Completion: Added offline member_delete (soft-delete via SoftDeletes, idempotent for already-deleted records), trainer_create (full validation: required fields, gender enum, salary non-negative, email uniqueness), trainer_update (LWW conflict resolution using updated_at comparison via Carbon), and settings_update (LWW, language/theme/time_format enum validation). Implemented Last-Write-Wins conflict resolution for all UPDATE operations (member_update, trainer_update, settings_update) — client must supply client_updated_at; if server updated_at is newer, returns structured conflict response and retains action in IndexedDB queue. Added JavaScript queue helpers (queueMemberDelete, queueTrainerCreate, queueTrainerUpdate, queueSettingsUpdate, queueMemberUpdate) to offline.js. Conflict, error, and success statuses handled correctly in sync result processing. 22 new Phase 15 tests added (37 total OfflineSync tests); full suite: 242 tests, 699 assertions, 0 failures, 0 errors, 0 skipped. No schema changes, no new migrations, no warmup.sql modifications.	9, 12

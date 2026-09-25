# BizHR developer roadmap

## 1. What this project is

BizHR is a server-rendered HR information system (HRIS) for managing an organisation's people and HR operations. It is built as a Laravel monolith: one application owns the web pages, business rules, authentication, authorisation, database migrations, background jobs, and tests.

The core business chain is:

```text
Company -> branches/departments/positions -> employees -> schedules
        -> attendance and leave -> payroll -> payment/payslip
```

Supporting modules cover employee documents, contracts, imports/exports, assets, expenses, announcements, recruitment, tasks, training, performance, audit logs, and access administration.

This is a good reference project, but it is too large for a first student project. Build the MVP first, then add modules in the order below.

## 2. Technology stack

| Layer | Technology in BizHR | Why it is used |
| --- | --- | --- |
| Backend | PHP 8.3, Laravel 13 | Routing, validation, ORM, queues, authorization, testing |
| Database | PostgreSQL (Supabase-hosted in production) | Relational HR data, transactions, indexes, constraints |
| Frontend | Blade templates, Bootstrap 5, Font Awesome | Fast server-rendered administrative UI |
| Small interactive UI | HTMX, vanilla JavaScript | Partial updates and confirmation/dialog behavior without a SPA |
| Build tooling | Vite, Node.js/npm | Bundles CSS and JavaScript assets |
| Authentication | Laravel Fortify, Laravel Passkeys | Login, password reset, email verification, 2FA/passkeys |
| Authorization | spatie/laravel-permission plus Laravel Policies | Roles, permissions, record ownership, company boundaries |
| Import/export | spatie/simple-excel | Spreadsheet templates, imports and exports |
| Background work | Laravel database queue | Long-running exports and future notifications/imports |
| Quality | Pest/PHPUnit, PHPStan (Larastan), Laravel Pint | Tests, static analysis, code formatting |
| Deployment dependencies | PHP/container host, queue worker, scheduler, private object storage | Needed for reliable production workflows |

Important: the repository contains `vercel.json`, but its own deployment notes say Vercel is not an appropriate production backend for this Laravel app without proven PHP/worker/storage support. A PHP/container host with PostgreSQL, a worker, scheduler, and private storage is the safer deployment target.

## 3. Project structure

```text
bizhr/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Accept HTTP requests and return views/redirects
│   │   └── Middleware/       # Active-user, employee-context, security guards
│   ├── Models/               # Eloquent database models and relationships
│   ├── Services/             # Business workflows and calculations
│   ├── Policies/             # Per-record authorization rules
│   ├── Observers/            # Audit/history side effects after model changes
│   ├── Jobs/                 # Queued work, e.g. export generation
│   ├── Actions/Fortify/      # Registration and password-reset actions
│   └── Console/Commands/     # Maintenance commands
├── bootstrap/                # Laravel startup and provider registration
├── config/                   # Database, auth, queue, storage, app settings
├── database/
│   ├── migrations/           # Version-controlled schema changes
│   ├── seeders/              # Roles, permissions, demo/test data
│   └── factories/            # Reusable test data builders
├── lang/en and lang/km/      # English and Khmer application text
├── public/                   # Browser entry point and compiled assets
├── resources/
│   ├── views/                # Blade pages, layouts, partials, components
│   ├── css/                  # Source styles
│   └── js/                   # Source JavaScript (QR/passkeys/app behavior)
├── routes/
│   ├── web.php               # Main browser routes and permission middleware
│   ├── settings.php          # Account/settings routes
│   └── console.php           # Scheduled/console definitions
├── tests/
│   ├── Feature/              # HTTP, authorization, UI, and workflow tests
│   └── Unit/                 # Pure service/calculator tests
├── docs/                     # Production and module-readiness notes
├── composer.json             # PHP dependencies and quality scripts
└── package.json              # Frontend dependencies and build script
```

### Request and business-rule flow

```text
Browser
  -> routes/web.php
  -> middleware (auth, active account, verified email, permission)
  -> controller (input validation and orchestration)
  -> policy (may this user access this record?)
  -> service (workflow/calculation inside a DB transaction)
  -> model/database
  -> audit log, notification, or queued job
  -> Blade view / redirect / downloadable private file
```

Keep controllers thin. For example, leave submission belongs in `LeaveRequestService`, which calculates working days, prevents date overlaps, locks balances with a transaction, and creates a pending request. Payroll changes similarly belong in `PayrollWorkflowService`, which enforces separated approval/payment/close steps.

## 4. Major domain modules

| Module | Main records | Key learning point |
| --- | --- | --- |
| Organisation | Company, Branch, Department, Position, EmploymentType | Master data and foreign keys |
| Access | User, Role, Permission, UserSession | Authentication, least privilege, account deactivation |
| Employees | Employee, Document, EmploymentHistory, Contract | Personal data, file authorization, audit/history |
| Attendance | WorkShift, EmployeeSchedule, Attendance, Correction, QR session | Dates/times, state transitions, controlled corrections |
| Leave | LeaveType, LeaveBalance, LeaveRequest | Date calculation, balances, approvals, concurrency |
| Payroll | PayrollSetting, Period, Item, Adjustment, Payment | Financial calculations, snapshots, maker-checker approval |
| Operations | Asset, ExpenseClaim, Announcement, Task | Lifecycle workflows and notifications |
| Talent | JobVacancy, Applicant, Interview, Offer, Goal, Review, Training | Private recruitment/performance data and workflow states |
| Governance | AuditLog, DataExport | Traceability and private data handling |

## 5. Data model rules to copy

1. Add `company_id` to every business record that must never cross companies. Scope queries and policies by that value.
2. Use database foreign keys, unique constraints, indexes, and `NOT NULL` values for real invariants. Application validation alone is not enough.
3. Use a public identifier for records exposed in URLs rather than revealing sequential numeric IDs.
4. Use soft deletes for HR records where historical recovery matters; avoid hard deletion of payroll, audit, or approved workflow records.
5. Treat status as a state machine. Define allowed transitions before coding buttons.
6. Use `DB::transaction()` and `lockForUpdate()` when two people could update the same balance, approval, or payroll period simultaneously.
7. Store uploaded HR files privately. Download them through an authorized controller; never expose their storage path directly.
8. Record who did a sensitive action, when, why, and relevant before/after values in an audit log.

## 6. Student build roadmap (12 weeks)

This plan produces a credible mini-BizHR while keeping complexity controlled. A week assumes roughly 8-15 focused learning hours; take longer if this is your first Laravel project.

### Phase 0 — preparation (2-3 days)

- Learn basic PHP, HTTP, HTML forms, SQL joins, Git, and Laravel MVC.
- Install PHP 8.3, Composer, Node.js, PostgreSQL, and Git.
- Create a Laravel application and configure a local PostgreSQL database.
- Add a `.env` file from `.env.example`; never commit credentials.
- Learn the loop: migration -> model -> route/controller -> Blade view -> test.

**Done when:** you can register/login, run migrations, create one page, and commit the code to Git.

### Phase 1 — organisation and login (week 1)

- Add Laravel authentication (Fortify, Breeze, or starter kit).
- Create migrations/models for Company, Branch, Department, Position, and Employee.
- Build simple CRUD pages for branches, departments, and positions.
- Seed one company, roles, permissions, and a test administrator.
- Add `company_id` early, even if the student MVP uses only one company.

**Done when:** an admin can sign in and manage organisation master data.

### Phase 2 — employee directory (weeks 2-3)

- Build employee create, list, search/filter, profile, edit, archive, and restore flows.
- Connect employees to branch, department, position, and optional user account.
- Add server-side validation: employee code unique per company, valid email, required hire date, salary numeric.
- Add a private document upload/download feature with MIME type and file-size rules.
- Add employment-history records whenever an employee's department, position, salary, or status changes.
- Write feature tests for both happy paths and cross-company denial.

**Done when:** HR can manage employee records without data leaking between companies.

### Phase 3 — roles and authorization (week 4)

- Install/configure `spatie/laravel-permission`.
- Start with four roles: Super Admin, HR, Manager, Employee.
- Create permissions such as `employee.view`, `employee.create`, `employee.edit`, `leave.request`, and `leave.approve`.
- Use route middleware for broad permissions and Laravel policies for record-level checks (own profile, direct report, same company).
- Add an audit-log table for employee edits and approval decisions.

**Done when:** an employee can view only their own profile and a manager cannot access another company.

### Phase 4 — attendance and leave (weeks 5-6)

- Create WorkShift and EmployeeSchedule models. Start with one daily shift.
- Implement manual check-in/check-out and calculate worked hours.
- Create LeaveType, LeaveBalance, and LeaveRequest models.
- Calculate leave days excluding weekends first; add public holidays only after tests are clear.
- Implement `pending -> approved/rejected/withdrawn` leave states.
- Lock leave balances in a database transaction when approving a request.
- Add a manager review page and employee "my leave" page.

**Done when:** leave cannot overlap, balance cannot become negative, and approvals are audited.

### Phase 5 — basic payroll (weeks 7-8)

- Do **not** copy legal tax rules without a local payroll expert. Start with a clearly labelled demo formula.
- Create a PayrollPeriod (monthly), PayrollItem (per employee), and PayrollAdjustment.
- Calculate a transparent formula: base salary + allowance + approved overtime - deductions.
- Snapshot calculated values into payroll items; do not recalculate historical payroll from today's employee salary.
- Use statuses: `draft -> awaiting_approval -> approved -> paid -> closed`.
- Enforce maker-checker separation: the processor cannot approve their own payroll.
- Generate a simple payslip view/PDF later, after calculations and tests are correct.

**Done when:** a closed payroll period cannot be silently changed and totals reconcile with its items.

### Phase 6 — usable UI and operational features (weeks 9-10)

- Build a role-aware dashboard with employee count, pending leave, attendance exceptions, and payroll status.
- Add pagination, filters, empty states, validation errors, flash messages, responsive tables, and Khmer/English text if required.
- Add Excel/CSV employee import with a preview and row-level errors.
- Add export jobs that run on a queue and produce temporary private downloads.
- Add notifications for leave approvals and payroll payment.

**Done when:** ordinary HR work can be completed through the UI without database editing.

### Phase 7 — testing, security, and deployment (weeks 11-12)

- Write unit tests for calculators and workflow services; write feature tests for routes and permissions.
- Run formatter, static analysis, dependency audit, and full test suite in CI.
- Test unauthorized file downloads, invalid uploads, inactive accounts, tenant isolation, and duplicate requests.
- Configure production PostgreSQL, HTTPS, mail, private object storage, queue worker, scheduler, backups, monitoring, and error reporting.
- Perform a backup restore test before entering real HR data.

**Done when:** the app can be deployed repeatably, restored from backup, and critical permissions have negative tests.

## 7. Advanced modules — build only after the MVP

1. QR attendance: short-lived token, scan event history, branch rules, replay protection.
2. Expenses/assets: draft, submit, approve, pay/return or assign/transfer/return lifecycle.
3. Recruitment/onboarding: candidate privacy, stage history, interviews, offer, employee conversion.
4. Performance/training: goals, review cycles, templates, approvals, locked snapshots.
5. Reporting: documented metric definitions, filters, drill-down, queued exports.
6. Integrations: versioned APIs, idempotency keys, webhooks, retries, logs, credential rotation.

## 8. Recommended feature checklist

Before calling any module complete, verify all of these:

- A business owner agrees on the workflow and names for every status.
- Database migration, model relations, factories, and seed data exist.
- Server-side validation and useful error messages exist.
- Permissions and company boundaries are explicit and negatively tested.
- Allowed status transitions are enforced by service methods, not only hidden buttons.
- Sensitive actions are audited.
- Lists are paginated; search/filter behavior is practical.
- Files are private and download access is authorized.
- Unit/feature tests, PHPStan, Pint, and dependency audit pass.
- The UI works on small screens and with keyboard navigation.

## 9. Commands a junior developer will use often

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve

# Quality checks
composer lint:check
composer types:check
php artisan test
```

For this repository, `composer test` runs formatting, PHPStan, and automated tests. Check the exact scripts in `composer.json` before using them in CI.

## 10. Common mistakes to avoid

- Starting with payroll, QR, or analytics instead of the employee directory and permissions.
- Placing financial or approval logic directly in controllers.
- Checking only "can edit" and forgetting the same-company/own-record policy check.
- Trusting UI buttons to enforce a workflow; users can still call an HTTP route directly.
- Storing documents in `public/` or logging national IDs, bank account numbers, passwords, or tokens.
- Treating a successful page render as proof that a workflow is correct.
- Using production payroll/tax rules without review by the responsible local payroll and legal professionals.
- Skipping backup restore tests, migrations, and tests because the app "works on my machine".

## 11. Where to learn from this codebase

- `routes/web.php`: the real permission-protected application map.
- `app/Models/Employee.php`: fillable data, casts, relationships, scopes, and soft deletes.
- `app/Services/LeaveRequestService.php`: validation, date calculations, transactions, overlap prevention, row locking.
- `app/Services/PayrollWorkflowService.php`: financial workflow states, segregation of duties, locking, notifications.
- `app/Policies/`: record-level access control.
- `database/migrations/`: how the data model evolved and which database rules matter.
- `database/seeders/`: canonical roles/permissions and demo data.
- `tests/Feature/TenantIsolationTest.php` and `tests/Feature/RolePermissionMatrixTest.php`: examples of critical negative tests.
- `docs/module-maturity-inventory.md` and `Production MVP Road Map.md`: honest module status and production priorities.

## 12. Suggested first portfolio scope

For a student portfolio, ship this small but complete version:

```text
Authentication + Roles
Organisation structure
Employee CRUD + private document upload
Attendance check-in/out
Leave request and manager approval
Basic monthly payroll demo
Audit log + tests + deployment
```

That scope demonstrates Laravel, relational modelling, authorization, transactions, file security, testing, and deployment. It is much stronger than attempting every BizHR screen with incomplete business logic.

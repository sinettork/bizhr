# BizHR — Project Audit

**Stack:** Laravel 13 / Livewire 4 / Volt  
**Last updated:** 29 July 2026  
**Scope:** application code in `app/`, `routes/`, `resources/views/`, and `database/`. Dependency folders are excluded.

## 1. Current phase status

| Module | Status | Notes |
|---|---:|---|
| Company settings | 🟡 Stabilization | Route permission added; action authorization and UX tests remain |
| Branches | 🟡 Stabilization | Route permissions added; business-rule tests remain |
| Departments | 🟡 Stabilization | Route permissions added; relational validation tests remain |
| Job positions | 🟡 Stabilization | Route permissions added; relational validation tests remain |
| Employment types | 🟡 Stabilization | Route permission added; automated tests remain |
| Work shifts | 🟡 Stabilization | CRUD exists; conflict and overnight-shift tests remain |
| Employee schedules | 🟡 Stabilization | CRUD exists; conflict, rest-day and team-scope tests remain |
| Employees | 🟡 Stabilization | Core CRUD exists; ownership, team scope and sensitive-field review remain |
| Employee documents | 🟡 Stabilization | Ownership/company/sensitive-data checks strengthened; audit logging and retention remain |
| Employment history | 🟡 Stabilization | Salary access and company references strengthened; immutable history/audit policy remains |
| Roles and permissions | 🟡 Stabilization | Canonical roles and safe assignment transfer implemented; permission tests remain |
| Users | 🟡 Stabilization | Role assignment exists; role normalization and audit logging remain |
| Attendance check-in/out | 🟡 Stabilization | Workflow exists; concurrency and schedule/leave tests remain |
| Attendance corrections | 🟡 Stabilization | Review exists; audit log and payroll-lock behavior remain |
| Attendance reports | 🟡 Stabilization | Page exists; scope, currency/export and performance tests remain |
| Attendance QR/GPS scan | 🟡 Stabilization | Implemented; HTTPS/mobile camera, replay and concurrency tests remain |
| Leave types | 🟡 Stabilization | CRUD exists; policy and protected-history tests remain |
| Leave requests | 🟡 Stabilization | Two-stage approval and calendar-aware cross-year calculation implemented; automated tests remain |
| Leave balances | 🟡 Stabilization | Implemented; accrual, carry-forward and transactional reconciliation need tests |
| Payroll | 🟡 MVP stabilization | Configurable policy, currency, holidays, OT approval, exception review, calculation and payslips exist; automated tests remain |
| Performance management | ⏳ Not started | Planned after payroll MVP |
| Task management | ⏳ Not started | Planned |
| Recruitment and onboarding | ⏳ Not started | Planned |
| Training | ⏳ Not started | Planned |
| General document management | ⏳ Not started | Employee documents already exist |
| Asset management | ⏳ Not started | Planned |
| Expense reimbursement | ⏳ Not started | Planned |
| Announcements | ⏳ Not started | Planned |
| General reports | ⏳ Not started | Attendance reports already exist |
| Audit logs | 🟡 Stabilization | Append-only automatic tracking and protected viewer implemented; verification tests remain |

No old phase is currently certified production-ready. A module is only
marked complete after authorization, business-rule, UI, automated-test,
log, and local-runtime verification all pass.

## 2. Production audit findings (29 July 2026)

### Fixed in this audit pass

- Added route-level permissions for company settings, branches,
  departments, positions, employment types, employee creation/profile,
  employee documents/history, and attendance scanning.
- Restricted employee documents and employment history by ownership,
  company, sensitive-data permission, and role.
- Blocked managers from broad access to private employee documents and
  salary-bearing employment history.
- Validated that branch, department and position references saved in an
  employment-history record belong to the employee's company.
- Added missing-file protection for employee document downloads.
- Replaced English success messages in the two reviewed employee
  controllers with Khmer messages.
- Implemented `pending -> manager_approved -> approved` leave workflow.
- Restricted manager review to the manager's own department and blocked
  self-approval.
- Restricted final approval to HR, Owner, or Super Admin roles.
- Calculated leave from employee schedules, rest days, weekends, and
  company public holidays.
- Split cross-year requests across the correct annual leave balances.
- Added transactions and row locks to prevent duplicate requests and
  concurrent balance deduction errors.
- Rechecked the working-day total at final approval so a changed schedule
  cannot silently change payroll or leave deductions.
- Normalized roles to `Owner`, `HR Administrator`, `Manager`,
  `Accountant`, `Employee`, and `Super Admin`.
- Added safe alias transfer so existing user assignments move before old
  duplicate roles are deleted.
- Centralized permission ownership between permission, role, and payroll
  seeders.
- Removed test-account creation from the normal production database
  seeding path.
- Added append-only audit records with actor, before/after values, route,
  IP address, browser, request ID, and HMAC checksum.
- Added automatic observers for company structure, employees, documents,
  schedules, attendance, leave, payroll, roles, and permissions.
- Added a Khmer, responsive, dark-mode audit viewer protected by
  `audit.view`.
- Verified that old operational pages consistently include responsive
  layouts, dark mode, loading states, confirmation prompts, and
  horizontal overflow handling where tables require it.
- Added model-level authorization backstops for company, branch,
  department, position, and employment-type create/edit/delete actions
  so Livewire calls cannot bypass route permissions.
- Added company payroll settings for USD/KHR exchange rate, standard
  working days, hours per day, overtime multiplier, overtime approval,
  and unpaid-absence deductions.
- Replaced hard-coded payroll conversion and fallback values with the
  saved company policy.
- Added public-holiday management shared by leave and payroll
  calculations.
- Added manager/HR overtime approval and rejection with department and
  company scoping, self-approval protection, notes, and audit history.
- Added a payroll exception queue showing the attendance or schedule
  issues that must be corrected before final approval.
- Payroll approval remains blocked while exception records exist.

### Confirmed high-priority gaps

1. Dedicated tests for HR, attendance, leave, payroll, documents,
   authorization and security are still missing.
2. PostgreSQL production compatibility, queues, Redis, cloud storage,
   backups, monitoring and deployment hardening are not verified.

## 3. Issues resolved from the previous audit

### Leave Balances

The previous `balances.blade.php` file was empty. It now includes:

- Balance initialization for every active employee and active leave type
- Year selection
- Employee and leave-type filters
- Opening, earned, used, adjusted and remaining totals
- Synchronization with approved leave requests
- HR adjustment workflow
- Khmer validation and interface
- Permission checks for `leave.report` and `leave.manage`
- Responsive table and dark-mode styling

Route:

```text
/leave/balances
```

### Attendance QR/GPS

The previous scanner was only a placeholder. It now includes:

- Camera QR detection
- Manual payload fallback
- GPS collection
- Branch QR-token validation
- Branch enable/disable validation
- Employee-to-branch validation
- Active-employee validation
- Allowed-radius validation using the Haversine formula
- Automatic check-in on first scan
- Automatic check-out on second scan
- Duplicate attendance protection
- IP address, browser agent, QR token, GPS coordinates and distance recording
- Correct use of the existing `work_date` database column

Route:

```text
/attendance/scan
```

## 4. CRUD verification

| Module | Create | Read | Update | Delete/Deactivate |
|---|---:|---:|---:|---:|
| Branches | ✅ | ✅ | ✅ | ✅ |
| Departments | ✅ | ✅ | ✅ | ✅ |
| Positions | ✅ | ✅ | ✅ | ✅ |
| Employment types | ✅ | ✅ | ✅ | ✅ |
| Work shifts | ✅ | ✅ | ✅ | ✅ |
| Schedules | ✅ | ✅ | ✅ | ✅ |
| Employees | ✅ | ✅ | ✅ Core profile | Deactivation preferred |
| Leave types | ✅ | ✅ | ✅ | ✅ Protected |
| Leave requests | ✅ | ✅ | Approval workflow | N/A |
| Leave balances | Initialize | ✅ | ✅ Adjustment/sync | Historical records retained |
| Roles | ✅ | ✅ | ✅ | ✅ Protected |
| Users | Existing-user management | ✅ | ✅ Roles | No permanent delete |

## 5. Local verification checkpoint

Validated on 29 July 2026:

- ✅ Drive and local source copies synchronized
- ✅ Laravel caches cleared successfully
- ✅ Database migrations are current
- ✅ All 91 application routes register successfully
- ✅ `/attendance/scan` route registers successfully
- ✅ `/leave/balances` route registers successfully
- ✅ Existing automated suite passes: 33 tests, 81 assertions

The existing tests mainly cover authentication, settings and the
dashboard. Dedicated HR, attendance, leave and payroll tests still need
to be added.

### Commands used

Run these commands from the fast local project:

```powershell
cd D:\www\bizhr

php artisan optimize:clear
php artisan migrate
php artisan route:list
php artisan test
```

Manual pages to test:

```text
/attendance/scan
/leave/balances
/employment-types
/work-shifts
/schedules
/leave/types
/roles
/users
```

## 6. Recommended next development order

1. Complete authorization/ownership/company/team scope across old modules
2. Correct leave calendar and two-stage approval business logic
3. Normalize roles safely without losing user assignments
4. Complete payroll exception, overtime and policy workflows
5. Add audit logs for all sensitive HR actions
6. Add automated business-rule and authorization tests
7. Standardize old screens against the `/schedules` UX/UI template
8. Complete employee profile/deactivation and reports/exports
9. Verify PostgreSQL and production deployment requirements
10. Only then start non-MVP phases

## 7. Phase completion rule

A phase is complete only when:

- Database migration is correct
- Model relationships work
- Validation messages are available in Khmer
- Permissions are enforced
- Desktop and mobile layouts work
- Dark mode works
- Automated tests pass
- No Laravel log errors remain
- A Git commit is created

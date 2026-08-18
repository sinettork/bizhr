# BizHR module maturity inventory

**Status:** Phase 0 working inventory  
**Updated:** 15 August 2026  
**Route baseline:** 208 registered routes

This inventory prevents a data-backed directory from being mistaken for a complete production workflow. Maturity labels are:

- **Operational:** dedicated server workflow exists and has automated coverage; domain/UAT verification may still be required.
- **Partial:** meaningful workflow exists, but production controls or lifecycle steps remain incomplete.
- **Directory only:** active route uses `StandardPageController`; it supports authorized search/filter/read but not complete CRUD or workflow transitions.
- **Legacy candidate:** a separate Flux/Volt page exists in the repository but is not the active Bootstrap route. It must not be deleted until dependency and behavior comparison is complete.

## Active modules

| Module | Active implementation | Maturity | Phase 0/next action |
|---|---|---|---|
| Dashboard | `DashboardController`, configurable Bootstrap dashboard | Operational/partial | Permission-scoped metrics, action queues, movable/hideable widgets and persistence are active; verify metric definitions and query performance at representative volume. |
| Company settings | `OrganizationController` | Operational/partial | Logo, legal/local names, locale, time zone, currency, fiscal/work week and formats are editable; add identifier verification policy. |
| Branches | `OrganizationController` | Operational/partial | Create/update/delete with company scope and referenced-record protection; add effective dating. |
| Departments | `OrganizationController` | Operational/partial | Create/update/delete with company scope and referenced-record protection; add effective dating. |
| Positions | `OrganizationController` | Operational/partial | Create/update/delete with company scope and referenced-record protection; add effective salary/reporting history. |
| Employment types | `OrganizationController` | Operational/partial | Create/update/delete with company scope and referenced-record protection; add effective dating. |
| Employees | `EmployeeController` | Operational/partial | Lifecycle changes, automatic history, separation deprovisioning and privately authorized profile images are active; add reinstate/rehire approval and full effective dating. |
| Employee documents | `EmployeeDocumentController` | Operational/partial | Verification, versioning, revocation, expiry state, private downloads and centralized malware scanning are active; add document-type requirements, reminders and retention. |
| Employment history | `EmploymentHistoryController` + `EmployeeController` | Operational/partial | Hire and controlled assignment/salary/status edits generate reasoned history automatically; add formal approval workflow. |
| Employment contracts | `EmploymentContractController` | Partial | Complete amendment/version/signed-snapshot and maker-checker controls. |
| Work shifts | `WorkShiftController` | Operational/partial | Remove duplicate toolbar source; add effective dating and policy conflict coverage. |
| Employee schedules | `EmployeeScheduleController` | Operational/partial | Add bulk roster/copy/publish/conflict controls. |
| Attendance | `AttendanceController` and QR controller | Partial | Harden event provenance, replay/anomaly policy, locks and reconciliation. |
| Attendance corrections | `AttendanceCorrectionController` | Operational/partial | Self-approval, concurrent review, immutable before/after audit and payroll-period locks are enforced; add authorized reopen and notifications. |
| Attendance reports | `AttendanceReportController` | Partial | Validate metrics/export totals and performance at representative volume. |
| Leave types | `LeaveTypeController` | Operational/partial | Add versioned/effective-dated eligibility and accrual policies. |
| Leave requests | `LeaveRequestController` | Operational/partial | Add cancel/recall/delegation/escalation/team coverage and notification workflow. |
| Leave balances | `LeaveBalanceController` | Operational/partial | Add projected balance, reconciliation/locking and remove duplicate toolbar source. |
| Payroll periods/settings/review/payslips | `PayrollController` | Partial/high risk | Domain-verify formulas, snapshots, maker-checker, reconciliation, reopening and immutable close. |
| Tasks | `TaskController`, `TaskWorkflowService` | Operational/partial | Assign, edit, progress, submit, maker-checker verify/return and reasoned cancellation are active; add event history, notifications and overdue escalation. |
| Performance | `PerformanceController`, `PerformanceReviewService` | Operational/partial | KPI weights, goals, review snapshots, manager scoring, HR maker-checker approval, employee acknowledgment, close and controlled reopen are active; add calibration, appeals and cycle scheduling. |
| Recruitment | `RecruitmentController`, `RecruitmentWorkflowService` | Operational/partial | Vacancy/candidate creation, private CV storage and guarded candidate stages are active; add requisition approval, interviews, offers, retention/anonymization and hire conversion. |
| Training | `TrainingController` | Operational/partial | Course CRUD/archive guard, assignment, progress, assessment score and retained enrollment history are active; add sessions, capacity, certificates, expiry and compliance automation. |
| Assets | `AssetController`, `AssetWorkflowService` | Operational/partial | Asset CRUD/archive guard and assign/transfer/return/lost/retired lifecycle are active; add repairs, write-off approval, attachments and stocktake. |
| Announcements | `AnnouncementController` | Operational/partial | Scheduling, urgent/pinned notices, company/branch/department targeting and idempotent acknowledgments are active; add attachments, delivery notifications and escalation. |
| Expenses | `ExpenseController` | Operational/partial | Submission, private receipt, separated manager/accounting approval and payment are active; add draft/return, policy and reconciliation controls. |
| Users and roles | `AccessAdministrationController` | Operational/partial | Provision/deactivate, password setup/reset, role assignment, employee link, protected system roles, immediate inactive-session enforcement, session revocation and access-review signals are active; add temporary/scoped access and MFA policy enforcement. |
| Audit logs | `AuditLogController` | Operational/partial | Immutable event filtering, actor/target/request correlation and before/after inspection are active; add cryptographic verification command, retention and governed export. |
| Imports | `BulkImportController` | Partial | Add job processing, rollback strategy, mapping/versioning and full row-error export. |
| Exports | `DataExportController` | Partial | Verify private storage, expiry cleanup, large queued exports and authorization. |

## Active directory-only routes

These routes are useful read views, but they are **not production-complete modules**:

| Route | Domain | Current capability | Required replacement |
|---|---|---|---|
| `/payroll/statutory-profiles` | Payroll | Authorized directory | Dedicated employee statutory profile workflow with versioned rules and validation. |
| `/payroll/reports` | Payroll | Authorized directory | Reconciled reports and exports tied to approved immutable payroll snapshots. |

The UI must describe these screens as directories/reporting views until their dedicated workflow replaces the generic controller. Do not add misleading Create/Edit/Delete buttons to them.

## Legacy cleanup completed

The unreferenced Flux/Volt-style module pages and obsolete alternate layouts were removed after route, component, compile and smoke-test verification. Active authentication views, the current Bootstrap app/auth layouts, contract views, and the read-only standard directory remain.

Phase 0 cleanup procedure for each candidate:

1. Search routes, component discovery, tests, imports/includes and dynamic view references.
2. Compare unique domain behavior against the active implementation.
3. Port any required validation/workflow/test coverage.
4. Delete only after route and full-suite verification.
5. Remove packages/build assets only after the final dependent page is gone.

## Immediate Phase 0 evidence backlog

- [x] Make PHPStan green without adding suppressions; the baseline was removed.
- [x] Add route authorization matrix for anonymous, inactive, insufficient-role, own-record and cross-company access across the active P0 surfaces; continue extending it with each new route.
- [ ] Replace every directory-only module with a dedicated workflow or explicitly retain it as read-only.
- [ ] Remove duplicate active toolbar markup from source.
- [x] Resolve legacy candidate pages and remove confirmed dead files while retaining active Flux dependencies used by passkeys/authentication.
- [x] Verify private HR documents, contracts, CVs, receipts, exports and profile images for owner/own-record/cross-company access; legacy public profile images were migrated to authorized private storage.
- [ ] Run staging backup/restore and deployment/rollback rehearsal.
- [ ] Record UAT owner and acceptance script for each operational module.

The current automated evidence and outstanding sign-offs are recorded in `docs/production-readiness-ledger.md`.

This file is updated when evidence changes; maturity is never raised based only on a page rendering successfully.

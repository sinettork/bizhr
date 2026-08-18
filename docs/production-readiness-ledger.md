# BizHR production readiness ledger

**Updated:** 15 August 2026  
**Rule:** a phase is not complete until every exit-gate item has evidence and an accountable signer.

## Verified in the current workspace

| Control | Evidence | Result |
|---|---|---|
| Formatting | Laravel Pint | Pass |
| Static analysis | PHPStan level 7, no baseline suppression | 0 errors |
| Automated regression | Pest suite | 262 tests / 954 assertions passed |
| Database migrations | `php artisan migrate:status` against configured PostgreSQL database | All migrations ran |
| Dependency security | `composer audit --locked --no-interaction` | No advisories on the last verified run; current external recheck requires approval to share dependency metadata with Composer's advisory service. |
| Production compilation | config, route and Blade caches | Pass |
| Scheduled operations | scheduler inspection | Backup and expired-export cleanup registered |
| Route maturity | `docs/module-maturity-inventory.md` | 208 routes classified; only two explicit payroll directories remain generic |
| Frontend supply chain | Vite production build plus npm audit | Bootstrap, Font Awesome, HTMX, QR and passkey runtimes are self-hosted; 0 npm vulnerabilities |

## Implemented workflow evidence

- Organization CRUD with company scope and referenced-record protection.
- Employee create/update/separation, automatic employment history and linked-account deprovisioning.
- Private employee documents with versioning, verification, revocation and expiry state.
- Attendance corrections with concurrency control, immutable before/after audit and payroll locks.
- Expense submission, private receipts, separated manager/accounting approval and payment.
- User/role administration, protected system accounts, reset links, session revocation and access-review signals.
- Recruitment vacancy/candidate pipeline with private CVs and guarded transitions.
- Performance goals/reviews with weighted snapshots, manager scoring, maker-checker HR approval, employee acknowledgement, close and controlled reopen.
- Announcements with scheduling, audience security and idempotent acknowledgement.
- Task assign/edit/progress/verify/return/cancel workflow.
- Training create/edit/assign/progress/score/archive workflow with retained history.
- Asset create/edit/assign/return/archive workflow with retained assignment history.
- Page-specific action bars, live server search, persisted columns/dashboard widgets and paginated tables.
- User avatars and employee profile photos are private, served only by authorized endpoints; the legacy public image migration is idempotent and has been executed on the configured database/storage.
- Inactive accounts are rejected both at login and on every protected request, with session invalidation.
- Every active upload entry point invokes a central security scanner; production fails closed without a working ClamAV scanner and security logs retain only file fingerprint/size/outcome metadata.
- Explicit model policies cover employees, sensitive documents, contracts, payroll periods, overtime review, leave, expenses, assets and recruitment; high-risk controller actions enforce them and cross-company policy tests pass.
- Bootstrap, Font Awesome, HTMX, QR generation and passkey JavaScript are built or copied from locked npm dependencies; active pages no longer execute CDN JavaScript or CSS.

## Phase 0 external exit gate — not yet signed

| Required evidence | Owner | Environment | Status |
|---|---|---|---|
| Encrypted database and private-file backup created | DevOps | Staging | Pending |
| Restore into an isolated staging database and file store | DevOps/DBA | Staging | Pending |
| Previous release redeploy and migration rollback rehearsal | DevOps | Staging | Pending |
| Browser acceptance: login, HR CRUD, attendance, leave, payroll, private downloads, live search and pagination | QA | Supported browsers/devices | Pending |
| Representative-volume p95 list/write measurements | QA/Engineering | Staging | Pending |
| Route/module inventory approval | Product/HR/Engineering | Review | Pending |

## Phase 1 external exit gate — not yet signed

| Required evidence | Owner | Status |
|---|---|---|
| Pilot HR administrator completes hire, transfer, salary change and separation script | HR Product Owner | Pending |
| Required-document and contract policy is approved | HR/Legal | Pending |
| Role matrix and sensitive-field access are approved | Security/HR | Pending |
| Khmer/English terminology and printable outputs are approved | HR/Localization | Pending |
| WCAG 2.2 AA keyboard/screen-reader review | QA/Accessibility | Pending |

## High-risk domain gate before payroll production use

Payroll must remain non-production until a Cambodian payroll/accounting owner validates statutory formulas, effective dates, rounding, taxable bases, NSSF/tax configuration, overtime treatment, maker-checker approval, immutable snapshots, reconciliation totals and payslip output using a signed acceptance dataset. Code tests cannot replace this legal/domain approval.

## Release decision

**Current decision: NO-GO for real employee or payroll data.** The code gate is green, but staging recovery, domain validation and user acceptance remain mandatory. Follow `docs/production.md` and `docs/supabase.md`; never run demo seeders in production.

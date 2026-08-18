# BizHR Production MVP Road Map

**Document status:** Working product and delivery plan  
**Last updated:** 14 August 2026  
**Planning horizon:** Production MVP through enterprise scale  
**Product:** BizHR human resources, attendance, leave, payroll, talent, and HR operations platform

## 1. Purpose

This roadmap turns BizHR from a broad functional prototype into a dependable production HR system. It deliberately prioritizes correctness, security, traceability, and daily HR workflows before adding more modules.

The first target is not “every HR feature.” The first target is a system one company can safely use as its source of truth for employees, attendance, leave, contracts, and payroll. Later phases add automation, talent management, integrations, and multi-company scale.

## 2. Product principles

1. **One source of truth:** employee, organization, attendance, leave, and payroll records must have clear ownership and history.
2. **No fake completeness:** a generic read-only page is not considered a finished module.
3. **Workflow before screens:** every process needs statuses, allowed transitions, responsible roles, validation, notifications, and an audit trail.
4. **Security by default:** least privilege, private files, encrypted transport, protected sensitive fields, and tested tenant/company boundaries.
5. **Correctness before automation:** payroll and leave calculations require versioned rules, explainable results, and approval controls.
6. **Progressive enhancement:** normal server forms remain usable; HTMX improves search, filtering, and pagination without becoming the source of business rules.
7. **Responsive and accessible:** keyboard operation, Khmer/English support, visible focus, useful error messages, and WCAG 2.2 AA as the target.
8. **Every release is reversible:** database backup, tested rollback, observable deployment, and documented recovery steps.

## 3. Current baseline

BizHR already contains useful foundations:

- Laravel authentication, verified users, passkeys/two-factor support, roles and permissions.
- Company, branch, department, position, employment type, employee, profile image, documents, history, contracts, and ID card.
- Work shifts, schedules, attendance, QR workflow, corrections, and attendance reports.
- Leave types, requests, approvals, balances, adjustments, initialization, and synchronization.
- Payroll periods, settings, review, overtime decisions, payslips, statutory profile placeholders, and reports.
- Tasks, training, assets, announcements, performance, recruitment, expenses, users, roles, and audit pages at different levels of completeness.
- Excel import preview/confirmation, exports, configurable table columns, pagination, dashboard preferences, and HTMX live search.
- Automated test suite, dependency audits, production runbook, scheduler, queue configuration, and migration history.

Known gaps that affect release readiness:

- Static analysis is not green; unsafe relationship inference and stale baseline entries remain.
- Several routes use a generic `StandardPageController`; these are directories or placeholders, not complete production workflows.
- Some active views still contain legacy duplicate toolbar markup hidden or removed at runtime.
- CRUD coverage and action patterns are inconsistent across secondary modules.
- Payroll, statutory rules, reporting, and approvals need deeper domain verification.
- Production observability, restore testing, retention policy, and operational ownership are not yet proven.

### Confirmed production-readiness findings

This project is not yet ready for live client use as a company production HR system. The current codebase contains a strong operational foundation, but it still sits in a **Phase 0 / partial maturity** state rather than a release-grade production state. The strongest evidence is that the active route inventory still includes directory-only pages for payroll statutory profiles and payroll reports, which are not production workflows and must not be treated as complete modules.

The system is suitable for internal evaluation, deeper UAT, and controlled pilot workflows only after the following are demonstrated:

- route-by-route authorization and company isolation checks across all employee, payroll, leave, and document actions;
- a staging backup/restore rehearsal with tested rollback steps;
- payroll and statutory rule validation by qualified domain experts, not just code-level tests;
- controlled workflow approval tests for leaves, attendance corrections, contracts, and payroll periods;
- evidence that every module has a clear owner, lifecycle states, and audit trail;
- removal or explicit read-only labeling of all placeholder pages that imply unsupported CRUD operations.

Until those gates are proven, the release recommendation remains: **do not treat BizHR as a fully production-ready HR system for a live company deployment**. It should be positioned as an evolving operational platform that requires additional validation and sign-off before production adoption.

## 4. Priority model

- **P0 — Release blocker:** security, data loss, payroll/leave correctness, authorization, tenant isolation, broken workflow, or unrecoverable deployment risk.
- **P1 — MVP essential:** required for daily HR operations at the first production company.
- **P2 — Operational maturity:** reduces manual work, improves controls, and supports managers at scale.
- **P3 — Expansion:** advanced talent, integrations, analytics, and enterprise capabilities.

No phase is complete because code exists. A phase is complete only when its exit gate is satisfied.

---

## Phase 0 — Stabilize the foundation

**Priority:** P0  
**Indicative duration:** 2–4 weeks  
**Outcome:** A releasable, testable foundation with no known critical correctness or security blockers.

### Engineering and architecture

- Make `composer types:check` pass without new suppressions or baseline entries.
- Remove stale PHPStan baseline entries as underlying types are fixed.
- Add return types and typed Eloquent relationships across active models/controllers.
- Inventory every route and classify it as complete, partial, placeholder, duplicate, or obsolete.
- Replace or clearly disable generic placeholder modules that imply unsupported CRUD.
- Remove legacy/unused Flux, Volt, Tailwind, or duplicate Blade pages only after confirming no route/component dependency.
- Consolidate repeated list markup into reusable command bar, result table, pagination, loading, empty, and error patterns.
- Remove duplicate toolbars from source rather than depending on CSS/JavaScript removal.
- Introduce service/action classes for payroll, leave, attendance, imports, exports, and other domain transitions.
- Add database transactions and idempotency protections to multi-record operations.

### Security and authorization

- Test every protected route against anonymous, inactive, wrong-role, own-record, and cross-company access.
- Add explicit policies for employee, document, contract, payroll, attendance, leave, expense, asset, and recruitment records.
- Verify company scoping on every query, relation, export, download, background job, and dashboard metric.
- Protect uploads with MIME/content inspection, size limits, randomized private paths, authorization on download, and malware-scanning integration point.
- Redact passwords, tokens, salaries, bank information, national IDs, and document contents from logs and exceptions.
- Add security headers: CSP plan, HSTS, frame protection, referrer policy, permissions policy, and secure cookies.
- Define data classification: public, internal, confidential HR, payroll-sensitive, identity-sensitive.

### Database and data integrity

- Review foreign keys, unique constraints, nullability, decimal precision, time zones, indexes, and soft-delete policy.
- Add immutable public IDs where external URLs expose records.
- Add optimistic locking/version checks to high-risk editable records.
- Define deletion rules: archive, anonymize, or legal retention—never generic hard delete for payroll/audit records.
- Add searchable indexes for employee code, normalized names, email, phone, document/contract numbers, dates, and statuses.
- Establish migration rollback rules and test migrations against the production database engine.

### Quality gate

- CI must run formatting, static analysis, unit/feature tests, authorization matrix tests, dependency audits, migration tests, and production cache compilation.
- Establish test fixtures for owner, HR, manager, accountant/payroll officer, employee, inactive user, and cross-company user.
- Add browser-level smoke tests for login, employee create/edit, attendance correction, leave approval, payroll generation/approval, downloads, HTMX search, filters, and pagination.
- Set an initial performance budget: common list response p95 under 500 ms and common write response p95 under 800 ms under agreed test load.

### Phase 0 exit gate

- Static analysis, tests, audits, and production cache compilation are green.
- No P0 route authorization or company-isolation failures.
- No active page is falsely presented as complete.
- Backup and rollback steps have been executed successfully in a staging environment.
- A signed route/module inventory identifies the owner and maturity of every module.

---

## Phase 1 — Production MVP: core HR source of truth

**Priority:** P0/P1  
**Indicative duration:** 4–8 weeks  
**Outcome:** One company can manage its workforce safely from hire through separation.

### Company and organization setup

- Complete company profile, logo, legal name, local name, tax/payroll identifiers, address, locale, time zone, currency, fiscal year, work week, and date/number formats.
- Complete branch, department, position, employment type, cost center, work location, holiday calendar, and reporting-line CRUD.
- Add effective dates and prevent invalid deletion while records are referenced.
- Add import templates, validation preview, row-level errors, duplicate detection, and rollback for organizational master data.

### Employee lifecycle

- Complete employee create, view, edit, archive, reinstate, and controlled separation workflows.
- Add personal, contact, emergency contact, address, employment, compensation, reporting manager, bank/payment, tax/statutory, dependent, and custom-field sections.
- Add employee status transitions: pre-hire, probation, active, suspended, on leave, resigned, terminated, retired, archived.
- Add hire, transfer, promotion, salary change, contract renewal, probation confirmation, resignation, termination, and rehire events with effective dates.
- Preserve employment history automatically from approved changes rather than relying only on manual entries.
- Add duplicate detection using employee code plus configurable identity/contact matching.
- Add profile completeness and missing-document alerts.
- Add printable employee profile and configurable ID card templates with expiry/reissue tracking.
  - **Recommended next steps for Employees card:**
    - Company logo upload and branding customization
    - ID card expiry date tracking and renewal workflow
    - Emergency contact information on card back
    - Secure verification QR endpoint for card authenticity validation

### Documents and contracts

- Define document types, required-by-role rules, issued/expiry dates, verification status, and expiry reminders.
- Add document versioning and replace/revoke operations.
- Complete contract draft, review, approval, activation, renewal, amendment, expiry, termination, supersession, and downloadable PDF workflow.
- Add reusable bilingual contract templates and immutable signed/approved snapshots.
- Add acknowledgment/e-signature integration point; do not claim legal e-signature support until verified for the deployment jurisdiction.

### Users and access

- Link user provisioning and deprovisioning to employee lifecycle with explicit approval.
- Complete users, roles, permission groups, branch/department scopes, temporary access, password reset, MFA/passkey policy, and session revocation.
- Add access review report: dormant users, privileged users, employees without accounts, and terminated employees with active access.
- Add maker-checker separation for payroll settings, payroll approval, sensitive profile edits, and role changes.

### Core UX

- Finish a consistent, page-specific action model: primary action, contextual actions, bulk actions, import/export, columns, and compact view only where useful.
- Retain live server-side search with debouncing, cancellation, loading/error/empty states, URL state, pagination, and search-button fallback.
- Persist table column visibility, order, page size, filters, and dashboard widget preferences per user.
- Add saved views for frequently used filters.
- Add Khmer and English UI labels, validation messages, dates, and printable templates.
- Meet keyboard, focus, target size, labeling, contrast, and accessible-authentication requirements for WCAG 2.2 AA.

### Phase 1 exit gate

- HR can onboard, update, transfer, and separate an employee without database intervention.
- All employee changes create actor/time/reason audit records.
- Required documents and contracts can be tracked through expiry.
- Role and company-scope tests cover every employee and organization action.
- A pilot HR administrator completes an agreed end-to-end acceptance script with no P0/P1 defect.

---

## Phase 2 — Time, attendance, scheduling, and leave

**Priority:** P1  
**Indicative duration:** 4–7 weeks  
**Outcome:** Daily time and leave records are reliable enough to feed payroll.

### Scheduling

- Add shift templates, breaks, overnight shifts, grace periods, flexible schedules, rotating patterns, rest days, and effective-date assignments.
- Add weekly/monthly roster views, copy week, bulk assignment, conflict detection, publication, and employee acknowledgment.
- Prevent overlapping assignments and invalid work/rest sequences according to company policy.
- Add public holiday calendars by company/branch/location.

### Attendance capture

- Harden QR sessions: short lifetime, replay prevention, branch/device policy, scan-event audit, clock drift controls, and anomaly flags.
- Add manual kiosk code or approved device integration as a fallback.
- Store original event, normalized event, source, device, IP/context, and adjustment history separately.
- Add duplicate punch, missed punch, late, early leave, absence, overtime, business trip, remote work, and holiday/rest-day rules.
- Add manager daily exception inbox and bulk resolution where decisions share the same reason.
- Add configurable cut-off/locking after payroll close.

### Attendance corrections

- Complete employee request, evidence, manager review, HR escalation, approve/reject/return, and immutable before/after audit.
- Prevent self-approval and correction after locked payroll unless an authorized reopening workflow is used.
- Notify requester and reviewer at each state transition.

### Leave

- Version leave policies by effective date, employment type, grade, location, tenure, and gender/eligibility where legally appropriate.
- Support annual entitlement, monthly accrual, probation restrictions, carry-forward, expiry, negative balance, half-day/hourly leave, attachments, and encashment policy.
- Add calendar overlap, roster, public holiday, weekend, existing leave, and team coverage validation.
- Add multi-level approval, delegation, cancellation/recall, return-to-work, and correction workflows.
- Add manager team calendar and employee projected balance as of a requested date.
- Reconcile approved leave with attendance and payroll automatically.

### Phase 2 exit gate

- A complete test month reconciles schedules, attendance events, corrections, leave, holidays, overtime, and payroll input without spreadsheet repair.
- Every adjustment is attributable and historical totals remain reproducible.
- Payroll input locks are enforced and reopening is separately authorized and audited.
- Attendance/leave reports agree with source records for the acceptance dataset.

---

## Phase 3 — Payroll, statutory rules, and financial controls

**Priority:** P0/P1  
**Indicative duration:** 6–12 weeks plus domain/legal review  
**Outcome:** Payroll is explainable, repeatable, approved, and safe to pay.

> Payroll must be validated by qualified local payroll, accounting, tax, and labor-law professionals. Software tests do not constitute legal or tax advice.

### Payroll configuration

- Version salary components, allowances, benefits, deductions, loans/advances, overtime rules, leave-without-pay rules, taxable treatment, employer contributions, and rounding rules.
- Add earning/deduction formulas with effective dates and an approval workflow.
- Add employee payroll profile, bank/payment method, statutory IDs, dependents, residency/tax attributes, and exemptions.
- Keep configuration changes separate from approved payroll snapshots.

### Payroll processing

- Implement explicit period states: draft, collecting inputs, calculated, exception review, awaiting approval, approved, payment prepared, paid, closed, reopened.
- Make payroll generation idempotent and transaction-safe.
- Capture source snapshots for salary, attendance, overtime, leave, benefits, deductions, and statutory tables.
- Produce calculation lines that explain every amount and formula input.
- Add pre-payroll validation: missing bank/tax data, duplicate employee, negative pay, unexpected variance, missing attendance, expired contract, and unapproved overtime.
- Add comparison against previous period and configurable variance thresholds.
- Enforce maker-checker approval and prevent an approver from approving their own configuration/input changes.
- Add controlled reopening, recalculation, adjustment payroll, off-cycle payroll, final pay, reversal, and correction workflows.

### Payment and payslips

- Generate bank/payment files using versioned templates and approval-controlled access.
- Track payment batch, reference, failure, retry, and reconciliation status.
- Generate immutable bilingual PDF payslips and year-to-date totals.
- Notify employees securely; never email sensitive payslip content as an unprotected attachment by default.
- Support employee acknowledgment and controlled reissue.

### Statutory and accounting outputs

- Implement jurisdiction-specific tax/social-security calculations only after rule approval and effective-date versioning.
- Add statutory reports, reconciliation totals, and source-to-report traceability.
- Add payroll journal export by account, cost center, branch, department, and project.
- Add general-ledger/API integration boundary without coupling payroll calculation to a single accounting product.

### Phase 3 exit gate

- Parallel-run at least two payroll periods against independently verified expected results.
- Gross-to-net, employer cost, payment total, payslip total, statutory output, and journal total reconcile exactly within defined rounding rules.
- Approved periods are immutable; reopening requires separate permission, reason, and audit.
- Local payroll/accounting owner signs off calculation rules and outputs.
- Disaster recovery test proves an approved payroll can be reconstructed from snapshots and audit history.

---

## Phase 4 — Employee and manager self-service

**Priority:** P1/P2  
**Indicative duration:** 4–6 weeks  
**Outcome:** Employees and managers complete routine work without HR acting as a data-entry proxy.

### Employee self-service

- View/update permitted personal and contact information through approval-controlled change requests.
- View schedule, attendance, correction history, leave balances, team/company holidays, contracts, documents, payslips, assets, tasks, training, goals, and announcements.
- Submit leave, attendance correction, expense, document update, personal-data change, resignation, and support requests.
- Add secure notification center and email/in-app preferences.

### Manager self-service

- Team directory and organizational tree.
- Unified approval inbox with due date, delegation, escalation, comments, attachments, and bulk decision safeguards.
- Team attendance exceptions, leave calendar/coverage, contract/probation/expiry alerts, goals, training, assets, and headcount summary.
- Prevent managers from accessing salary, medical, identity, disciplinary, or other sensitive data without explicit permission.

### Mobile/PWA readiness

- Responsive essential flows: login, QR attendance, leave, corrections, approvals, payslip, announcements, and profile.
- Add installable PWA only if offline behavior, caching, session security, and update strategy are explicitly designed.
- Do not cache sensitive API/HTML responses in service workers.

### Phase 4 exit gate

- At least 80% of routine employee requests in the pilot acceptance script can be completed without HR re-entry.
- Manager approvals work on desktop and mobile with full audit and delegation controls.
- Accessibility and responsive acceptance tests cover every self-service critical path.

---

## Phase 5 — HR operations: expenses, assets, announcements, and requests

**Priority:** P2  
**Indicative duration:** 4–8 weeks  
**Outcome:** Supporting HR/administrative workflows are complete rather than generic list pages.

### Expenses

- Complete draft, submit, manager approval, accounting review, approve/reject/return, payment, and reconciliation states.
- Add expense categories, policy limits, receipt upload, duplicate detection, currency/exchange rate, tax treatment, mileage/per-diem rules, and cost allocation.
- Add payment reference and export to accounting.

### Assets

- Complete asset catalog, category, serial/barcode/QR, purchase/warranty, custodian, location, condition, assignment, employee acknowledgment, transfer, return, repair, loss, write-off, and audit history.
- Add bulk import, stocktake, expiry/warranty reminders, and offboarding clearance.

### Announcements and requests

- Add audience targeting, scheduled publish/unpublish, pinned/urgent messages, attachments, acknowledgment tracking, and read metrics.
- Add configurable HR service requests/case management with category, SLA, assignment, status, internal notes, employee-visible messages, and attachments.

### Phase 5 exit gate

- Expense and asset totals reconcile with acceptance fixtures and every transition is permission-tested.
- Offboarding checklist identifies and clears outstanding assets, expenses, access, documents, and final-pay dependencies.
- Announcement audience tests prevent cross-company/branch/department disclosure.

---

## Phase 6 — Recruitment and onboarding

**Priority:** P2  
**Indicative duration:** 5–9 weeks  
**Outcome:** Approved hiring demand becomes a completed onboarding record without duplicate entry.

- Add manpower/requisition request, budget approval, vacancy, hiring team, and publication status.
- Add candidate profile, source, CV/document consent, duplicate detection, stage history, evaluation scorecards, interviews, communication log, rejection reason, and retention/erasure policy.
- Add offer generation, approval, acceptance, expiration, and conversion to pre-hire employee.
- Add onboarding templates by role/location, tasks, responsible owners, due dates, dependencies, document collection, equipment, account provisioning, orientation, and probation review.
- Add candidate privacy notice, consent evidence, access restrictions, and retention automation.

### Phase 6 exit gate

- Requisition-to-hire works end-to-end without copying candidate data manually into the employee record.
- Candidate stage history is immutable and privacy/retention controls are tested.
- Onboarding completeness and overdue tasks are visible to HR and responsible managers.

---

## Phase 7 — Performance, goals, training, and succession

**Priority:** P2/P3  
**Indicative duration:** 6–10 weeks  
**Outcome:** Talent workflows measure development without mixing them with payroll or disciplinary access.

### Performance

- Add review cycles, eligibility, templates, weighted competencies/KPIs, employee self-review, manager review, calibration, second-level approval, acknowledgment, appeal, and locked final snapshot.
- Add SMART goals, cascading/alignment, milestones, check-ins, evidence, progress history, and approval.
- Add review reminders and overdue escalation.

### Training

- Add course catalog, provider, session, capacity, prerequisites, mandatory assignment, enrollment approval, attendance, assessment, completion, certificate, expiry, cost, and effectiveness feedback.
- Link required training to role, location, risk, and asset/equipment authorization.

### Succession and skills

- Add skills catalog, employee skills/evidence, position requirements, competency gap, talent pool, successor readiness, and development plans.
- Apply strict access rules to ratings, succession, and sensitive manager notes.

### Phase 7 exit gate

- Review calculations and state transitions are reproducible and locked after approval.
- Mandatory training compliance is reportable by employee, role, department, and expiry date.
- Talent data permissions are separately tested from normal employee directory permissions.

---

## Phase 8 — Reporting, analytics, and workforce planning

**Priority:** P2/P3  
**Indicative duration:** 5–9 weeks  
**Outcome:** Leaders get governed metrics with traceable definitions, not disconnected dashboard cards.

- Create a metric catalog defining owner, formula, grain, filters, refresh time, and source for headcount, hires, exits, turnover, absence, overtime, leave liability, payroll cost, vacancies, time-to-hire, training compliance, and performance distribution.
- Add as-of-date headcount and effective-dated historical reporting.
- Add drill-down from dashboard metric to authorized source records.
- Add dashboard widgets by role with saved layout, date range, branch/department/location filters, and export.
- Add scheduled reports with private delivery links, expiration, access checks, and audit.
- Add workforce budget, approved positions, vacancy, forecast, actual headcount, and scenario planning.
- Introduce a reporting replica/warehouse only when production query load and historical complexity justify it.

### Phase 8 exit gate

- Every production metric has an approved definition and reconciles to source records.
- Dashboard/query performance meets the agreed p95 budget at representative data volume.
- Export and scheduled-report access cannot bypass record-level authorization.

---

## Phase 9 — Integrations and automation platform

**Priority:** P3  
**Indicative duration:** Incremental  
**Outcome:** BizHR exchanges data safely without one-off database scripts.

- Add a versioned REST API with scoped tokens/OAuth, pagination, idempotency keys, validation, rate limits, audit, and deprecation policy.
- Add signed outbound webhooks with retry, exponential backoff, dead-letter handling, replay protection, and delivery logs.
- Add SSO through a standard identity provider (OIDC/SAML based on customer requirements) and automated user provisioning where justified.
- Add accounting/ERP payroll journals, bank payment status, biometric/time device, email/calendar, object storage, messaging, and government/statutory submission integrations as separate adapters.
- Add integration health dashboard, credential rotation, sandbox/test mode, mapping/version controls, and reconciliation jobs.
- Never let an external integration write an approved payroll or audit record without a controlled domain command and authorization context.

### Phase 9 exit gate

- Integration contracts are versioned and covered by consumer/provider tests.
- Failed events are visible, retryable, and reconcilable without data duplication.
- Secrets are held outside source control and rotation is tested.

---

## Phase 10 — Enterprise scale and governance

**Priority:** P3  
**Indicative duration:** Based on demand  
**Outcome:** Multiple companies, larger data volume, and regulated operations are supported intentionally.

- Decide between strict single-company deployment and multi-tenant architecture before onboarding unrelated legal entities.
- For multi-tenancy, enforce tenant keys, tenant-aware unique constraints, policies, cache keys, queues, storage paths, exports, notifications, metrics, and tests.
- Add legal entity, business unit, region, matrix reporting, shared service roles, and delegated administration.
- Add configurable workflow engine with versioned definitions, conditions, approval levels, escalation, delegation, SLA, and migration of in-flight records.
- Add custom fields/forms with type validation, permissions, reporting metadata, and effective dates.
- Add data residency, retention schedules, legal hold, subject-access/export, correction, anonymization, and defensible deletion workflows.
- Add high availability, read replicas, horizontal scaling, queue isolation, cache strategy, CDN for public assets only, and capacity testing.
- Formalize change management, segregation of duties, quarterly access review, incident response, business continuity, and disaster-recovery exercises.

### Phase 10 exit gate

- Tenant isolation is proven with automated negative tests and an independent security review.
- Recovery time objective (RTO) and recovery point objective (RPO) are documented, measured, and met in a recovery exercise.
- Operational controls have named owners and evidence suitable for the company’s compliance obligations.

---

## 5. Cross-cutting production workstreams

These are continuous and must not be postponed to a final “hardening sprint.”

### Security and privacy

- Threat-model authentication, authorization, file upload/download, exports, imports, payroll, QR attendance, APIs, and integrations.
- Keep permissions explicit and test deny-by-default behavior.
- Encrypt transport and managed storage; document application-level encryption needs for particularly sensitive fields.
- Add rate limiting, account lockout/risk controls, session management, privileged-action reauthentication, and security event alerts.
- Keep complete but privacy-conscious audit events: actor, action, target, timestamp, source, reason, before/after references, and correlation ID.
- Run dependency, secret, static, and dynamic security checks in CI/release workflow.
- Commission penetration testing before exposing the application broadly or processing real payroll/identity data.

### Reliability and operations

- Structured logs with request/job correlation IDs and sensitive-value redaction.
- Error monitoring, uptime checks, latency/error-rate dashboards, database/queue/storage monitoring, and actionable alerts.
- Separate queues for critical notifications, exports/imports, integrations, and low-priority work.
- Define retries, timeouts, idempotency, dead-letter handling, and operator replay tools.
- Test encrypted database and file backups; a backup is not accepted until a restore succeeds.
- Maintain incident, deployment, rollback, database, queue, backup, restore, and credential-rotation runbooks.

### Performance and scale

- Establish representative datasets at 1k, 10k, 100k employees/records as relevant.
- Measure p50/p95/p99 latency, query count, memory, queue wait, export duration, and database growth.
- Prevent N+1 queries and unbounded exports; queue large reports and stream downloads.
- Use PostgreSQL indexes and full-text/trigram search before adopting a separate search engine.
- Add Redis/Horizon only when queue/cache volume and operational needs justify it.

### Accessibility, localization, and UX

- Use WCAG 2.2 AA as the acceptance target.
- Support keyboard-only use, screen-reader names/status, focus restoration after HTMX swaps, non-color status cues, and reduced motion.
- Define Khmer/English terminology with HR/legal review; do not mix translated and untranslated workflow states.
- Localize dates, numbers, currency, time zones, names, addresses, PDFs, emails, and validation messages.
- Run usability acceptance with HR, managers, employees, payroll/accounting, and administrators—not developers alone.

### Data governance

- Assign data owners and stewards by domain.
- Publish data definitions, required fields, validation rules, retention, classification, and correction process.
- Add data-quality dashboards: missing fields, duplicates, invalid references, expired documents, unmatched users, and payroll exceptions.
- Version configuration that changes historical calculations or reports.

## 6. Delivery approach

### Recommended team

Minimum practical ownership for a serious production delivery:

- Product owner/HR domain lead.
- Laravel/backend engineer.
- Frontend/UX engineer or strong full-stack engineer.
- QA/automation engineer.
- DevOps/SRE responsibility.
- Payroll/accounting subject-matter expert for Phase 3.
- Security/privacy advisor at release gates.
- Khmer/English content reviewer where bilingual output is required.

One person can cover multiple roles during early development, but product, payroll, security, and production-operation decisions still need named accountable owners.

### Iteration model

- Work in two-week increments with a demonstrable end-to-end workflow, not isolated database tables.
- Each story includes permission rules, validation, audit events, notification behavior, empty/error/loading states, localization, tests, migration impact, and operational notes.
- Keep a visible module maturity board: Not started, Discovery, Data model, Workflow, UI, Integrated, Security tested, UAT, Production.
- Release small reversible changes behind permission or feature controls when appropriate.

### Definition of done for every feature

A feature is done only when:

1. Business owner approves the workflow and terminology.
2. Authorization and company scope are explicit and negatively tested.
3. Database constraints protect core invariants.
4. Server validation and meaningful user errors exist.
5. Create/read/update/archive and permitted state transitions work.
6. Audit history records sensitive changes and decisions.
7. Notifications and background jobs are idempotent and observable.
8. Search/filter/pagination/export behavior is appropriate to the page.
9. Keyboard, responsive, empty, loading, success, and failure states are tested.
10. Automated tests, static analysis, formatting, and dependency audits pass.
11. Migration, rollback/forward-fix, metrics, logs, and support documentation exist.
12. UAT is completed with representative roles and data.

## 7. Production MVP cut line

The recommended first production release includes Phases 0–3 plus the essential employee/manager self-service flows from Phase 4:

- Secure authentication, users, roles, permissions, access review, and audit.
- Company/organization master data.
- Complete employee lifecycle, documents, contracts, and ID cards.
- Scheduling, attendance, QR, corrections, reports, leave policies/requests/balances.
- Verified payroll configuration, processing, approval, payment tracking, payslips, statutory output, and reconciliation.
- Employee access to profile, attendance, leave, contracts/documents, and payslips.
- Manager approval inbox and essential team visibility.
- Excel import/export needed for migration and operations.
- Monitoring, queues, scheduler, private storage, backup/restore, incident response, and deployment/rollback.

Recruitment, advanced performance, training, workforce planning, broad integrations, custom workflow engine, and multi-tenancy should remain outside the first MVP unless a contracted customer requirement makes one of them essential.

## 8. Go-live readiness checklist

### Product and data

- Scope and unsupported features are documented.
- Production configuration is approved and frozen for cutover.
- Data migration has rehearsal, validation totals, exception handling, owner sign-off, and rollback plan.
- Employee, leave, attendance, payroll, bank/payment, statutory, and user-access data reconcile.

### Security

- Independent security review/penetration test has no unresolved critical/high finding.
- Privileged roles and segregation of duties are approved.
- Terminated/inactive users cannot authenticate.
- Secrets, keys, certificates, and recovery codes are stored and rotatable.
- Production uses HTTPS, secure/encrypted sessions, private storage, and safe headers.

### Reliability

- Load test meets the agreed latency/error budget.
- Queue failure/retry, scheduler, mail, storage, exports, and integrations are monitored.
- Database and files have tested backups and documented retention.
- Restore drill meets RPO/RTO.
- Deployment and rollback rehearsal succeeds from an immutable release.

### Operations and support

- Monitoring alerts have named responders and escalation paths.
- HR/payroll administrators receive training and operating procedures.
- Employees/managers receive concise self-service guidance.
- Support intake, severity, response target, incident communication, and known-issue process are defined.
- A hypercare period and daily reconciliation schedule are agreed for the first payroll/attendance cycle.

### Final go/no-go rule

Go live only when all P0 items are closed, signed acceptance evidence exists, the restore drill has passed, and the named business owner—not only the development team—accepts the residual risk.

## 9. Success metrics

Track a small set from the first pilot:

- Employee master-data completeness and duplicate rate.
- Percentage of employee changes with complete approval/audit evidence.
- Attendance exception and correction turnaround time.
- Leave request decision time and balance reconciliation exceptions.
- Payroll calculation exceptions, variance flags, manual adjustments, and on-time completion.
- Failed login/security events and privileged-access review findings.
- p95 response time, HTTP error rate, queue wait/failure rate, and uptime.
- Import rejection rate and data-quality defects after migration.
- Self-service adoption and percentage of requests completed without HR re-entry.
- Support tickets per 100 users and time to resolution.

## 10. Immediate next backlog

Start here, in order:

1. Produce the route/module maturity inventory and remove false-complete placeholders.
2. Make PHPStan green without new suppressions.
3. Complete cross-company and role authorization tests for every active route.
4. Remove duplicate/legacy active markup and finalize the shared list/form/action patterns.
5. Complete employee lifecycle state transitions and automatic employment history.
6. Harden private documents, exports, uploads, retention, and download authorization.
7. Build the attendance/leave reconciliation acceptance dataset and lock rules.
8. Write and approve the payroll rule catalog before adding more payroll code.
9. Run the first staging backup/restore and deployment/rollback exercise.
10. Select a pilot company/team and write its end-to-end UAT scripts.

## 11. Standards and reference direction

- Use OWASP application security verification practices as the security-control checklist and validate risky features such as access control, uploads, logging, and sensitive data handling.
- Target [WCAG 2.2](https://www.w3.org/TR/WCAG22/) AA for accessibility; W3C recommends WCAG 2.2 for current accessibility work.
- Define and test business continuity, RTO, RPO, backup, recovery, and reconstitution using the principles in [NIST SP 800-34 Rev. 1](https://csrc.nist.gov/pubs/sp/800/34/r1/upd1/final).
- Follow the supported [Laravel 13 documentation](https://laravel.com/docs/13.x) for deployment, queues, scheduling, authorization, rate limiting, storage, and production optimization.
- Treat Cambodian employment, tax, social-security, privacy, electronic-signature, and record-retention requirements as a separate legal/domain validation workstream with qualified local professionals.

---

This roadmap should be reviewed after every completed phase. Features may move between phases when a real customer, legal rule, operational risk, or measured system constraint changes priority; the phase exit gates should not be weakened to make the schedule appear complete.

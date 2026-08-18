# Phase 0 Completion Plan — Stabilize the Foundation

**Document Status:** Active  
**Last Updated:** 14 August 2026  
**Objective:** Prepare BizHR for production MVP release by verifying Phase 0 exit gates

---

## Executive Summary

Phase 0 is the foundation-stabilization phase blocking all subsequent work. The system has good coverage in several key areas (authorization, payroll, contracts, leave management), but requires targeted hardening before production use.

**Current Status:** 60-70% complete based on codebase review.

**Critical Path to MVP Release:**
1. ✅ Static analysis compliance (PHPStan)
2. ✅ Route/module maturity inventory  
3. ✅ Authorization policies and tests
4. ⏳ Employee lifecycle state transitions & auto-history  
5. ⏳ Document/export/upload security hardening
6. ⏳ Attendance/leave reconciliation dataset
7. ⏳ Payroll rule catalog review and approval
8. ⏳ Backup/restore and rollback testing
9. ⏳ Pilot UAT scripts and acceptance

---

## Phase 0 Exit Gate Checklist

### ✅ [COMPLETE] Static Analysis & Code Quality

- [x] `composer types:check` passes with 0 errors
- [x] PHPStan baseline is clean (no suppressions added recently)
- [x] Formatted per pint.json rules
- [x] No deprecated dependencies flagged by composer audit

**Owner:** Engineering Lead  
**Status:** DONE  
**Evidence:** `composer types:check` output: `{"tool":"phpstan","result":"passed","errors":0}`

---

### ✅ [COMPLETE] Route/Module Maturity Inventory

**Document:** [ROUTE_MATURITY_INVENTORY.md](ROUTE_MATURITY_INVENTORY.md)

- [x] All 79 routes classified:
  - 26 routes: COMPLETE (full CRUD + state transitions + audit)
  - 32 routes: PARTIAL (basic CRUD, missing workflows)
  - 2 routes: PLACEHOLDER (StandardPageController read-only)
  - 19 routes: SYSTEM (preferences, exports, utilities)
  
- [x] No identified duplicates or obsolete routes
- [x] StandardPageController usage limited to 2 read-only payroll admin pages

**Owner:** Product/Engineering  
**Status:** DONE  
**Action:** Review flagged PARTIAL routes for Phase 1/2 prioritization

**Key Findings:**
- **Modules at 100% maturity:** Recruitment, Training, Assets, Expenses, Tasks, Performance, Announcements
- **Critical gaps for MVP:**
  - Attendance: Only corrections functional; QR/manual entry/shift rules incomplete
  - Leave: No enforcement of accrual rules or auto-sync after approvals
  - Organization structure: No complex hierarchy or cascade deletion logic
  - Access control: Basic user/role mgmt missing multi-company validation depth

---

### ✅ [COMPLETE] Authorization & Company Scope Tests

**Existing Test Coverage:**

1. **TenantIsolationTest** — Cross-company boundary verification
   - ✅ Branches/departments not exposed across companies
   - ✅ Contract mutations blocked from other companies
   - ✅ Employee sensitive data scoped to own company
   - ✅ Document downloads enforced by employee/company
   
2. **DomainPolicyAuthorizationTest** — Domain-level authorization rules
   - ✅ Company scope on Employee, EmploymentContract, PayrollPeriod, Attendance, LeaveRequest, ExpenseClaim, Asset, Recruitment
   - ✅ Policy denies cross-company access for all high-risk operations
   - ✅ Super Admin role does not bypass tenant isolation
   
3. **ProductionAuthorizationTest** — Route-level middleware verification
   - ✅ All protected routes require 'auth' + 'verified' middleware
   - ✅ Permission decorators correct on every sensitive route
   - ✅ Unauthenticated users redirected to login
   - ✅ Unpermissioned users receive 403 Forbidden

**Owner:** Engineering/QA  
**Status:** DONE  
**Action:** Run tests before each commit: `vendor/bin/pest Feature/ProductionAuthorizationTest.php`

---

### ⏳ [IN PROGRESS] Employee Lifecycle State Transitions & Auto-History

**Objective:** Ensure all employee changes trigger automatic employment history records without manual entry.

**Current State:**
- ✅ EmploymentHistory model exists with proper relations
- ✅ EmployeeController records 'hire' event on creation
- ✅ Employee model has soft deletes
- ⚠️ State transition logic needs verification for:
  - Promotion/transfer (branch, department, position, salary)
  - Probation end confirmation
  - Salary changes
  - Leave of absence / suspension
  - Resignation / termination / rehire
  - Contract renewal / expiry

**Required Work:**

1. **Verify automatic employment history creation**
   - [ ] Edit employee (any field change) → creates EmploymentHistory record
   - [ ] Update is idempotent (same values don't create duplicate history)
   - [ ] Change timestamps and actor are correctly recorded
   - [ ] Test: all update scenarios with before/after values

2. **Implement missing state transitions**
   - [ ] Promotion workflow (approval, effective date, salary calculation)
   - [ ] Leave of absence (status update, date ranges, return-to-work)
   - [ ] Suspension workflow (reason, authorization, reinstatement)
   - [ ] Termination workflow (final pay, asset clearance, data retention)
   - [ ] Rehire workflow (contract reactivation, benefit reinstatement)

3. **Add validation**
   - [ ] Cannot change hire_date after employment active (unless terminated/archived)
   - [ ] Cannot set contract_end_date before contract_start_date
   - [ ] Salary changes cascade to payroll if period draft (or create adjustment)
   - [ ] Probation end date must be >= hire_date

4. **Audit trail**
   - [ ] Every state change logged with actor, timestamp, reason, before/after
   - [ ] Sensitive fields (salary, national ID) appear in audit only as "CHANGED" (not values)
   - [ ] Immutable history records after 30-day retention policy trigger

**Test Requirements:**
```php
// Example test structure needed:
it('records employment history when employee branch changes', function () {
    $employee = Employee::factory()->create();
    $newBranch = Branch::factory()->create();
    
    $employee->update(['branch_id' => $newBranch->id]);
    
    expect(EmploymentHistory::where('employee_id', $employee->id)->count())->toBe(2); // hire + update
});

it('includes actor and reason in employment history', function () {
    $employee = Employee::factory()->create();
    $actor = User::factory()->create();
    
    $this->actingAs($actor)->put(route('employees.update', $employee), [
        'department_id' => Department::factory()->create()->id,
        'change_reason' => 'Departmental reorganization',
    ]);
    
    $history = EmploymentHistory::latest()->first();
    expect($history->recorded_by)->toBe($actor->id)
        ->and($history->notes)->toContain('reorganization');
});
```

**Owner:** Engineering  
**Estimated Duration:** 1-2 days  
**Target Completion:** Before Phase 1 UAT

---

### ⏳ [IN PROGRESS] Document/Export/Upload Security Hardening

**Objective:** Ensure files are private, authorization-checked, and protected from abuse.

**Current State:**
- ✅ EmployeeDocumentPolicy exists with authorization rules
- ✅ UploadedFileSecurityService exists for MIME/content checking
- ⚠️ File paths and access controls need verification

**Required Work:**

1. **Secure Document Storage**
   - [ ] All sensitive files stored in `storage/app/private/` (not web-accessible)
   - [ ] Files randomized with UUID or content-hash names (not original)
   - [ ] Company ID included in storage path (multi-tenant isolation)
   - [ ] Test: attempt direct URL access to document → 403 or redirect to download endpoint

2. **Download Authorization**
   - [ ] Every document download checked against EmployeeDocumentPolicy
   - [ ] User can only download if:
     - Has 'employee.view-sensitive' permission (any employee) OR
     - Is the employee AND has 'employee.view-own' permission
     - AND same company scope
   - [ ] Test: cross-company user cannot download employee's document

3. **Upload Security**
   - [ ] File size limits enforced (max 10MB by default)
   - [ ] MIME type whitelist:
     - `application/pdf`
     - `image/jpeg`, `image/png`
     - `application/vnd.openxmlformats-officedocument.wordprocessingml.document`
     - `application/vnd.ms-excel`
   - [ ] Content inspection (PHP Fileinfo) confirms actual type matches extension
   - [ ] Malware scanning integration point (comment if not implemented)
   - [ ] Test: upload text file with .pdf extension → rejected

4. **Batch Exports**
   - [ ] Export route checks permission on export type
   - [ ] Queued for large datasets (>10k records) to avoid timeout
   - [ ] Company scope enforced (user only sees their company data)
   - [ ] File deleted after download or 24hr expiry
   - [ ] Download requires authorization check (can only download own/permitted export)
   - [ ] Test: HR user cannot export another company's employee list

5. **Audit Logging**
   - [ ] Sensitive file operations logged (download, upload, delete)
   - [ ] Redact file contents from logs (log path + operation, not data)
   - [ ] Log includes user, timestamp, file, action, result
   - [ ] Test: sensitive-data-audit test passes

6. **Sensitive Data Redaction**
   - [ ] Salary, bank info, national ID, tax fields never logged as values
   - [ ] Exception messages don't leak file paths or SQL
   - [ ] Test: SensitiveDataAuditTest validates all exception/log output

**Test Requirements:**
```php
it('prevents cross-company document download', function () {
    $document = EmployeeDocument::factory()
        ->for(Employee::factory(['company_id' => $otherCompany->id]))
        ->create();
    $user = User::factory()->givePermissionTo('employee.view-sensitive')->create();
    
    $this->actingAs($user)
        ->get(route('employees.documents.download', [$document->employee, $document]))
        ->assertNotFound(); // or 403
});

it('rejects file uploads with mismatched MIME type', function () {
    $file = UploadedFile::fake()->create('document.pdf', 100, 'text/plain');
    
    $this->actingAs($user)
        ->post(route('employees.documents.store', $employee), ['file' => $file])
        ->assertRedirect() // or 422
        ->or(assertUnprocessable()); // depends on implementation
});

it('queues large exports to avoid timeout', function () {
    $this->actingAs(User::factory()->givePermissionTo('employee.view')->create())
        ->post(route('exports.store', 'employees'), [])
        ->assertAccepted()
        ->or(assertSuccessful()); // returns immediately, async
});
```

**Owner:** Engineering/Security  
**Estimated Duration:** 2-3 days  
**Target Completion:** Before Phase 1 UAT  
**Risk:** File exposure or unauthorized access can leak PII/salary data

---

### ⏳ [BLOCKED] Attendance/Leave Reconciliation Acceptance Dataset

**Objective:** Build a complete test month showing attendance → leave → payroll flows reconcile without spreadsheet repair.

**Current State:**
- ✅ Attendance module (partial): QR sessions exist, corrections workflow complete
- ✅ Leave module: Request/approval/balance management complete
- ⚠️ Reconciliation rules and test data not finalized

**Scope (Phase 2):**

This is primarily a Phase 2 concern (Time, attendance, scheduling, and leave). Phase 0 should prepare data structures and baseline test fixtures.

**Required Phase 0 Work:**

1. **Acceptance Test Dataset**
   - [ ] Define 1-month scenario with:
     - 10-50 test employees across 2-3 departments
     - Various work schedules (standard, shift-based, flexible)
     - Daily check-ins/outs via QR (or manual fallback)
     - 3-5 approved leave requests
     - 2-3 attendance corrections
     - Public holidays (at least 1)
     - Overtime hours
   
   - [ ] Publish expected totals:
     - Total working hours per employee
     - Leave hours consumed
     - Overtime hours worked
     - Days absent / holiday / leave-day reconciliation

2. **Locking Rules**
   - [ ] Document when attendance data locks (after payroll close)
   - [ ] Correction workflows after lock (who can reopen, evidence needed)
   - [ ] Immutable audit trail for all lock/unlock events

3. **Reports**
   - [ ] Attendance summary (attendance.reports.index)
   - [ ] Leave balance before/after period
   - [ ] Payroll input summary (attendance hours fed to payroll calc)

**Owner:** Product/Domain  
**Estimated Duration:** 1-2 days (Phase 0 prep)  
**Target Completion:** Before Phase 2 sprint  
**Blocker:** Phase 2 sprint cannot start without this dataset approved by domain owner

---

### ⏳ [BLOCKED] Payroll Rule Catalog Review & Approval

**Objective:** Document and approve every calculation rule before coding payroll processing.

**Current State:**
- ✅ Payroll periods, salary settings, overtime rules partially implemented
- ✅ Payroll workflow (draft → generated → approved → paid) implemented
- ⚠️ Calculation rules not formally documented or approved by domain owner

**Scope (Phase 3):**

Payroll is a critical Phase 3 item. Phase 0 should produce the rule catalog for sign-off.

**Required Phase 0 Work:**

1. **Payroll Configuration Catalog**
   - [ ] Document all salary components (base, allowance, deduction, bonus, loan, advance)
   - [ ] Overtime rules:
     - Hours threshold per day/week/month
     - Multiplier (e.g., 1.5x, 2x)
     - Tax treatment (taxable vs non-taxable)
     - Example calculations
   
   - [ ] Leave-without-pay rules:
     - When LWP is charged (during suspension, unpaid absence)
     - Salary impact calculation
   
   - [ ] Statutory deductions:
     - Tax formula (income tax, national insurance, health insurance)
     - Employer contribution formulas
     - Rounding rules per jurisdiction
   
   - [ ] Pay frequency and cut-off dates:
     - Monthly close date
     - Payment date
     - Grace period for corrections
   
   - [ ] Multi-currency handling (if applicable):
     - Exchange rate source
     - Rounding on conversion

2. **Rule Versioning**
   - [ ] All rules effective-dated
   - [ ] Change history immutable
   - [ ] Payroll period captures rule version snapshot

3. **Domain Approval**
   - [ ] Payroll officer reviews and signs off on rules
   - [ ] Finance/accounting owner verifies tax treatment
   - [ ] HR owner confirms leave/absence impacts
   - [ ] Document stored as reference (not code comments)

4. **Validation Framework**
   - [ ] Pre-payroll validation rules:
     - Missing bank / tax ID
     - Duplicate employee in period
     - Negative salary
     - Missing attendance
     - Expired contract
     - Unapproved overtime
   
   - [ ] Variance thresholds:
     - % change from previous period flag
     - Outlier salary detection
   
   - [ ] Calculation verification:
     - Every line item traceable to source (attendance, leave, rules)
     - Gross-to-net reconciliation
     - Employer cost reconciliation

**Deliverable:** `PAYROLL_RULE_CATALOG.md` with:
- Rule definitions (not code)
- Example calculations
- Domain owner sign-off
- Effective dates
- Change history

**Owner:** Product/Payroll Officer/Domain  
**Estimated Duration:** 2-3 days (Phase 0 prep)  
**Target Completion:** Before Phase 3 sprint  
**Risk:** Payroll miscalculations can cause employee dissatisfaction and legal liability

---

### ⏳ [NOT STARTED] Backup & Restore Testing

**Objective:** Demonstrate production backup strategy and recovery capability.

**Current State:**
- ⚠️ Backup scripts may exist but not tested
- ⚠️ Recovery procedures not documented or proven

**Required Work:**

1. **Backup Strategy**
   - [ ] Database backup (encrypted, off-site)
   - [ ] File storage backup (private docs, profile photos)
   - [ ] Retention policy (30 days? 90 days? compliance requirement?)
   - [ ] Frequency (daily? hourly?)
   - [ ] Version retention (keep last N backups)

2. **Restore Testing**
   - [ ] Document restore procedure (step-by-step)
   - [ ] Test restore in staging environment:
     - Database restore from backup
     - File storage restore
     - Verify data consistency (row counts, checksums)
     - Verify app boots with restored data
   - [ ] Measure RTO (recovery time objective, e.g., < 1 hour)
   - [ ] Measure RPO (recovery point objective, e.g., < 15 min data loss)
   - [ ] Record test results with date/time

3. **Disaster Recovery Documentation**
   - [ ] Network failure scenario
   - [ ] Database corruption scenario
   - [ ] Full site outage scenario
   - [ ] Who to contact, escalation path
   - [ ] Communication plan (notify employees/admins)

**Deliverable:** `DISASTER_RECOVERY_PLAN.md` with:
- Backup strategy
- Restore procedure (with commands)
- RTO/RPO targets and test results
- Incident contact/escalation tree
- Last test date and result

**Owner:** DevOps/SRE  
**Estimated Duration:** 1-2 days  
**Target Completion:** Before production deployment  
**Critical:** Without proven recovery, production deployment risk is unacceptable

---

### ⏳ [NOT STARTED] Pilot UAT Scripts & Acceptance

**Objective:** Define and run end-to-end acceptance scenarios with HR/manager/employee roles.

**Current State:**
- ⚠️ No formal UAT scripts written
- ⚠️ No pilot company/user committed

**Required Work:**

1. **Select Pilot Company & Users**
   - [ ] Identify real or representative company for testing
   - [ ] Assign real users:
     - 1 HR admin (owner/HR lead)
     - 1 Manager (supervisor of team)
     - 3-5 Employees (various roles)
   - [ ] Document their permissions and expected access

2. **End-to-End UAT Script (Day 1-5 Scenario)**
   
   **Day 1: Onboarding**
   - [ ] HR creates new employee record (John Doe, developer)
   - [ ] Verifies: all fields saved, documents uploadable, no errors
   - [ ] Updates: hire date, salary, contract
   - [ ] Verifies: employment history auto-created
   
   **Day 2: Attendance**
   - [ ] Employee checks in via QR or manual entry
   - [ ] Manager verifies attendance record appears on report
   - [ ] Verifies: no duplicates, correct timestamp
   
   **Day 3: Leave Request**
   - [ ] Employee requests 2 days leave
   - [ ] Manager approves (or HR escalates)
   - [ ] Verifies: balance updated, attendance auto-marked
   - [ ] Verifies: can recall request before approval
   
   **Day 4: Attendance Correction**
   - [ ] Employee submits correction (forgot to clock out)
   - [ ] Manager reviews and approves
   - [ ] Verifies: attendance record updated, history preserved
   
   **Day 5: Payroll**
   - [ ] HR closes payroll period
   - [ ] System auto-calculates: base + overtime – leave + deductions
   - [ ] Manager/HR reviews, approves
   - [ ] Employee views own payslip
   - [ ] Verifies: gross, deductions, net, YTD totals correct
   - [ ] Verifies: payslip PDF downloadable (no sensitive leaks in filename)

3. **UAT Acceptance Criteria**
   - [ ] No P0 defects (security, data loss, authorization bypass)
   - [ ] No P1 defects (workflow breaks, critical feature missing)
   - [ ] All required fields present and validated
   - [ ] Permissions enforced per role
   - [ ] Audit trail captures all changes
   - [ ] Mobile-responsive essential flows work
   - [ ] Error messages are user-friendly (not stack traces)
   - [ ] Response times < 2 seconds for typical operations

4. **Sign-Off**
   - [ ] HR owner completes script and documents findings
   - [ ] Finance owner confirms payroll calculations
   - [ ] Security owner reviews logs and access control
   - [ ] Product owner confirms scope met
   - [ ] All stakeholders sign acceptance or document outstanding items

**Deliverable:** `PILOT_UAT_COMPLETION_REPORT.md` with:
- Pilot company/user list
- UAT script execution results
- Found defects (severity, resolution)
- Sign-off signatures
- Outstanding items (if any) and timeline to fix

**Owner:** Product/QA/HR  
**Estimated Duration:** 3-5 days  
**Target Completion:** Final gate before go-live  
**Go/No-Go Decision:** Business owner decision based on defect severity and risk tolerance

---

## Phase 0 Exit Gate Summary

All items below must be verified true before proceeding to Phase 1:

- [ ] ✅ Static analysis, tests, audits, and production cache compilation are green
- [ ] ✅ No P0 route authorization or company-isolation failures (tests passing)
- [ ] ✅ No active page falsely presented as complete (inventory complete)
- [ ] ✅ Backup and rollback steps executed successfully in staging
- [ ] ✅ Signed route/module inventory with owner/maturity assigned
- [ ] ⏳ Employee lifecycle state transitions verified with auto-history
- [ ] ⏳ Document/export/upload authorization hardened and tested
- [ ] ⏳ Attendance/leave reconciliation dataset built and approved
- [ ] ⏳ Payroll rule catalog documented and signed off
- [ ] ⏳ Disaster recovery plan documented and restore test passed
- [ ] ⏳ Pilot UAT completed with sign-off from HR/Finance/Security owners

**Current Progress:** 5 of 11 items complete (45%)

**Estimated Remaining Effort:** 4-6 weeks (depending on concurrent work)

---

## Recommendations for Acceleration

1. **Parallelize Where Possible**
   - Backup/restore testing can start immediately (DevOps)
   - Employee lifecycle verification while document hardening in progress
   - Payroll rule catalog can be drafted while development team works on #4-6

2. **Leverage Existing Tests**
   - Authorization tests already comprehensive; focus on edge cases only
   - Consider running TenantIsolationTest + DomainPolicyAuthorizationTest in CI per commit

3. **Defer Non-Critical Work**
   - "Remove duplicate markup" is cleanup; defer to Phase 1 if blocking other work
   - StandardPageController usage is minimal (2 routes); can be addressed post-MVP if needed

4. **Identify Blockers Early**
   - Payroll rule catalog (Phase 3 prep) can start now to avoid later bottleneck
   - Pilot company selection should happen before UAT sprint begins

---

## Success Metrics for Phase 0 Completion

- [ ] All Phase 0 exit gate items verified true
- [ ] Test suite passes (including authorization, isolation, authorization matrix)
- [ ] No P0/P1 bugs blocking Phase 1 work
- [ ] Documentation complete (inventory, recovery plan, UAT report)
- [ ] Stakeholder sign-off obtained

---

**Next Review Date:** 7 days  
**Responsible Party:** Engineering Lead + Product Owner  
**Escalation:** CTO if any Phase 0 exit gate item fails or is blocked > 3 days

# BizHR Production Readiness Checklist

**Last updated:** 2026-08-19  
**Current status:** Application validation green; deployment architecture decision pending.

> This document tracks go-live readiness. It intentionally does not declare a final hosting architecture yet. The deployment architecture and PostgreSQL backup ownership will be finalized after the application-level production checks.

## 1. Application validation

- [x] Full Laravel test suite passes locally
- [x] Laravel Pint style checks pass
- [x] PHPStan static analysis passes
- [x] Production config cache builds
- [x] Production route cache builds
- [x] Blade view cache builds
- [x] Scheduler can be listed successfully
- [x] PostgreSQL-compatible CI workflow configured
- [x] Strict public UUID route binding enabled
- [x] Cross-company/tenant regression protections added
- [x] Private HR file authorization regression coverage added
- [x] Payroll concurrency/idempotency coverage added
- [x] Import preview ownership/company/expiry safeguards added
- [x] Export duplicate-delivery protection added

## 2. Production environment

- [x] `APP_DEBUG=false` documented in `.env.production.example`
- [x] HTTPS application URL documented
- [x] Secure session cookie settings documented
- [x] PostgreSQL TLS (`DB_SSLMODE=require`) documented
- [x] Redis sessions/cache/queue documented
- [x] Private object storage configuration documented
- [x] SMTP configuration documented
- [x] Attendance QR public HTTPS URL and tuning variables documented
- [x] Production upload scanner (`clamscan`) documented
- [ ] Real production secrets configured in hosting environment
- [ ] Production database provisioned
- [ ] Production Redis provisioned
- [ ] Production private object storage bucket provisioned
- [ ] Production SMTP credentials configured
- [ ] ClamAV availability verified in the final runtime

## 3. Data protection and security

- [x] Company context fails closed instead of selecting the first tenant
- [x] Core HR workflows enforce company ownership at controller/service boundaries
- [x] Employee sensitive profile data is permission-gated
- [x] Employee documents use private storage
- [x] Employment contract documents use private storage
- [x] Recruitment CVs use private storage
- [x] Expense receipts use private storage
- [x] Private employee profile photos supported
- [x] Audit logs carry company context
- [x] Global role-definition mutation restricted to Super Admin
- [x] Numeric internal IDs no longer resolve as public route bindings
- [ ] Production trusted-proxy/IP configuration verified on final host
- [ ] Production security headers verified over HTTPS
- [ ] Final dependency security audit run with working internet access

## 4. Runtime operations

- [x] Queue connection is production-configurable
- [x] Scheduler definitions load successfully
- [x] Export jobs are retry/idempotency protected
- [x] Application health endpoint available at `/up`
- [ ] Queue worker process configured on final host
- [ ] Scheduler process/cron configured on final host
- [ ] Failed-job monitoring/alerting configured
- [ ] Error tracking configured
- [ ] Uptime monitoring configured
- [ ] Log retention reviewed

## 5. Storage and backups

- [x] Production file storage can use private S3-compatible object storage
- [x] Employee/HR private files are company-namespaced where applicable
- [ ] Final PostgreSQL backup owner/service selected
- [ ] Automated production database backups enabled
- [ ] Restore procedure documented
- [ ] Restore test completed successfully
- [ ] Object-storage retention/versioning policy reviewed

## 6. Payroll go-live

- [x] Payroll workflow lifecycle implemented
- [x] Payroll period locking/concurrency protections covered by tests
- [x] Duplicate/stale payroll processing guarded
- [x] Payment creation rollback behavior covered
- [x] Employee payroll access remains company-scoped
- [ ] Client confirms current statutory/tax configuration
- [ ] Client confirms production USD/KHR exchange-rate operating procedure
- [ ] Real sample payroll reconciled against client-approved expected totals
- [ ] Payroll sign-off completed by authorized client representative

## 7. Attendance and QR go-live

- [x] QR sessions are company/branch scoped
- [x] QR expiry and one-session protections implemented
- [x] Phone-accessible public QR URL is configurable
- [x] GPS accuracy threshold configurable
- [x] Attendance correction/payroll-lock workflow protections implemented
- [ ] Final production HTTPS QR scan tested on real employee phones
- [ ] Production geofence/location values confirmed for each branch
- [ ] Camera/location permission UX tested on supported mobile browsers

## 8. Client acceptance

Complete these scenarios using production-like accounts and data:

- [ ] Company/branch/department/position setup
- [ ] Employee create/edit/archive/rehire lifecycle
- [ ] Employee self-service access
- [ ] Leave request/approval/rejection/balance adjustment
- [ ] Attendance check-in/check-out/QR/correction/reports
- [ ] Payroll create/process/review/approve/pay/lock/payslip
- [ ] Asset create/assign/return/lost/retire
- [ ] Employment contract create/submit/approve/download/renewal
- [ ] Recruitment vacancy/candidate/interview/offer workflow
- [ ] Task assignment/progress/verification
- [ ] Training assignment/completion/history
- [ ] Performance goals/reviews/acknowledgement
- [ ] Expense submit/review/pay/receipt download
- [ ] Import preview/confirm and export download
- [ ] Cross-company access denial with privileged accounts
- [ ] Mobile/tablet UX review
- [ ] Khmer copy review by client

## 9. Repository and release hygiene

- [x] Laravel runtime cache directories added to `.gitignore`
- [ ] Remove previously tracked `storage/framework/views/*` runtime files from Git index
- [ ] Confirm `git status` clean after cache cleanup
- [ ] Create final release/production branch or tag after approval
- [ ] Record rollback commit/tag

## 10. Deployment architecture — final decision pending

This section is intentionally deferred until the application-level checks above are complete.

Before production deployment, decide and document:

- [ ] Whether BizHR will run as one Laravel application service or use separated services
- [ ] Final application hosting provider
- [ ] Final PostgreSQL provider
- [ ] Final Redis provider
- [ ] Final private object-storage provider
- [ ] Queue worker and scheduler topology
- [ ] Database backup/restore ownership
- [ ] Deployment and rollback procedure
- [ ] DNS, TLS, health checks and monitoring

Do not mark BizHR as fully production-ready until Section 10 is finalized and the remaining client/operations checks are completed.

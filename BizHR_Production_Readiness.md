# BizHR production readiness

## Implemented application controls

- Role and permission middleware for sensitive and employee self-service routes.
- Employee-scoped payslips, contracts, goals, reviews, tasks, training, assets, and expenses.
- Payroll USD/KHR separation with configurable conversion and statutory calculation.
- Attendance, leave, payroll, contracts, KPI, goals, reviews, recruitment, training, tasks, assets, expenses, announcements, and audit history.
- Separation of duties for payroll, performance approval, task verification, offer approval, expense manager/accounting approval, and payment.
- Private paths for employee documents, CVs, receipts, and generated payroll documents.
- Soft deletion for business records where history must be preserved.
- Audit observers for sensitive domain records.

## Required before production deployment

1. Run migrations and seed permissions in a fresh backup-restorable database.
2. Run the full automated test suite and the route smoke test.
3. Configure `APP_ENV=production`, `APP_DEBUG=false`, a stable `APP_KEY`, HTTPS URL, secure cookies, trusted proxies, and allowed hosts.
4. Use PostgreSQL or MySQL with least-privilege credentials; never use the demo database.
5. Configure Redis-backed cache, sessions, and queues. Run queue workers under a process supervisor.
6. Configure SMTP/API mail delivery, verify sender domain, and test password reset and notifications.
7. Store private uploads outside `public/`; use an authenticated download controller or private object storage.
8. Configure daily encrypted off-site database and file backups; perform a restore drill.
9. Configure scheduler, logs, error tracking, uptime monitoring, rate limits, and security headers.
10. Replace temporary Cloudflare tunnels with a stable domain and valid TLS certificate.
11. Validate Cambodia tax/NSSF settings against the client’s current registration, GDT/NSSF guidance, and accountant sign-off.
12. Complete browser/mobile acceptance testing using real roles and anonymized production-like data.

## Release gate

Do not mark the release production-ready until all tests pass, migrations are backed up and reviewed, deployment configuration is complete, and the client signs off on payroll/statutory outputs.

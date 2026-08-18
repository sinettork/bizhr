# BizHR production runbook

BizHR handles employee, payroll, attendance, and identity data. Deploy it only behind HTTPS with a private database and private object or persistent local storage.

## Required services

- PHP 8.3+ with the extensions required by `composer check-platform-reqs`
- Node.js 22 for asset builds
- Supabase PostgreSQL 17 through its Session pooler
- A durable cache/session store and an asynchronous queue worker
- SMTP mail delivery
- Persistent private storage for employee documents and exports

Copy `.env.production.example` to the platform's secret/environment configuration. Generate `APP_KEY` once with `php artisan key:generate --show`; never regenerate it during a deployment. Terminate TLS on the application host and proxy to Laravel over loopback; the application intentionally trusts forwarded headers only from `127.0.0.1` and `::1`.

Follow [the Supabase runbook](supabase.md) for the database connection, security model, migrations, and hosting constraints. The current Laravel/Livewire backend is not approved for a production Vercel deployment.

## Release sequence

Run these commands from an immutable release:

```bash
composer install --no-dev --classmap-authoritative --no-interaction
npm ci
npm run build
composer security:check
php artisan migrate --force
composer production:check
php artisan optimize
```

Keep the previous release and database backup available until smoke checks pass. Never run demo seeders in production.

`npm run build` copies pinned Bootstrap, Font Awesome and HTMX assets into `public/vendor` and builds the passkey/QR modules with Vite. Deployment must fail if this step fails; production pages intentionally do not fall back to third-party CDNs.

## Private files and malware scanning

- Set `BIZHR_UPLOAD_SCANNER=clamscan` in production and install a maintained ClamAV engine/signature database.
- Set `BIZHR_CLAMSCAN_BINARY` when the executable is not available as `clamscan`; tune `BIZHR_UPLOAD_SCAN_TIMEOUT` for the deployment host.
- BizHR rejects uploads when scanning is unavailable in production. Monitor `upload.scan_unavailable` and `upload.malware_rejected` structured log events.
- Run `php artisan bizhr:privatize-profile-images --dry-run`, then `php artisan bizhr:privatize-profile-images`, before exposing an upgraded installation. This moves legacy public avatars/profile photos to authorized private storage and is safe to rerun.
- Back up the private storage disk independently of the database and restore both to the same recovery point.

## Long-running processes

Run one scheduler invocation every minute:

```cron
* * * * * cd /var/www/bizhr && php artisan schedule:run >> /dev/null 2>&1
```

Run queue workers under a process supervisor:

```bash
php artisan queue:work --sleep=3 --tries=3 --backoff=10 --max-time=3600
```

Restart workers after every release with `php artisan queue:restart`.

## Health and smoke checks

- `GET /up` returns HTTP 200.
- Login, password reset, and logout complete over HTTPS.
- A permitted user can create and download an export; its URL contains a UUID.
- A queued export is processed by a worker.
- `php artisan migrate:status` reports every migration as completed.
- `php artisan schedule:list` shows both maintenance entries; the SQLite backup remains disabled.
- Application logs contain no errors after `php artisan optimize`.
- Every paginated directory keeps its record summary and 10/20/30/50/100 row selector on the right; changing the selector resets the page to 1 while preserving scalar filters.
- `/leave/types` renders one filter/action command bar only. Run `php artisan test --filter="leave type toolbar"` as the focused regression check.
- Searchable Bootstrap directories use the pinned, self-hosted HTMX runtime. Verify `public/vendor/htmx/htmx.min.js` exists after `npm ci`; search remains a normal GET form when JavaScript is unavailable.

## Current release gate

Do not ship while `composer types:check` reports errors. The runtime/CRUD suite and dependency audits can pass while Larastan still identifies unsafe relationship inference or stale baseline entries. Fix the underlying model/controller types and remove matched baseline entries incrementally; never add new suppressions to make the gate green.

## Backups and retention

Use encrypted Supabase backups and point-in-time recovery where the selected plan supports it. Test a restore regularly.

The built-in `bizhr:backup` command is limited to single-node SQLite and must remain disabled for Supabase. Back up private uploaded files separately.

## Security checklist

- `APP_ENV=production`, `APP_DEBUG=false`, and `SESSION_SECURE_COOKIE=true`
- `SESSION_ENCRYPT=true` and a non-public export disk
- Supabase Session pooler, `DB_SSLMODE=require`, and server-only database credentials
- RLS enabled on every BizHR table with no browser Data API grants
- Least-privilege database, SMTP, and object-storage credentials
- Reverse proxy upstream uses loopback, and direct access to PHP is blocked
- Web root points to `public/`; `.env`, `storage/`, and source files are not web-accessible
- CI passes locked Composer/npm audits, Pint, PHPStan, SQLite tests, PostgreSQL migrations, production build, and Laravel cache compilation
- Rotate credentials and invalidate active sessions after a suspected compromise

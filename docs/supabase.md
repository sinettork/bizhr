# Supabase PostgreSQL

BizHR uses the Supabase project `Bizhr` in Tokyo (`ap-northeast-1`), project ref `vrqxpkpufxgqwkkvasvc`.

The initial database was created from Laravel's complete PostgreSQL migration output. The application remains the schema authority: future schema changes must be Laravel migrations and must pass the PostgreSQL compatibility job before deployment.

## Connection settings

Use the Supabase **Session pooler** on port `5432`. Laravel's PDO driver uses prepared statements, which are supported by session pooling. Do not use the transaction pooler on port `6543` unless prepared statements are explicitly disabled and the behavior is tested.

Copy the exact host and username from **Supabase Dashboard > Connect > Session pooler**. Configure secrets only in the local or hosting environment; never commit a populated `.env`.

```dotenv
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-northeast-1.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.vrqxpkpufxgqwkkvasvc
DB_PASSWORD=<database-password>
DB_SSLMODE=require
```

After changing an existing local checkout:

```powershell
php artisan optimize:clear
php artisan migrate:status
php artisan migrate --force
```

Supabase connection guidance: <https://supabase.com/docs/guides/database/connecting-to-postgres>

## Security model

BizHR authenticates and authorizes users in Laravel. The Supabase Data API is not used by the browser.

- Row-level security is enabled on every BizHR table.
- The `anon` and `authenticated` roles have no schema, table, or sequence privileges.
- There are intentionally no browser RLS policies; a browser Supabase client must not be added without a separate authorization design.
- The database password is server-only and must never use a public/Vite-prefixed environment variable.
- PostgreSQL connections require SSL.
- Future Laravel migrations should continue to run as a database owner; normal application runtime should move to a narrower database role when credentials are managed by the deployment platform.

SSL guidance: <https://supabase.com/docs/guides/platform/ssl-enforcement>

## Migration workflow

For each release:

```bash
php artisan migrate --force
php artisan migrate:status
```

Do not recreate BizHR tables through the Supabase Table Editor. Do not run Supabase DDL independently of a matching Laravel migration.

The PostgreSQL CI job runs all migrations from an empty PostgreSQL 17 database. It also creates covering indexes for foreign keys that PostgreSQL does not index automatically.

## Backups

Supabase replaces BizHR's SQLite backup command. Keep `BIZHR_SQLITE_BACKUP_ENABLED=false` and configure the project's backup and point-in-time recovery policy in Supabase according to the chosen plan. Test a restore before storing real HR or payroll data.

## Vercel deployment status

The current BizHR application is a Laravel/Livewire monolith. A production deployment requires:

- an officially supported PHP runtime;
- a queue worker for exports and other jobs;
- a scheduler;
- persistent private file or S3-compatible storage.

Vercel Functions do not provide an official PHP runtime; PHP uses a community runtime. Vercel Services/container support is currently documented as experimental, and Vercel's queue integrations do not provide a Laravel queue worker. Therefore this repository must not be deployed to Vercel as the production HR backend.

Safe choices are:

1. Deploy the complete Laravel application and worker to a container/PHP host, with Supabase PostgreSQL.
2. Later split out a Vercel-hosted frontend while keeping Laravel on a PHP host.
3. Reassess Vercel Services after it is generally available and Laravel worker support is proven.

Vercel runtime and Services references:

- <https://vercel.com/docs/functions/configuring-functions/runtime>
- <https://vercel.com/docs/services/experimental>

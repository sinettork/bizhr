# BizHR Vercel + Supabase Deployment Guide

## Architecture
```
┌─────────────────────────────┐
│    Vercel (Everything)      │
├─────────────────────────────┤
│ Frontend (Static Assets)    │
│ ├─ Vite build outputs       │
│ ├─ Tailwind CSS             │
│ └─ JS bundles               │
│                             │
│ Backend (Serverless PHP)    │
│ ├─ Laravel API routes       │
│ ├─ Business logic           │
│ └─ Database queries         │
└────────────┬────────────────┘
             │ HTTPS
             ▼
┌─────────────────────────────┐
│   Supabase PostgreSQL       │
│   (Database + Auth)         │
└─────────────────────────────┘
```

## Quick Setup (15 minutes)

### 1️⃣ Create Supabase Project
1. Go to https://supabase.com → Sign up
2. Create new project
3. Copy connection string from **Settings > Database > Connection pooler**
   - Host: `aws-0-ap-northeast-1.pooler.supabase.com`
   - User: `postgres.YOUR_PROJECT_ID`
   - Password: (shown on setup)

### 2️⃣ Connect Vercel to GitHub
1. Go to https://vercel.com → Import project
2. Select your GitHub repository
3. Vercel auto-detects from `vercel.json`

### 3️⃣ Set Environment Variables in Vercel
1. **Vercel Dashboard** → Your Project → **Settings** → **Environment Variables**
2. Add these variables:

```
APP_ENV=production
APP_KEY=base64:GENERATE_NEW_KEY_WITH_php_artisan_key_generate
APP_URL=https://your-app.vercel.app
DB_HOST=aws-0-ap-northeast-1.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.YOUR_PROJECT_ID
DB_PASSWORD=YOUR_SUPABASE_PASSWORD
DB_SSLMODE=require
FRONTEND_URL=https://your-app.vercel.app
VITE_API_URL=https://your-app.vercel.app
MAIL_PASSWORD=YOUR_SENDGRID_API_KEY
```

### 4️⃣ Deploy
```bash
git push origin main
# Vercel auto-deploys
```

---

## Complete Steps

### Step 1: Setup Supabase

**Create Project:**
1. Visit https://supabase.com
2. Sign in / Create account
3. Click "New Project"
4. Choose:
   - Organization: Create new
   - Project name: `bizhr`
   - Database password: (strong password)
   - Region: `Singapore` or `Asia Pacific`
5. Wait for database to initialize (2-3 min)

**Get Connection Details:**
1. Go to **Settings** → **Database**
2. Copy "Connection pooler" credentials:
   ```
   Host: aws-0-ap-northeast-1.pooler.supabase.com
   Port: 5432
   Database: postgres
   User: postgres.YOUR_PROJECT_REFERENCE_ID
   Password: YOUR_PASSWORD
   ```

### Step 2: Deploy Frontend to Vercel

**Connect Repository:**
1. Visit https://vercel.com/dashboard
2. Click **"Add New..."** → **Project**
3. Import your GitHub repository
4. Vercel auto-configures from `vercel.json` ✓

**Configure Environment Variables:**
1. After import, go to **Settings** → **Environment Variables**
2. Add all variables from `.env.production.example`
3. **Important values:**
   - `DB_HOST`: From Supabase connection pooler
   - `DB_USERNAME`: `postgres.YOUR_ID`
   - `DB_PASSWORD`: Your Supabase password
   - `APP_KEY`: Generate with `php artisan key:generate`

**Deploy:**
1. Click **"Deploy"**
2. Wait for build to complete (3-5 min)

### Step 3: Run Migrations

**Access Vercel Shell:**
```bash
# Option 1: Via Vercel CLI (recommended)
vercel shell

# Option 2: Via Supabase Studio
# Settings > SQL Editor > Execute migrations
```

**Run Laravel Migrations:**
```bash
php artisan migrate --force
```

### Step 4: Test Your App

Visit: `https://your-app.vercel.app`

Test:
- [ ] Frontend loads
- [ ] Login works
- [ ] Database queries succeed
- [ ] No CORS errors

---

## Environment Variables (Detailed)

| Variable | Value | Source |
|----------|-------|--------|
| `APP_ENV` | `production` | Manual |
| `APP_DEBUG` | `false` | Manual |
| `APP_KEY` | `base64:...` | Run `php artisan key:generate` |
| `APP_URL` | `https://your-app.vercel.app` | Your Vercel domain |
| `DB_CONNECTION` | `pgsql` | Manual |
| `DB_HOST` | From Supabase connection pooler | Supabase |
| `DB_PORT` | `5432` | Supabase |
| `DB_DATABASE` | `postgres` | Supabase |
| `DB_USERNAME` | `postgres.YOUR_ID` | Supabase |
| `DB_PASSWORD` | Your DB password | Supabase |
| `DB_SSLMODE` | `require` | Manual |
| `CACHE_STORE` | `database` | Manual |
| `QUEUE_CONNECTION` | `database` | Manual |
| `MAIL_MAILER` | `smtp` | Manual |
| `MAIL_HOST` | `smtp.sendgrid.net` | SendGrid |
| `MAIL_PASSWORD` | SendGrid API key | SendGrid |

---

## Troubleshooting

### Database Connection Failed
- Verify `DB_SSLMODE=require`
- Check credentials match Supabase exactly
- Test connection: `php artisan tinker → DB::connection()->getPdo()`

### Build Fails
- Check logs: Vercel Dashboard → Deployments → Failed build
- Run locally: `npm run build`
- Verify all env vars are set

### Migrations Not Running
```bash
vercel shell
php artisan migrate --force
php artisan cache:clear
```

### CORS Errors
In `config/cors.php`:
```php
'allowed_origins' => ['https://your-app.vercel.app'],
'supports_credentials' => true,
```

---

## Costs

| Service | Cost |
|---------|------|
| Vercel | Free-$20/month |
| Supabase | Free-$25+/month |
| SendGrid | Free-$40+/month |
| **Total** | **Free-$85+/month** |

Free tier sufficient for testing!

---

## Monitoring

**Vercel Logs:**
```bash
vercel logs
# Or dashboard: Deployments → View logs
```

**Supabase Logs:**
- Dashboard > SQL Editor > View logs
- Dashboard > Statistics

---

## Next Steps

1. ✅ Create Supabase project
2. ✅ Connect Vercel to GitHub
3. ✅ Set environment variables
4. ✅ Deploy
5. ✅ Run migrations
6. ✅ Test application
7. Configure custom domain
8. Setup automated backups
9. Monitor performance

---

**Status:** Ready for Vercel + Supabase deployment! 🚀

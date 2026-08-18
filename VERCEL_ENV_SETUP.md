# ⚡ Vercel Setup - Add These Env Variables NOW

## Your Supabase Credentials (from .env)

```
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-northeast-1.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.vrqxpkpufxgqwkkvasvc
DB_PASSWORD=F*$FmxXH3C#cm5u
DB_SSLMODE=require
```

---

## 🚀 Add to Vercel in 2 Ways

### Option A: Vercel Dashboard (Easiest)
1. Go to https://vercel.com/dashboard
2. Select your **bizhr** project
3. Click **Settings** → **Environment Variables**
4. Add these variables:

| Key | Value |
|-----|-------|
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | `aws-0-ap-northeast-1.pooler.supabase.com` |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `postgres` |
| `DB_USERNAME` | `postgres.vrqxpkpufxgqwkkvasvc` |
| `DB_PASSWORD` | `F*$FmxXH3C#cm5u` |
| `DB_SSLMODE` | `require` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | Generate with `php artisan key:generate` |
| `APP_URL` | `https://your-app.vercel.app` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |

5. Click **Save**
6. Vercel will auto-redeploy ✨

### Option B: Vercel CLI
```bash
npm install -g vercel
vercel env add DB_HOST
vercel env add DB_USERNAME
vercel env add DB_PASSWORD
# ... for each variable
vercel redeploy
```

---

## 📋 Required Variables Checklist

- [ ] DB_CONNECTION
- [ ] DB_HOST  
- [ ] DB_PORT
- [ ] DB_DATABASE
- [ ] DB_USERNAME
- [ ] DB_PASSWORD
- [ ] DB_SSLMODE
- [ ] APP_ENV
- [ ] APP_DEBUG
- [ ] APP_KEY
- [ ] APP_URL

---

## ✅ After Adding Variables

1. Vercel auto-deploys
2. Wait 2-3 minutes for build
3. Check deployment status: https://vercel.com/dashboard
4. Run migrations: `vercel shell → php artisan migrate --force`
5. Visit: https://your-app.vercel.app

---

## 🎯 Status

- [ ] Add environment variables to Vercel
- [ ] Verify build succeeds
- [ ] Run migrations
- [ ] Test login
- [ ] Check app works

**Do this NOW!** ⚡

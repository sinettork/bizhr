# BizHR Deployment Readiness Checklist

## 🚀 Option A: Separate Services (Recommended)

### Frontend (Vercel)
- [x] `vercel.json` created ✅
- [x] Vite build tested ✅  
- [x] `.vercelignore` configured ✅
- [x] `VERCEL_DEPLOYMENT.md` ready ✅
- [ ] Environment variables configured in Vercel dashboard
- [ ] GitHub connected to Vercel
- [ ] Deploy with `vercel --prod`

### Backend (Railway)
- [x] `Dockerfile` created ✅
- [x] Docker config (nginx, supervisord) ✅
- [x] `.env.production.example` updated ✅
- [x] `RAILWAY_DEPLOYMENT.md` ready ✅
- [ ] Railway account created
- [ ] PostgreSQL database provisioned
- [ ] Redis cache provisioned  
- [ ] AWS S3 bucket configured
- [ ] SMTP provider configured (SendGrid, etc.)
- [ ] Deploy with `railway up`

---

## 📋 Deployment Steps

### Phase 1: Backend (Railway) - First!
1. Create Railway account
2. Run: `npm i -g @railway/cli && railway login`
3. Run: `railway init` in project root
4. Add PostgreSQL & Redis services
5. Configure environment variables (see `.env.production.example`)
6. Deploy: `railway up`
7. Verify: `curl https://your-api.up.railway.app/health`

### Phase 2: Frontend (Vercel) - After Backend
1. Create Vercel account
2. Option A: Connect GitHub repo to Vercel dashboard
   - OR Option B: Run `npm i -g vercel && vercel --prod`
3. Set `VITE_API_URL` env var in Vercel dashboard
4. Verify deployment at `https://your-app.vercel.app`

### Phase 3: Connect & Test
1. Update backend `FRONTEND_URL` env var: `https://your-app.vercel.app`
2. Test API calls from frontend
3. Test user authentication flow
4. Run: `npm run build` locally to verify
5. Check browser console for CORS/API errors

---

## 🔒 Security Checklist

- [ ] `APP_DEBUG=false` in production `.env`
- [ ] `APP_KEY` generated and stored securely
- [ ] Database credentials use strong passwords
- [ ] Redis password configured
- [ ] AWS S3 credentials for uploads
- [ ] CORS configured to allow only your Vercel domain
- [ ] SSL certificates (auto on Railway/Vercel)
- [ ] Trusted proxies configured for IP logging
- [ ] Session/cookie settings use HTTPS
- [ ] No secrets in `.env.production.example`

---

## 📦 Environment Variables

### Vercel (Frontend)
```
VITE_API_URL=https://your-api.up.railway.app
VITE_APP_NAME=BizHR
```

### Railway (Backend)
```
APP_ENV=production
APP_DEBUG=false
APP_KEY=<generate>
APP_URL=https://your-api.up.railway.app
FRONTEND_URL=https://your-app.vercel.app

DB_CONNECTION=pgsql
DB_HOST=railway-internal-host
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=<strong-password>

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=railway-internal-host
REDIS_PASSWORD=<strong-password>

AWS_ACCESS_KEY_ID=<s3-key>
AWS_SECRET_ACCESS_KEY=<s3-secret>
AWS_BUCKET=<bucket-name>

MAIL_HOST=smtp.sendgrid.net
MAIL_USERNAME=apikey
MAIL_PASSWORD=<sendgrid-api-key>
```

---

## 🧪 Testing Pre-Deployment

### Local Testing
```bash
# Test build
npm run build

# Test Laravel build
php artisan config:cache
php artisan route:cache

# Run tests (if applicable)
npm run test
composer test
```

### Post-Deployment Testing
1. [ ] Frontend loads at Vercel URL
2. [ ] API calls reach backend
3. [ ] Database queries work (check logs)
4. [ ] File uploads to S3 work
5. [ ] Emails send via SMTP
6. [ ] Sessions persist across page reloads
7. [ ] Queue jobs process (if used)
8. [ ] Cache hits work

---

## 📊 Cost Estimates

| Service | Tier | Monthly Cost |
|---------|------|--------------|
| Vercel | Free/Pro | $0-20 |
| Railway PostgreSQL | Basic | $5-10 |
| Railway Redis | Basic | $5-10 |
| AWS S3 + Transfer | Usage | $5-50+ |
| SendGrid (Email) | Free/Paid | $0-40+ |
| **Total** | | **$15-130+** |

---

## 🔧 Troubleshooting

### Frontend won't connect to backend
- Check `VITE_API_URL` is set in Vercel dashboard
- Check backend CORS configuration
- Verify backend is running: `curl https://your-api.up.railway.app/health`

### Database migration fails on Railway
```bash
railway shell
php artisan migrate --force
```

### Queue jobs not processing
- Check Supervisor is running: `railway shell → supervisorctl status`
- Monitor: `php artisan queue:failed`

### Build fails on Vercel
- Check `vercel.json` build command matches `package.json`
- Run locally: `npm run build`
- Check logs: `vercel logs`

---

## 📚 Documentation

- **VERCEL_DEPLOYMENT.md** - Frontend setup details
- **RAILWAY_DEPLOYMENT.md** - Backend setup details

---

## ✅ Final Pre-Launch Checklist

- [ ] All environment variables configured
- [ ] Migrations run on production database
- [ ] Backups configured
- [ ] Monitoring set up (error tracking, uptime)
- [ ] Performance tested (Lighthouse, load testing)
- [ ] Security audit completed
- [ ] User acceptance testing done
- [ ] Team trained on deployment procedures
- [ ] Rollback plan documented
- [ ] On-call support team assigned

---

**Last Updated:** 2026-08-05  
**Status:** Deployment ready ✅

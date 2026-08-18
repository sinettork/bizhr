# BizHR Vercel Frontend Deployment Guide

## Architecture
```
┌──────────────────────────────┐
│   Vercel Frontend            │
│  ├─ Vite build artifacts     │
│  └─ Static assets            │
│   (this deployment)          │
└────────────┬─────────────────┘
             │ API calls (HTTPS)
             ▼
┌──────────────────────────────┐
│   Railway Backend            │
│  ├─ Laravel API              │
│  ├─ PostgreSQL DB            │
│  └─ Redis Cache              │
│   (separate deployment)      │
└──────────────────────────────┘
```

## Prerequisites
1. Vercel account (https://vercel.com)
2. GitHub repository connected to Vercel
3. Railway backend deployed (see RAILWAY_DEPLOYMENT.md)
4. Frontend API URL (e.g., https://your-api.up.railway.app)

## Step 1: Prepare for Deployment

### Install Dependencies
```bash
npm ci
```

### Build Frontend Assets
```bash
npm run build
```
✓ Assets will be in `public/build/`

## Step 2: Connect to Vercel

### Option A: CLI Deployment
```bash
npm i -g vercel
vercel login
vercel
```

### Option B: GitHub Integration (Recommended)
1. Push code to GitHub
2. Go to vercel.com/dashboard
3. Click "Add New..." → "Project"
4. Import your GitHub repository
5. Vercel auto-detects settings from `vercel.json`

## Step 3: Configure Environment Variables

In Vercel Dashboard (Project Settings → Environment Variables):

```
VITE_API_URL=https://your-api.up.railway.app
APP_NAME=BizHR
```

The `VITE_API_URL` must be accessible from the browser.

## Step 4: Deployment Options

### Automatic (Recommended)
- Every push to `main` auto-deploys
- Pull requests get preview deployments
- Rollback via Vercel dashboard

### Manual
```bash
vercel --prod
```

## Step 5: Configure CORS (Backend)

Your Laravel backend must allow requests from Vercel domain:

**config/cors.php:**
```php
'allowed_origins' => [
    env('FRONTEND_URL', 'http://localhost:3000'),
],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

## Step 6: Frontend API Configuration

Update your frontend to use the environment variable:

**resources/js/api.js (or your API client):**
```javascript
const apiBaseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000';

// Use this for all API calls
fetch(`${apiBaseUrl}/api/endpoint`, { ... })
```

## Troubleshooting

### Build Fails
```bash
npm run build  # Test locally first
```
- Check `vercel.json` build command
- Verify `node_modules` installed
- Check console for errors

### API Calls Return 404/CORS Error
- Verify `VITE_API_URL` is set correctly in Vercel dashboard
- Check backend CORS configuration
- Verify backend is running on Railway
- Test with `curl https://your-api.up.railway.app/health`

### Asset Files Not Loading
- Vercel serves from `public/build/`
- Ensure `npm run build` completes successfully
- Check `public/build/manifest.json` exists

### Static Assets (CSS/JS) Return 404
- These are served from `public/build/`
- Vercel automatically handles this with `vercel.json`
- Clear browser cache: Cmd+Shift+Delete

## Monitoring & Logs

### View Deployment Logs
```bash
vercel logs
```

### Real-time Logs
- Vercel Dashboard → Deployments → View logs

### Check Frontend Build Output
- Vercel Dashboard → Deployments → Build logs

## Performance Tips

1. **Enable Compression** (automatic on Vercel)
2. **Optimize Images** - Use optimized images in `resources/`
3. **Lazy Load Assets** - Use Vite's dynamic imports
4. **Cache Headers** (configured in `vercel.json`)

## Rollback

```bash
# Via CLI
vercel --prod --target production

# Via Dashboard
Deployments → Select version → Redeploy
```

## Cost
- **Free tier**: 1 deployment per second, sufficient for most projects
- **Pro**: $20/month for advanced features
- **Vercel for GitHub**: Auto-included for open source

## Next Steps
1. Deploy backend to Railway (RAILWAY_DEPLOYMENT.md)
2. Verify backend is running: `curl https://your-api.up.railway.app/health`
3. Deploy frontend: `vercel --prod`
4. Test user flows end-to-end
5. Configure custom domain in Vercel dashboard

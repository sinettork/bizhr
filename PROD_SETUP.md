# BizHR Production Setup Guide

This document outlines the steps to move BizHR from local development (SQLite) to a production-ready environment (PostgreSQL/MySQL).

## 1. Database Migration (PostgreSQL Recommended)
- Change `.env` to use PostgreSQL:
  ```env
  DB_CONNECTION=pgsql
  DB_HOST=your-db-host
  DB_PORT=5432
  DB_DATABASE=bizhr
  DB_USERNAME=your-user
  DB_PASSWORD=your-password
  ```
- Install required PHP extension: `php-pgsql`
- Run migrations: `php artisan migrate --force`

## 2. Security & HTTPS
- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Configure your web server (Nginx/Apache) with a valid SSL certificate (Let's Encrypt).
- Set `FORCE_HTTPS=true` in `.env` if using a load balancer.

## 3. Redis Configuration
- Use Redis for Cache, Session, and Queue in production for better performance:
  ```env
  CACHE_STORE=redis
  SESSION_DRIVER=redis
  QUEUE_CONNECTION=redis
  
  REDIS_HOST=127.0.0.1
  REDIS_PASSWORD=null
  REDIS_PORT=6379
  ```

## 4. Document Storage
- BizHR uses `storage/app/private` for sensitive documents.
- Ensure the `storage` directory has write permissions for the web server user.
- For multi-server setups, use S3-compatible storage.

## 5. Automated Backups
- The system is configured to back up the database daily at 01:00.
- Ensure the system cron is running:
  ```bash
  * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
  ```
- Backups are stored in `storage/backups`. Move them to an off-site location regularly.

## 6. Performance Optimization
- Run these commands during deployment:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  npm run build
  ```

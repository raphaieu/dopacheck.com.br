# Deployment Guide: DOPA Check

_Overview of production infrastructure and deployment processes._

## Infrastructure
- **Web Server**: FrankenPHP / Nginx with Laravel Octane.
- **Runtime**: PHP 8.3 running on Docker.
- **Database**: Managed MySQL (Production) / Dockerized (Dev/Staging).
- **Storage**: S3-Compatible (MinIO / Cloudflare R2).

## Deployment Steps
1. **Build Assets**:
   ```bash
   npm run build
   ```
2. **Environment Verification**:
   Ensure `APP_ENV=production` and `STRIPE_KEY` are configured.
3. **Migrations**:
   ```bash
   php artisan migrate --force
   ```
4. **Optimization**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## CI/CD (Planned)
- Use GitHub Actions for automated testing and deployment to production VPS/Cloud.

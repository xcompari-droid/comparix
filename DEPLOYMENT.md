# Deployment Checklist pentru Comparix.ro

## Pre-Deployment

### 1. Pregătire Cod
- [ ] Rulează `composer install --optimize-autoloader --no-dev`
- [ ] Rulează `npm run build` pentru assets
- [ ] Generează APP_KEY nou: `php artisan key:generate`
- [ ] Setează `APP_ENV=production` și `APP_DEBUG=false`

### 2. Configurare Bază de Date
- [ ] Creează baza MySQL/PostgreSQL pe server
- [ ] Actualizează credențialele în `.env`
- [ ] Rulează migrations: `php artisan migrate --force`
- [ ] Seed admin user: `php setup-admin.php`

### 3. Storage & Permissions
```bash
php artisan storage:link
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 4. Optimizări Producție
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 5. Queue Worker (Important pentru import-uri)
Adaugă în cron sau supervisor:
```bash
php artisan queue:work --tries=3 --timeout=300
```

### 6. Scheduler (Pentru sync-uri automate)
Adaugă în crontab:
```bash
* * * * * cd /path/to/comparix && php artisan schedule:run >> /dev/null 2>&1
```

---

## Opțiuni de Hosting

### A. Laravel Forge + DigitalOcean (Recomandat)

**Cost:** ~$18-30/lună ($12 Forge + $6-18 server)

**Pași:**
1. **Creează cont pe [forge.laravel.com](https://forge.laravel.com)**
2. **Conectează DigitalOcean/AWS/Vultr**
3. **Creează server nou:**
   - Tip: App Server
   - Size: 1GB RAM (minimum) sau 2GB (recomandat)
   - Database: MySQL 8.0
   - Adaugă Redis (pentru cache/queue)
4. **Creează site:**
   - Domain: comparix.ro
   - Root: `/public`
   - PHP Version: 8.4
5. **Conectează Git repository:**
   - Repository: `xcompari-droid/comparix`
   - Branch: `main`
   - Deploy pe push: Da
6. **Configurare Environment:**
   - Copiază `.env.production.example`
   - Generează APP_KEY
   - Setează DB credentials (Forge le generează automat)
7. **SSL Certificate:**
   - Forge instalează automat Let's Encrypt
8. **Deploy Script (Forge îl generează, îl poți modifica):**
```bash
cd /home/forge/comparix.ro
git pull origin main
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan storage:link
```

9. **Configurare Queue Worker în Forge:**
   - Adaugă Daemon: `php artisan queue:work --tries=3`
10. **Configurare Scheduler:**
    - Forge îl activează automat

**Avantaje:**
- Setup complet în 10 minute
- SSL automat
- Deploy pe git push
- Monitoring inclus
- Backup-uri automate (opțional)

---

### B. Ploi.io (Alternativă mai ieftină)

**Cost:** ~€10-20/lună

**Pași similari cu Forge:**
1. Cont pe [ploi.io](https://ploi.io)
2. Conectează server provider
3. Creează server + site
4. Conectează Git + deploy

---

### C. Manual pe VPS (Pentru experiență)

**Provideri:** DigitalOcean, Vultr, Linode, Hetzner
**Cost:** $6-12/lună

**Stack necesar:**
- Ubuntu 22.04/24.04
- Nginx
- PHP 8.4 + extensii (php-fpm, php-mysql, php-redis, php-xml, php-mbstring, php-curl, php-zip, php-gd)
- MySQL 8.0 / PostgreSQL 16
- Redis
- Composer
- Node.js + NPM
- Meilisearch
- Supervisor (pentru queue)
- Certbot (pentru SSL)

**Script instalare automată:**
```bash
# Voi crea un deployment script dacă alegi această variantă
```

---

### D. Hosting Partajat (NU RECOMANDAT pentru acest proiect)

**Probleme:**
- PHP 8.4 poate nu e disponibil
- Nu poți rula queue workers
- Nu poți rula Meilisearch
- Limitări la cron jobs
- Performance slab pentru import-uri
- Nu poți instala Redis

---

## Post-Deployment

### 1. Testare Funcționalitate
- [ ] Login admin panel: https://comparix.ro/admin
- [ ] Test import feed: `php artisan feed:import --file=test.csv`
- [ ] Verifică queue procesează jobs
- [ ] Test search (după ce instalezi Meilisearch)

### 2. Monitoring
- [ ] Configurează New Relic / Sentry pentru errors
- [ ] Monitorizare uptime (UptimeRobot gratuit)
- [ ] Log rotation pentru `storage/logs`

### 3. Performance
- [ ] Activează OPcache în php.ini
- [ ] Configurează Nginx caching pentru assets
- [ ] CDN pentru imagini (Cloudflare gratuit)

### 4. Securitate
- [ ] Firewall: permite doar 80, 443, 22
- [ ] SSH key authentication (disable password)
- [ ] Fail2ban pentru brute-force protection
- [ ] Regular updates: `apt update && apt upgrade`

### 5. Backup
- [ ] Backup bază de date zilnic
- [ ] Backup storage (imagini) săptămânal
- [ ] Păstrează ultimele 7 backup-uri

---

## Servicii Adiționale Necesare

### 1. Meilisearch Cloud (Recomandat)
- **Provider:** [cloud.meilisearch.com](https://cloud.meilisearch.com)
- **Cost:** €0-29/lună (depinde de volum)
- **Setup:** 5 minute, actualizezi `MEILISEARCH_HOST` în `.env`

### 2. Object Storage pentru Imagini
- **S3 / DigitalOcean Spaces / Cloudflare R2**
- **Cost:** ~$5/lună pentru 250GB
- **Config:** Actualizează `FILESYSTEM_DISK=s3` în `.env`

### 3. Email Sending
- **Mailgun:** 5000 emails/lună gratuit
- **Amazon SES:** $0.10 per 1000 emails
- **Setup:** Actualizează credentials în `.env`

---

## Recomandarea Mea pentru Comparix.ro

**Pentru început (MVP):**
1. **Laravel Forge** ($12/lună) + **DigitalOcean Droplet 2GB** ($12/lună) = **$24/lună**
2. **Meilisearch Cloud** - plan gratuit până la 100k documente
3. **DigitalOcean Spaces** - $5/lună pentru imagini
4. **Mailgun** - gratuit pentru 5k emails/lună

**Total cost lunar: ~$29-35**

**Când crești (>10k vizitatori/zi):**
- Upgrade server la 4GB RAM ($24/lună)
- Meilisearch Cloud paid ($29/lună)
- CDN (Cloudflare Pro $20/lună)
- Redis separate instance

---

## Următorii Pași

**Ce vrei să fac acum?**

1. **Pregătesc deployment pe Forge?** (îți dau pașii exacti)
2. **Creez script pentru deployment manual pe VPS?**
3. **Configurăm GitHub Actions pentru CI/CD?**
4. **Altceva?**

Spune-mi ce variantă preferi și te ajut să urcăm site-ul live! 🚀

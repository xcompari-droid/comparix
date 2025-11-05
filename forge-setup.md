# Setup Comparix pe Forge Server: 157.230.77.46

## Pași Rapidi

### 1. Verifică PHP Version pe Server

Conectează-te la server și verifică:
```bash
ssh forge@157.230.77.46
php -v
```

**Dacă ai PHP < 8.4:**
- Intră în Forge → Server 157.230.77.46 → PHP
- Instalează PHP 8.4 (dacă disponibil)
- SAU modifică `composer.json` să accepte PHP 8.3

### 2. Creează Site în Forge

**În Forge Dashboard:**
1. Click pe server **157.230.77.46**
2. Click **New Site**
3. Completează:
   - **Root Domain:** `comparix.ro` (sau subdomain pentru test)
   - **Aliases:** `www.comparix.ro` (opțional)
   - **Project Type:** Laravel
   - **Web Directory:** `/public`
   - **PHP Version:** 8.4 (sau cea mai mare disponibilă)
4. Click **Add Site**

### 3. Configurează DNS (Dacă ai domeniul)

**La provider-ul de DNS (Cloudflare/GoDaddy/etc):**
```
Type: A
Name: @
Value: 157.230.77.46
TTL: Automatic

Type: A  
Name: www
Value: 157.230.77.46
TTL: Automatic
```

### 4. Conectează GitHub Repository

**În Site Settings → Git Repository:**
1. Click **Install Repository**
2. Completează:
   - **Provider:** GitHub
   - **Repository:** `xcompari-droid/comparix`
   - **Branch:** `main`
   - **Install Composer Dependencies:** ✅
3. Click **Install Repository**

Forge va:
- Clona repo-ul
- Rula `composer install`
- Crea structura de foldere

### 5. Configurează Environment (.env)

**În Site → Environment:**

Copiază și completează:
```env
APP_NAME=Comparix
APP_ENV=production
APP_DEBUG=false
APP_URL=https://comparix.ro

LOG_CHANNEL=stack
LOG_LEVEL=error

# Database (Forge le setează automat)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forge
DB_USERNAME=forge
DB_PASSWORD=GENERAT_DE_FORGE

# Cache & Queue
CACHE_STORE=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Filesystem
FILESYSTEM_DISK=public

# Scout & Meilisearch
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=

# Mail
MAIL_MAILER=log
MAIL_FROM_ADDRESS=no-reply@comparix.ro
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

Apoi:
1. Click **Save**
2. Click butonul **Generate App Key**

### 6. Creează Database

**În Forge → Server → Database:**
1. Click **New Database**
2. Name: `comparix` (sau folosește `forge` default)
3. Click **Add Database**

**Actualizează .env cu numele DB:**
```env
DB_DATABASE=comparix
```

### 7. Modifică Deploy Script

**În Site → Deployments:**

Înlocuiește scriptul cu:
```bash
cd /home/forge/comparix.ro
git pull origin $FORGE_SITE_BRANCH

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

if [ -f artisan ]; then
    $FORGE_PHP artisan migrate --force
    $FORGE_PHP artisan config:cache
    $FORGE_PHP artisan route:cache
    $FORGE_PHP artisan view:cache
    $FORGE_PHP artisan event:cache
    $FORGE_PHP artisan storage:link
    $FORGE_PHP artisan queue:restart
fi

# Build frontend assets
npm ci
npm run build

# Cleanup
$FORGE_PHP artisan optimize
```

### 8. Configurează Queue Worker

**În Site → Queue:**
1. Click **New Worker**
2. Completează:
   - **Connection:** redis
   - **Queue:** default
   - **Processes:** 1
   - **Timeout:** 300
   - **Sleep:** 3
   - **Tries:** 3
3. Click **Create Worker**

### 9. Verifică Scheduler

**În Site → Scheduler:**
- Ar trebui deja activat
- Verifică că există cron job: `* * * * * php artisan schedule:run`

### 10. Instalează SSL Certificate

**În Site → SSL:**
1. Click **LetsEncrypt**
2. Domenii: `comparix.ro,www.comparix.ro`
3. Click **Obtain Certificate**

Forge va instala automat certificatul și va redirecta HTTP → HTTPS.

### 11. DEPLOY!

**În Site Dashboard:**
1. Click butonul mare verde **Deploy Now**
2. Monitorizează în **Deployment History**
3. Verifică că deployment e SUCCESS (verde)

### 12. Rulează Migrations & Setup Admin

**Opțiunea 1 - Din Forge SSH Terminal:**
```bash
cd /home/forge/comparix.ro
php artisan migrate --force
php setup-admin.php
```

**Opțiunea 2 - Conectare SSH directă:**
```bash
ssh forge@157.230.77.46
cd comparix.ro
php artisan migrate --force
php setup-admin.php
```

### 13. (Opțional) Instalează Meilisearch pe Server

**Din SSH:**
```bash
# Download & Install
curl -L https://install.meilisearch.com | sh
sudo mv ./meilisearch /usr/local/bin/

# Generate master key
MASTER_KEY=$(openssl rand -base64 32)
echo "MEILISEARCH_KEY=$MASTER_KEY"

# Create systemd service
sudo tee /etc/systemd/system/meilisearch.service << EOF
[Unit]
Description=Meilisearch
After=network.target

[Service]
Type=simple
User=forge
ExecStart=/usr/local/bin/meilisearch --http-addr 127.0.0.1:7700 --env production --master-key $MASTER_KEY
Restart=always

[Install]
WantedBy=multi-user.target
EOF

# Start service
sudo systemctl enable meilisearch
sudo systemctl start meilisearch
sudo systemctl status meilisearch
```

**Actualizează .env în Forge:**
```env
MEILISEARCH_KEY=CHEIA_GENERATA_MAI_SUS
```

Apoi redeploy sau rulează:
```bash
php artisan config:clear
php artisan reindex:search
```

---

## ✅ Verificări Post-Deploy

### 1. Test Site Funcționează
```bash
curl -I https://comparix.ro
# Ar trebui să vezi: HTTP/2 200
```

### 2. Test Admin Login
- Navighează la: https://comparix.ro/admin
- Login cu: admin@comparix.ro / password

### 3. Test Queue Worker
```bash
# Verifică că worker-ul rulează
sudo supervisorctl status

# Ar trebui să vezi:
# comparix.ro-queue:comparix.ro-queue_00   RUNNING
```

### 4. Check Logs pentru Erori
```bash
tail -f /home/forge/comparix.ro/storage/logs/laravel.log
```

### 5. Test Import
```bash
cd /home/forge/comparix.ro
php artisan feed:import --help
```

---

## 🔧 Troubleshooting Comun

### Eroare: "Class not found" după deploy
```bash
cd /home/forge/comparix.ro
composer dump-autoload --optimize
php artisan config:clear
php artisan cache:clear
```

### Assets (CSS/JS) nu se încarcă
```bash
cd /home/forge/comparix.ro
npm run build
php artisan storage:link
```

### Permission errors
```bash
cd /home/forge
sudo chown -R forge:forge comparix.ro
chmod -R 755 comparix.ro/storage
chmod -R 755 comparix.ro/bootstrap/cache
```

### Database connection failed
- Verifică credentials în `.env`
- Verifică că database există în Forge → Database
- Test connection: `php artisan tinker` apoi `DB::connection()->getPdo();`

---

## 📊 Next Steps După Deploy

1. **Populează cu Date Test:**
```bash
php artisan db:seed --class=TestDataSeeder
```

2. **Import Feed 2Performant:**
- Urcă CSV via SFTP în `/home/forge/feeds/`
- Rulează: `php artisan feed:import --file=/home/forge/feeds/test.csv --type=csv`

3. **Setup Monitoring:**
- În Forge: Enable Server Monitoring
- Adaugă Uptime Monitoring (Forge sau UptimeRobot)

4. **Backup:**
- În Forge → Server → Backups
- Enable Daily Backups pentru database

---

## 🚀 Quick Deploy Command Summary

```bash
# După modificări în cod, din local:
git add .
git commit -m "Update"
git push origin main

# Forge va deploy automat (dacă Quick Deploy e activat)
# SAU click "Deploy Now" în Forge
```

---

Gata de deploy? Spune-mi când ești ready și te ghidez pas cu pas! 🎯

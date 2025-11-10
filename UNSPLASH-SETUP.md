# 📸 GHID RAPID UNSPLASH API
# ==========================

## 🎯 CE TREBUIE SĂ FACI:

### 1️⃣ OBȚINE API KEY (5 minute)

1. Mergi pe: https://unsplash.com/developers
2. Click "Register as a developer"
3. Creează cont (email + parolă)
4. Click "New Application"
5. Acceptă termenii
6. Completează:
   - Application name: **Comparix**
   - Description: **Product comparison website for Romanian market**
7. Submit → Primești **Access Key**

---

### 2️⃣ CONFIGURARE .env

Deschide `.env` și adaugă la final:

```env
# Unsplash API Configuration
UNSPLASH_ACCESS_KEY=paste_your_access_key_here
UNSPLASH_SECRET_KEY=paste_your_secret_key_here
```

**Important:** Înlocuiește `paste_your_access_key_here` cu key-ul tău real!

---

### 3️⃣ VERIFICARE SETUP

Testează că totul funcționează:

```bash
php test-unsplash-api.php
```

Ar trebui să vezi:
- ✅ API Key găsit
- ✅ Conexiune OK
- ✅ Imagini găsite pentru produse
- ✅ Storage OK

---

### 4️⃣ CREEAZĂ STORAGE LINK (dacă e necesar)

```bash
php artisan storage:link
```

Asta creează link între `storage/app/public` și `public/storage`

---

### 5️⃣ RULEAZĂ IMPORT

```bash
php import-unsplash-images.php
```

Scriptul va:
- Căuta 10 produse per categorie (mașini spălat, frigidere, căști, smartwatch)
- Găsi imagini profesionale HD pe Unsplash
- Descărca imaginile local în `storage/app/public/products/`
- Actualiza database cu noul URL
- Respecta rate limiting (3 secunde între requests)

---

## 📊 RATE LIMITS

**Plan Gratuit:**
- ✅ 50 requests/oră
- ✅ Ideal pentru development
- ✅ ~10-15 produse/oră

**Plan Demo (gratuit cu atribuire):**
- ✅ 50 requests/oră
- ✅ Unlimited downloads
- ✅ Trebuie credit fotograf

**Plan Plus ($20/lună):**
- ✅ 5000 requests/oră
- ✅ Pentru producție

---

## ⚖️ TERMENI UNSPLASH (IMPORTANT!)

### ✅ PERMIS:
- Folosire comercială
- Modificare imagini
- Download și hosting propriu
- Nu trebuie să plătești fotografii

### ❌ OBLIGATORIU:
Trebuie să adaugi credit în footer:

```html
<!-- resources/views/layouts/app.blade.php -->
<footer>
    <div class="container">
        <p>
            Product images from 
            <a href="https://unsplash.com/?utm_source=comparix&utm_medium=referral">
                Unsplash
            </a>
        </p>
    </div>
</footer>
```

---

## 🔍 VERIFICARE REZULTATE

După import, verifică:

```bash
php check-all-images.php
```

Ar trebui să vezi:
- Mașini de spălat: 10 imagini locale
- Frigidere: 10 imagini locale
- Căști: 10 imagini locale
- Smartwatch: 10 imagini locale

---

## 💡 TROUBLESHOOTING

### ❌ Eroare: "API Key not configured"
**Soluție:** Verifică că ai adăugat `UNSPLASH_ACCESS_KEY` în `.env`

### ❌ Eroare: "Rate limit exceeded"
**Soluție:** Așteaptă 1 oră sau upgrade la plan Plus

### ❌ Eroare: "Failed to download"
**Soluție:** Verifică conexiunea internet și că `storage/app/public/products/` există

### ❌ Link simbolic lipsește
**Soluție:** Rulează `php artisan storage:link`

---

## 📈 PLAN RECOMANDAT

**Săptămâna 1 (ACUM):**
- ✅ Site live cu placeholder-uri branded
- ✅ Toate specs 100% complete

**Weekend:**
- 📸 Obține Unsplash API key
- 📸 Import 40 imagini (10 per categorie top)
- 📸 Verificare vizuală

**Luna 2:**
- 🏪 Integrare 2Performant (affiliate feeds)
- 🏪 Import automat zilnic
- 🏪 Imagini reale pentru toate produsele

---

## ✅ CHECKLIST

- [ ] Am creat cont pe Unsplash Developers
- [ ] Am copiat Access Key
- [ ] Am adăugat în `.env`
- [ ] Am rulat `php test-unsplash-api.php` (SUCCESS)
- [ ] Am rulat `php artisan storage:link`
- [ ] Am rulat `php import-unsplash-images.php`
- [ ] Am verificat cu `php check-all-images.php`
- [ ] Am adăugat credit Unsplash în footer

---

## 🎉 GATA!

Dacă toate checklist-urile sunt bifate, ai 40+ imagini profesionale HD pe site!

Site-ul tău arată 10x mai bine și e 100% legal! 🚀

# 🚀 GHID RAPID: Google Custom Search API pentru Imagini

## ⏱️ Setup în 5 MINUTE

### Pasul 1: API Key (2 minute)

1. **Mergi la**: https://console.cloud.google.com/
2. **Creează proiect**: "Comparix" (dacă nu ai)
3. **Activează API**:
   - Caută "Custom Search API"
   - Click ENABLE
4. **Creează Credentials**:
   - Credentials (stânga) → Create Credentials → API Key
   - **COPIAZĂ KEY-ul**: `AIzaSyC...`

### Pasul 2: Custom Search Engine (2 minute)

1. **Mergi la**: https://programmablesearchengine.google.com/controlpanel/create
2. **Completează**:
   - Name: "Comparix Product Images"
   - What to search: **Search the entire web** ✅
3. **Setări importante** (click Edit după creare):
   - Image search: **ON** ✅
   - SafeSearch: **OFF**
4. **COPIAZĂ ID-ul**: `017576662...` (cx= din URL sau din Overview)

### Pasul 3: Adaugă în .env (1 minut)

Deschide `.env` și adaugă:

```bash
# Google Custom Search API
GOOGLE_API_KEY=AIzaSyC...tau-key-aici
GOOGLE_SEARCH_ENGINE_ID=017576662...tau-id-aici
```

## ✅ TESTEAZĂ (30 secunde)

```bash
php test-google-images.php
```

Ar trebui să vezi:
```
✅ API Key găsit: AIzaSyC...
✅ Search Engine ID: 017576662...

🔍 Căutare: Samsung RB38A7B6AS9/EF (frigider)
   ✅ Găsit!
   📷 URL: https://...
   📐 Dimensiuni: 1200x1200px
   ⭐ Scor: 80/100
```

## 🎯 IMPORTĂ IMAGINI (când e OK)

```bash
php import-google-images.php
```

## 💰 COST

| Queries | Cost |
|---------|------|
| 0-100/zi | **GRATUIT** |
| 101-10,000/zi | $5/1000 |

**Pentru 284 produse**: 
- Gratis dacă faci ~95 pe zi × 3 zile
- SAU ~$1.42 dacă faci toate odată

## ⚠️ PROBLEME COMUNE

### "No results found"
- Verifică că **Image Search** este ON
- Verifică că **Search the entire web** este selectat

### "API key not valid"
- API-ul este ENABLED în Google Cloud Console?
- Key-ul este copiat corect (fără spații)?

### "Daily limit exceeded"
- Ai depășit 100 query gratuite
- Așteaptă până mâine SAU plătește $5/1000

## 📞 AJUTOR

Vezi documentația completă:
- Setup: GOOGLE-IMAGES-SETUP.md
- Logs: storage/logs/laravel.log

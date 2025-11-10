# Google Custom Search API - Setup Guide

## 🎯 De ce Google?

✅ **100 query/zi GRATUIT** = 3000/lună
✅ Găsește imagini după cod exact produs (RB38A7B6AS9/EF)
✅ Imagini de pe toate site-urile (Samsung, LG, Altex, eMAG, etc.)
✅ API oficial Google - stabil și rapid
✅ Perfect pentru 284 produse

## 📋 Pași Setup (5 minute)

### 1. Creează API Key (GRATUIT)

**Link**: https://console.cloud.google.com/apis/credentials

1. Mergi la Google Cloud Console
2. Creează proiect nou: "Comparix"
3. Activează "Custom Search API"
4. Credentials → Create Credentials → API Key
5. **Copiază API Key-ul**

### 2. Creează Custom Search Engine

**Link**: https://programmablesearchengine.google.com/

1. Click "Add" pentru search engine nou
2. **Setări importante**:
   - Search the entire web: **ON** ✅
   - Image search: **ON** ✅
   - SafeSearch: **OFF** (pentru produse)
3. **Copiază Search Engine ID** (cx=...)

### 3. Adaugă în .env

```bash
GOOGLE_API_KEY=AIzaSyC...voastrulkey
GOOGLE_SEARCH_ENGINE_ID=017576662...voastrulcx
```

## 💰 Cost

- **0-100 query/zi**: GRATUIT
- **101-10,000/zi**: $5 per 1000 query
- **Pentru 284 produse**: GRATUIT (sub 100)

## 🚀 Implementare

Folosește `GoogleImageService.php` pentru căutare:
```php
$service = new GoogleImageService();
$imageUrl = $service->searchProductImage('Samsung RB38A7B6AS9/EF', 'frigider');
```

## 📊 Rate Limits

- 100 queries/zi GRATUIT
- 10,000 queries/zi MAX
- Recommended: 50-80 queries/zi pentru siguranță

## ✅ Next Steps

1. Obține API Key + Search Engine ID
2. Adaugă în .env
3. Rulează `php test-google-images.php`
4. Importă imagini: `php import-google-images.php`

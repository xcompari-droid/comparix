# 🔑 PAS CU PAS: Cum să Obții Google API Key

## 📍 PASUL 1: Intră în Google Cloud Console

**Link direct**: https://console.cloud.google.com/

- Loghează-te cu contul Google
- Dacă e prima dată, acceptă Terms of Service

---

## 📍 PASUL 2: Creează Proiect (dacă nu ai)

### Opțiunea A: Ai deja proiect
- Sari la PASUL 3

### Opțiunea B: Nu ai proiect
1. Sus în header, click pe **"Select a project"**
2. Click **"NEW PROJECT"**
3. **Project name**: `Comparix` (sau orice nume)
4. Click **"CREATE"**
5. Așteaptă 10 secunde să se creeze
6. Selectează proiectul din dropdown

---

## 📍 PASUL 3: Activează Custom Search API

### Link direct: 
https://console.cloud.google.com/apis/library/customsearch.googleapis.com

SAU manual:

1. În meniul din stânga, click pe **"APIs & Services"**
2. Click pe **"Library"** (Biblioteca)
3. În search box, scrie: **"Custom Search API"**
4. Click pe **"Custom Search API"** din rezultate
5. Click butonul mare albastru **"ENABLE"** (Activează)
6. Așteaptă 5 secunde

---

## 📍 PASUL 4: Creează API Key ⭐ (CEL MAI IMPORTANT)

### Link direct:
https://console.cloud.google.com/apis/credentials

SAU manual:

1. În meniul din stânga: **"APIs & Services"** → **"Credentials"**
2. Sus, click **"+ CREATE CREDENTIALS"**
3. Din dropdown, selectează: **"API key"**

### ✅ Acum vei vedea un popup cu KEY-ul!

```
API key created
Your new API key:
AIzaSyC_xxxxxxxxxxxxxxxxxxxxxxxxxxx
[COPY] [RESTRICT KEY] [CLOSE]
```

4. **Click pe COPY** să copiezi key-ul
5. **Salvează-l temporar** în Notepad (pentru .env)

### 🔒 OPȚIONAL - Restricționează Key-ul (recomandat)

6. Click **"RESTRICT KEY"** (sau editează mai târziu)
7. În **"API restrictions"**:
   - Selectează **"Restrict key"**
   - Bifează doar **"Custom Search API"** ✅
8. Click **"SAVE"**

---

## 📍 PASUL 5: Creează Custom Search Engine

### Link direct:
https://programmablesearchengine.google.com/controlpanel/create

1. **Name**: `Comparix Product Images`
2. **What to search**:
   - Selectează: **"Search the entire web"** ✅ (IMPORTANT!)
3. Click **"Create"**

### ✅ Vei vedea mesaj de succes!

---

## 📍 PASUL 6: Configurează Search Engine pentru Imagini

1. După creare, vei fi pe pagina de Overview
2. Click **"Customize"** din sidebar
3. Scroll până la **"Image search"**
4. Toggle **ON** ✅ (IMPORTANT!)
5. Click **"Update"** jos

---

## 📍 PASUL 7: Copiază Search Engine ID

Ai 2 opțiuni:

### Opțiunea A: Din Overview
1. Click **"Overview"** în sidebar
2. Găsești **"Search engine ID"**: `017576662...`
3. Click pe icon de COPY

### Opțiunea B: Din URL
- URL-ul arată: `.../cse?cx=017576662xxxxx`
- Copiază partea după `cx=`

---

## 📍 PASUL 8: Adaugă în .env

Deschide fișierul `.env` din proiect și adaugă:

```bash
# Google Custom Search API
GOOGLE_API_KEY=AIzaSyC_xxxxxxxxxxxxxxxxxxxxxxxxxxx
GOOGLE_SEARCH_ENGINE_ID=017576662xxxxxxxxxxxxx
```

**IMPORTANT**: Înlocuiește cu KEY-urile tale reale!

---

## ✅ PASUL 9: TESTEAZĂ!

Rulează în terminal:

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
```

---

## 🚨 PROBLEME COMUNE

### Eroare: "API key not valid"
**Soluție**:
1. API-ul este ENABLED? (PASUL 3)
2. Key-ul este copiat complet? (fără spații)
3. Așteaptă 1-2 minute după creare

### Eroare: "Daily limit exceeded"
**Soluție**:
- Ai depășit 100 query gratuite astăzi
- Așteaptă până mâine
- SAU activează billing (plătești $5/1000 după primele 100)

### Nu găsește imagini
**Soluție**:
1. **Image search** este ON? (PASUL 6)
2. **Search the entire web** este selectat? (PASUL 5)
3. Refresh pagina și verifică setările

---

## 📞 LINK-URI RAPIDE

| Ce trebuie | Link direct |
|------------|-------------|
| Google Cloud Console | https://console.cloud.google.com/ |
| Enable API | https://console.cloud.google.com/apis/library/customsearch.googleapis.com |
| Create API Key | https://console.cloud.google.com/apis/credentials |
| Create Search Engine | https://programmablesearchengine.google.com/controlpanel/create |
| Manage Search Engines | https://programmablesearchengine.google.com/controlpanel/all |

---

## 💡 TIPS

1. **Salvează KEY-urile**: Pune-le în Notepad înainte să le adaugi în .env
2. **Verifică de 2 ori**: Image search = ON, Search entire web = ON
3. **Testează imediat**: Rulează test-google-images.php
4. **Rate limit**: 100 gratuit/zi = suficient pentru 284 produse în 3 zile

---

## 🎯 CE URMEAZĂ?

După ce testul funcționează:
```bash
php import-google-images.php
```

**GATA! Vei avea imagini reale pentru toate produsele!** 🎉

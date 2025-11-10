# GHID DESCĂRCARE MANUALĂ IMAGINI

## Produse care au nevoie de imagini (6 total)

### 1. Samsung Galaxy S24 Ultra
**Link imagine oficială:**
https://images.samsung.com/is/image/samsung/p6pim/ro/2401/gallery/ro-galaxy-s24-s928-sm-s928bzkgeue-539573226

**Alternative:**
- https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-s24-ultra-5g.jpg
- Caută pe Google Images: "Samsung Galaxy S24 Ultra official PNG"

**Salvează ca:** `samsung-galaxy-s24-ultra.jpg`

---

### 2. OPPO Reno 12 Pro 5G
**Link imagine:**
- https://fdn2.gsmarena.com/vv/bigpic/oppo-reno12-pro-5g.jpg
- Sau caută pe eMAG: https://www.emag.ro/search/oppo%20reno%2012%20pro

**Salvează ca:** `oppo-reno-12-pro-5g.jpg`

---

### 3. OPPO Reno 12 5G
**Link imagine:**
- https://fdn2.gsmarena.com/vv/bigpic/oppo-reno12-5g.jpg
- Sau eMAG: https://www.emag.ro/search/oppo%20reno%2012

**Salvează ca:** `oppo-reno-12-5g.jpg`

---

### 4. OPPO A3 Pro 5G
**Link imagine:**
- https://fdn2.gsmarena.com/vv/bigpic/oppo-a3-pro-5g.jpg
- Sau Google Images: "OPPO A3 Pro 5G official image"

**Salvează ca:** `oppo-a3-pro-5g.jpg`

---

### 5. Huawei Pura 70 Ultra
**Link imagine:**
- https://fdn2.gsmarena.com/vv/bigpic/huawei-pura-70-ultra.jpg
- Sau eMAG: https://www.emag.ro/search/huawei%20pura%2070%20ultra

**Salvează ca:** `huawei-pura-70-ultra.jpg`

---

### 6. Huawei Pura 70 Pro
**Link imagine:**
- https://fdn2.gsmarena.com/vv/bigpic/huawei-pura-70-pro.jpg
- Sau eMAG: https://www.emag.ro/search/huawei%20pura%2070%20pro

**Salvează ca:** `huawei-pura-70-pro.jpg`

---

### 7. Huawei Pura 70
**Link imagine:**
- https://fdn2.gsmarena.com/vv/bigpic/huawei-pura-70.jpg

**Salvează ca:** `huawei-pura-70.jpg`

---

## PAȘI DE URMAT:

### Metoda 1: Click dreapta pe link-uri (CEA MAI RAPIDĂ)
1. Click dreapta pe fiecare link de mai sus
2. "Save link as..." sau "Save image as..."
3. Navighează la: `C:\Users\calin\Documents\comparix\public\images\products\`
4. Salvează cu numele exact specificat
5. După ce ai descărcat toate, rulează: `php update-manual-images.php`

### Metoda 2: Descărcare din browser
1. Deschide link-ul în browser
2. Click dreapta pe imagine → "Save image as..."
3. Salvează în: `C:\Users\calin\Documents\comparix\public\images\products\`
4. Folosește numele exact specificat

### Metoda 3: eMAG (dacă link-urile de mai sus nu funcționează)
1. Deschide link-ul eMAG
2. Click pe primul produs
3. Click dreapta pe imaginea principală
4. "Inspect Element" sau "Inspect"
5. Găsește `<img src="https://s13emagst.akamaized.net/...">` 
6. Click dreapta pe URL → "Open in new tab"
7. Salvează imaginea

---

## VERIFICARE

După descărcare, rulează:
```bash
php update-manual-images.php
php check-image-status.php
```

Ar trebui să vezi: "✓ Real Images: 18"

---

## PROBLEME?

Dacă un link nu funcționează:
1. Caută produsul pe Google Images
2. Adaugă "official" sau "press image" la căutare
3. Selectează "Large" size
4. Descarcă imaginea cea mai profesională

# Ghid: Cum să adaugi imagini profesionale reale

## Metoda 1: Descărcare manuală (CEA MAI SIGURĂ)

### Pași:
1. **Caută produsul pe Google Images**
   - Exemplu: "Samsung Galaxy S24 Ultra official image PNG"
   - Sau mergi direct pe site-ul producătorului

2. **Descarcă imaginea**
   - Click dreapta → Save image as...
   - Salvează în: `c:\Users\calin\Documents\comparix\public\images\products\`
   - Nume fișier: folosește formatul: `samsung-galaxy-s24-ultra.jpg`

3. **Actualizează baza de date**
   - Rulează: `php update-manual-images.php`

## Metoda 2: Link-uri directe din CSV

Când importi produse noi, adaugă coloana `image_urls` în CSV cu link-uri către imaginile oficiale.

## Metoda 3: Admin Panel

1. Deschide `/admin`
2. Mergi la Products
3. Edit produs
4. Upload imagine direct

## Link-uri utile pentru imagini oficiale:

### Samsung
- https://www.samsung.com/ro/smartphones/ (secțiunea Gallery)
- https://images.samsung.com/ (CDN oficial)

### OPPO
- https://www.oppo.com/en/smartphones/
- https://image.oppo.com/ (CDN oficial)

### Huawei
- https://consumer.huawei.com/en/phones/
- https://consumer-img.huawei.com/ (CDN oficial)

## Script automat de actualizare

După ce descarci imaginile manual în `public/images/products/`, rulează:

```bash
php update-manual-images.php
```

Acest script va detecta automat imaginile noi și va actualiza baza de date.

# Icecat API Integration Guide

## 🔑 Setup Instructions

### 1. Register for Icecat Open API (FREE)

Visit: https://icecat.biz/en/menu/partners/index.html

**Free Tier Includes:**
- 100+ major brands (Samsung, LG, Bosch, Whirlpool, Beko, etc.)
- Official product images
- Full specifications (energy, dimensions, features)
- Multi-language support (Romanian available)
- JSON/XML format

### 2. Add Credentials to `.env`

```env
ICECAT_USERNAME=your_username_here
ICECAT_PASSWORD=your_password_here
```

### 3. Test Connection

```bash
php test-icecat.php
```

## 📊 API Usage Examples

### Get Product by EAN
```php
use App\Services\Importers\IcecatImporter;

$icecat = new IcecatImporter();
$product = $icecat->getProductByEAN('8806094808919');

// Returns:
[
    'name' => 'Samsung RB38A7B6AS9/EF',
    'brand' => 'Samsung',
    'mpn' => 'RB38A7B6AS9/EF',
    'ean' => '8806094808919',
    'image_url' => 'https://...',
    'specifications' => [
        'energy_class' => 'A+++',
        'total_capacity' => '385 L',
        'no_frost' => 'Yes',
        // ... 30-50+ specs
    ]
]
```

### Get Refrigerator-Specific Data
```php
$fridgeData = $icecat->getRefrigeratorSpecs(
    $ean, 
    $brand, 
    $model
);

// Extracts refrigerator-specific specs:
// - Energy class, consumption, noise
// - Capacities (total, fridge, freezer)
// - Features (No Frost, water dispenser, etc.)
// - Dimensions, weight
// - Technology (inverter, smart features)
```

## 🏗️ Integration Plan

### Phase 1: Replace Altex Manual Import ✅
1. Test Icecat with existing 10 refrigerators
2. Update `import-fridges-manual.php` to use Icecat API
3. Re-import with official data

### Phase 2: Automated Fridges Import
```bash
php artisan make:command ImportIcecatFridges
php artisan import:icecat-fridges --limit=50
```

### Phase 3: Expand to Other Categories
- Washing machines (mașini de spălat)
- Dishwashers (mașini de spălat vase)
- Ovens (cuptoare)
- Air conditioners (aparate de aer condiționat)
- TVs (televizoare)
- Small appliances

### Phase 4: Icecat for Existing Products
- Match existing products by EAN/MPN
- Enrich smartphones with official specs
- Add missing images from Icecat

## 🎯 Advantages Over Current Methods

### vs Altex Scraping:
- ✅ Official data (no copyright issues)
- ✅ Standardized format
- ✅ No CORS/scraping issues
- ✅ More detailed specifications
- ✅ Multi-language support

### vs Versus.com Puppeteer:
- ✅ Instant response (no 7-10 second wait)
- ✅ Official product data
- ✅ No JavaScript rendering needed
- ✅ Reliable API (no HTML parsing)
- ✅ Better structured data

### vs Manual Import:
- ✅ Automated at scale
- ✅ Always up-to-date data
- ✅ Consistent quality
- ✅ Easy to maintain

## 📋 Supported Brands (Free Tier)

**Refrigerators:**
- Samsung ✓
- LG ✓
- Bosch ✓
- Siemens ✓
- Whirlpool ✓
- Indesit ✓
- Beko ✓
- Arctic ✓
- Electrolux ✓
- AEG ✓
- Gorenje ✓
- Liebherr ✓
- Candy ✓
- Haier ✓

**Other Categories:**
- 100+ major appliance brands
- Consumer electronics
- IT products
- Home appliances

## 🔄 Migration Strategy

### Step 1: Test with 1 Product
```bash
php test-icecat.php
```

### Step 2: Update 10 Existing Fridges
```bash
php update-fridges-icecat.php
```

### Step 3: Import 50+ New Products
```bash
php artisan import:icecat-fridges --limit=50
```

### Step 4: Expand Categories
```bash
php artisan import:icecat-washers --limit=30
php artisan import:icecat-dishwashers --limit=30
```

## 💡 Best Practices

1. **Cache Icecat responses** (24h) to reduce API calls
2. **Store EAN/MPN** for easy re-sync
3. **Validate data** before saving (check required fields)
4. **Rate limit**: 1 request/second (free tier)
5. **Fallback**: Keep manual import as backup

## 🚀 Next Steps

1. Run `php test-icecat.php` to verify credentials
2. Review output and specification quality
3. Update existing imports to use Icecat
4. Create new category importers
5. Add EAN/MPN tracking to all products

## 📞 Support

- Icecat Documentation: https://icecat.biz/en/menu/support/
- API Docs: https://icecat.biz/en/menu/support/open-icecat/
- Forum: https://icecat.biz/forum/

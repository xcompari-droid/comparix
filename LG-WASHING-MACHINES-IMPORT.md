# LG Washing Machines Import System

Complete Laravel-ready system for importing LG washing machines using Open Icecat API and LG Romania catalog.

## Overview

This system provides:
- **LGWashingRosterFetcher**: Scrapes product URLs from LG Romania website
- **OpenIcecatClient**: Reusable service for fetching product specs and official images from Open Icecat
- **LGWashingIcecatImporter**: Maps washing machine specifications to database
- **Artisan Commands**: Easy-to-use CLI commands for import and export

## Installation

### 1. Install Dependencies

```bash
composer require symfony/dom-crawler symfony/css-selector league/csv --ignore-platform-req=ext-exif
```

### 2. Configure Open Icecat

Add to your `.env` file:

```bash
ICECAT_BASE_URL=https://api.openicecat.org/api/v1
ICECAT_TOKEN=your_token_here
ICECAT_USERNAME=your_username
ICECAT_PASSWORD=your_password
```

**Get your Icecat credentials**:
1. Register at https://icecat.biz/en/register
2. Choose "Free Open Icecat" tier
3. Get API token from your dashboard

### 3. Run Database Migrations

The category and product type will be created automatically on first import, but ensure your database is migrated:

```bash
php artisan migrate
```

## Usage

### Quick Test (5 Products)

```bash
php artisan comparix:import-washing-machines --brand=LG --limit=5
```

### Full Import

Import all available LG washing machines:

```bash
php artisan comparix:import-washing-machines --brand=LG
```

### Import Without Images

Skip image downloading to speed up import:

```bash
php artisan comparix:import-washing-machines --brand=LG --limit=10 --no-media
```

### Export to CSV

Export all washing machines to CSV:

```bash
php artisan comparix:export-washing-machines storage/app/exports/lg_washing_machines.csv
```

Export only LG brand:

```bash
php artisan comparix:export-washing-machines storage/app/exports/lg_wm.csv --brand=LG
```

## Command Options

### Import Command

```bash
php artisan comparix:import-washing-machines [options]
```

| Option | Default | Description |
|--------|---------|-------------|
| `--brand=LG` | LG | Brand to import (currently only LG supported) |
| `--limit=100` | 100 | Maximum number of products to import |
| `--no-media` | false | Skip downloading media/images |

### Export Command

```bash
php artisan comparix:export-washing-machines [output] [options]
```

| Argument | Default | Description |
|----------|---------|-------------|
| `output` | `storage/app/exports/washing_machines.csv` | Output CSV file path |

| Option | Default | Description |
|--------|---------|-------------|
| `--brand=` | all | Filter by brand (optional) |

## Architecture

### Services

#### OpenIcecatClient (`app/Services/OpenIcecatClient.php`)

Reusable service for Open Icecat API integration.

**Methods**:
- `searchByGtin(string $gtin)` - Search product by EAN/GTIN
- `searchByBrandModel(string $brand, string $model)` - Search by brand and model number
- `getProductById(int $productId)` - Get full product details
- `extractSpecifications(array $productData)` - Parse specifications from API response
- `extractImages(array $productData)` - Extract all product images

**Features**:
- Automatic caching (1 hour TTL)
- Error handling and logging
- Multiple image format support
- Flexible specification parsing

#### LGWashingRosterFetcher (`app/Services/Importers/LGWashingRosterFetcher.php`)

Scrapes washing machine product list from LG Romania website.

**Features**:
- Multiple CSS selector patterns for robustness
- Automatic model number extraction
- Product type validation (washing machines only)
- Fallback product list if scraping fails

#### LGWashingIcecatImporter (`app/Services/Importers/LGWashingIcecatImporter.php`)

Main importer orchestrating the entire import process.

**Workflow**:
1. Fetch product list from LG Romania
2. For each product:
   - Search in Icecat by model number
   - Fallback to EAN search if needed
   - Extract specifications and images
   - Create/update product in database
   - Save all specifications
   - Create offers

## Specifications Imported

The system maps 60+ washing machine specifications:

### Capacity & Loading
- Capacity (kg)
- Loading type (front/top)
- Max load

### Performance
- Spin speed (RPM)
- Energy class (A-G)
- Energy consumption (kWh/100 cycles)
- Water consumption (L/cycle)
- Washing performance class
- Spin efficiency class

### Noise Levels
- Washing noise (dB)
- Spinning noise (dB)
- Noise class

### Programs & Features
- Number of programs
- Quick wash
- Eco program
- Steam function
- Allergy care
- Baby care program
- Wool program
- Delicate program
- Sportswear program

### Technology
- Motor type
- Inverter motor
- Direct Drive
- AI technology
- Wi-Fi connectivity
- NFC
- App control

### Safety & Protection
- Child lock
- Overflow protection
- Leak protection (AquaStop)

### Display & Control
- Display type (LED/Digital)
- Time delay
- Time remaining indicator

### Physical Dimensions
- Width, Height, Depth (cm)
- Weight (kg)

### Additional Features
- Add clothes function
- Auto dosing
- Foam control
- Variable spin
- Rinse plus

### Warranty
- Standard warranty (years)
- Motor warranty (years)

## Database Structure

### Tables Used

#### `categories`
- Washing machines category created automatically

#### `product_types`
- "Mașină de spălat" type with proper slug

#### `products`
- Product information with images and descriptions

#### `spec_keys`
- All specification definitions with Romanian names

#### `spec_values`
- Actual specification values (number, string, or boolean)

#### `offers`
- Price and availability information

## Example Output

### Import

```
=== LG WASHING MACHINES IMPORT ===

Fetching washing machines from LG Romania...
✓ Found 10 washing machines

  Processing: LG F2WV5S8S0E
    Searching Icecat by model: F2WV5S8S0E
    ✓ Product created/updated (ID: 245)
    ✓ Saved 42 specifications
    ✓ Offer created
  ✓ Imported successfully

  Processing: LG F4WV708P1E
    Searching Icecat by model: F4WV708P1E
    ✓ Product created/updated (ID: 246)
    ✓ Saved 45 specifications
    ✓ Offer created
  ✓ Imported successfully

...

============================================================
IMPORT COMPLETE
  Total: 10
  Success: 8
  Skipped: 2
  Failed: 0
============================================================
```

### Export

```
Exporting washing machines to CSV...
Found 8 products to export
========================================> 8/8
✓ Export successful!
  File: storage/app/exports/lg_wm.csv
  Products: 8
  Specifications: 42
```

## Troubleshooting

### SSL Certificate Error

If you get `cURL error 77: error setting certificate file`:

**Solution**: The fetcher automatically falls back to a hardcoded list of LG washing machines. Alternatively, configure SSL certificates in your PHP installation.

### No Products Found in Icecat

If products aren't found in Icecat:

1. Check your ICECAT_TOKEN is valid
2. Verify the model numbers are correct
3. Note: Free tier has limited catalog
4. Consider upgrading to premium Icecat for full catalog access

### Import is Slow

The system includes rate limiting (1 second between requests) to be respectful to APIs. This is intentional. For faster imports:

- Use `--no-media` to skip image downloading
- Use `--limit` to import fewer products at once

## Extending to Other Brands

To add support for other brands (Samsung, Bosch, Whirlpool, etc.):

1. Create a new fetcher class (e.g., `SamsungWashingRosterFetcher`)
2. Implement the same `fetch()` method interface
3. Update `LGWashingIcecatImporter` to support multiple brands or create brand-specific importers
4. Add brand option to the Artisan command

The `OpenIcecatClient` is already brand-agnostic and can be reused for any manufacturer.

## Performance

- **Fetching**: ~2-5 seconds for 10 products
- **Icecat API**: ~1-2 seconds per product
- **Database Save**: ~0.1 seconds per product
- **Total**: ~10-15 seconds per product with images

**Estimated Times**:
- 5 products: ~1 minute
- 10 products: ~2 minutes
- 50 products: ~10 minutes
- 100 products: ~20 minutes

## Files Created

```
app/
├── Console/Commands/
│   ├── ImportWashingMachines.php
│   └── ExportWashingMachines.php
├── Services/
│   ├── OpenIcecatClient.php
│   └── Importers/
│       ├── LGWashingRosterFetcher.php
│       └── LGWashingIcecatImporter.php
config/
└── services.php (updated)
.env.example (updated)
```

## Next Steps

1. **Add Your Icecat Token**: Configure `.env` with valid Icecat credentials
2. **Test Import**: Run `php artisan comparix:import-washing-machines --limit=5`
3. **Verify Data**: Check `/masini-de-spalat` category on your website
4. **Full Import**: Run without limit for complete catalog
5. **Export**: Generate CSV reports for analysis
6. **Extend**: Add support for other brands using the same architecture

## Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Enable debug mode: `APP_DEBUG=true` in `.env`
- Review Icecat API documentation: https://icecat.biz/en/menu/partners/index.html

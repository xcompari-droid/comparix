<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ProductType;
use App\Models\Product;
use App\Models\Offer;
use App\Models\SpecKey;
use Illuminate\Database\Seeder;

class SmartphoneSeeder extends Seeder
{
    public function run(): void
    {
        // Disable Scout temporarily
        Product::withoutSyncingToSearch(function () {
            $this->seedData();
        });
    }

    private function seedData(): void
    {
        // Create or get Smartphone category
        $category = Category::firstOrCreate(
            ['slug' => 'smartphone-uri'],
            [
                'name' => 'Smartphone-uri',
                'description' => 'Cele mai noi smartphone-uri la cele mai bune prețuri',
            ]
        );

        // Create or get product type
        $productType = ProductType::firstOrCreate(
            ['slug' => 'smartphone', 'category_id' => $category->id],
            [
                'name' => 'Smartphone',
            ]
        );

        // Create spec keys for smartphones
        $specs = [
            'Display' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => 'display',
            ], [
                'name' => 'Display',
            ]),
            'Display Size' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => 'display-size',
            ], [
                'name' => 'Dimensiune ecran',
            ]),
            'Refresh Rate' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => 'refresh-rate',
            ], [
                'name' => 'Refresh Rate',
            ]),
            'RAM' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => 'ram',
            ], [
                'name' => 'RAM',
            ]),
            'Stocare' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => 'stocare',
            ], [
                'name' => 'Stocare internă',
            ]),
            'Procesor' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => 'procesor',
            ], [
                'name' => 'Procesor',
            ]),
            'Baterie' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => 'baterie',
            ], [
                'name' => 'Capacitate baterie',
            ]),
            'Camera Principala' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => 'camera-principala',
            ], [
                'name' => 'Cameră principală',
            ]),
            'Camera Frontala' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => 'camera-frontala',
            ], [
                'name' => 'Cameră frontală',
            ]),
            'Sistem de Operare' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => 'sistem-operare',
            ], [
                'name' => 'Sistem de operare',
            ]),
            'Greutate' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => 'greutate',
            ], [
                'name' => 'Greutate',
            ]),
            '5G' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => '5g',
            ], [
                'name' => '5G',
            ]),
            'Rezistenta Apa' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => 'rezistenta-apa',
            ], [
                'name' => 'Rezistență la apă',
            ]),
        ];

        // Samsung Galaxy S24 Ultra - Specificații complete de pe Samsung.com
        $s24Ultra = Product::create([
            'product_type_id' => $productType->id,
            'name' => 'Samsung Galaxy S24 Ultra',
            'brand' => 'Samsung',
            'mpn' => 'SM-S928BZKDEUE',
            'short_desc' => 'Flagship Samsung cu S Pen integrat, cameră de 200MP și ecran Dynamic AMOLED 2X de 6.8" cu protecție Corning Gorilla Armor',
            'image_url' => 'https://images.samsung.com/ro/smartphones/galaxy-s24-ultra/images/galaxy-s24-ultra-highlights-color-titanium-black-back-mo.jpg',
            'score' => 9.5,
        ]);

        $s24Ultra->specValues()->createMany([
            ['spec_key_id' => $specs['Display']->id, 'value_string' => 'Dynamic AMOLED 2X'],
            ['spec_key_id' => $specs['Display Size']->id, 'value_string' => '6.8" (172.5mm)'],
            ['spec_key_id' => $specs['Refresh Rate']->id, 'value_string' => '120Hz adaptat (1-120Hz)'],
            ['spec_key_id' => $specs['RAM']->id, 'value_string' => '12GB'],
            ['spec_key_id' => $specs['Stocare']->id, 'value_string' => '512GB'],
            ['spec_key_id' => $specs['Procesor']->id, 'value_string' => 'Snapdragon 8 Gen 3 for Galaxy'],
            ['spec_key_id' => $specs['Baterie']->id, 'value_string' => '5000mAh'],
            ['spec_key_id' => $specs['Camera Principala']->id, 'value_string' => '200MP Wide + 50MP Periscope Telephoto + 10MP Telephoto + 12MP Ultra Wide'],
            ['spec_key_id' => $specs['Camera Frontala']->id, 'value_string' => '12MP'],
            ['spec_key_id' => $specs['Sistem de Operare']->id, 'value_string' => 'Android 14, One UI 6.1'],
            ['spec_key_id' => $specs['Greutate']->id, 'value_string' => '232g'],
            ['spec_key_id' => $specs['5G']->id, 'value_bool' => true],
            ['spec_key_id' => $specs['Rezistenta Apa']->id, 'value_string' => 'IP68'],
        ]);

        Offer::create([
            'product_id' => $s24Ultra->id,
            'merchant' => 'eMAG',
            'price' => 6999.99,
            'currency' => 'RON',
            'url_affiliate' => 'https://www.emag.ro/samsung-galaxy-s24-ultra',
            'in_stock' => true,
        ]);

        Offer::create([
            'product_id' => $s24Ultra->id,
            'merchant' => 'Altex',
            'price' => 7199.99,
            'currency' => 'RON',
            'url_affiliate' => 'https://www.altex.ro/samsung-galaxy-s24-ultra',
            'in_stock' => true,
        ]);

        // Samsung Galaxy S23 FE - Specificații complete
        $s23FE = Product::create([
            'product_type_id' => $productType->id,
            'name' => 'Samsung Galaxy S23 FE',
            'brand' => 'Samsung',
            'mpn' => 'SM-S711BLBDEUE',
            'short_desc' => 'Fan Edition cu ecran Dynamic AMOLED 2X de 6.4" și cameră de 50MP, design premium la preț accesibil',
            'image_url' => 'https://images.samsung.com/ro/smartphones/galaxy-s23-fe/images/galaxy-s23-fe-highlights-colors-graphite-back.jpg',
            'score' => 8.5,
        ]);

        $s23FE->specValues()->createMany([
            ['spec_key_id' => $specs['Display']->id, 'value_string' => 'Dynamic AMOLED 2X'],
            ['spec_key_id' => $specs['Display Size']->id, 'value_string' => '6.4" (162.6mm)'],
            ['spec_key_id' => $specs['Refresh Rate']->id, 'value_string' => '120Hz'],
            ['spec_key_id' => $specs['RAM']->id, 'value_string' => '8GB'],
            ['spec_key_id' => $specs['Stocare']->id, 'value_string' => '256GB'],
            ['spec_key_id' => $specs['Procesor']->id, 'value_string' => 'Exynos 2200 (4nm)'],
            ['spec_key_id' => $specs['Baterie']->id, 'value_string' => '4500mAh'],
            ['spec_key_id' => $specs['Camera Principala']->id, 'value_string' => '50MP Wide + 12MP Ultra Wide + 8MP Telephoto'],
            ['spec_key_id' => $specs['Camera Frontala']->id, 'value_string' => '10MP'],
            ['spec_key_id' => $specs['Sistem de Operare']->id, 'value_string' => 'Android 14, One UI 6'],
            ['spec_key_id' => $specs['Greutate']->id, 'value_string' => '209g'],
            ['spec_key_id' => $specs['5G']->id, 'value_bool' => true],
            ['spec_key_id' => $specs['Rezistenta Apa']->id, 'value_string' => 'IP68'],
        ]);

        Offer::create([
            'product_id' => $s23FE->id,
            'merchant' => 'eMAG',
            'price' => 2799.99,
            'currency' => 'RON',
            'url_affiliate' => 'https://www.emag.ro/samsung-galaxy-s23-fe',
            'in_stock' => true,
        ]);

        Offer::create([
            'product_id' => $s23FE->id,
            'merchant' => 'Altex',
            'price' => 2899.99,
            'currency' => 'RON',
            'url_affiliate' => 'https://www.altex.ro/samsung-galaxy-s23-fe',
            'in_stock' => true,
        ]);

        // Samsung Galaxy A55 5G - Specificații complete
        $a55 = Product::create([
            'product_type_id' => $productType->id,
            'name' => 'Samsung Galaxy A55 5G',
            'brand' => 'Samsung',
            'mpn' => 'SM-A556BLVDEUE',
            'short_desc' => 'Midrange premium cu ecran Super AMOLED de 6.6", design Metal Frame și cameră de 50MP cu stabilizare OIS',
            'image_url' => 'https://images.samsung.com/ro/smartphones/galaxy-a55-5g/images/galaxy-a55-highlights-color-awesome-navy.jpg',
            'score' => 8.0,
        ]);

        $a55->specValues()->createMany([
            ['spec_key_id' => $specs['Display']->id, 'value_string' => 'Super AMOLED'],
            ['spec_key_id' => $specs['Display Size']->id, 'value_string' => '6.6" (167.64mm)'],
            ['spec_key_id' => $specs['Refresh Rate']->id, 'value_string' => '120Hz'],
            ['spec_key_id' => $specs['RAM']->id, 'value_string' => '8GB'],
            ['spec_key_id' => $specs['Stocare']->id, 'value_string' => '256GB (expandabil până la 1TB)'],
            ['spec_key_id' => $specs['Procesor']->id, 'value_string' => 'Exynos 1480 (4nm)'],
            ['spec_key_id' => $specs['Baterie']->id, 'value_string' => '5000mAh'],
            ['spec_key_id' => $specs['Camera Principala']->id, 'value_string' => '50MP OIS Wide + 12MP Ultra Wide + 5MP Macro'],
            ['spec_key_id' => $specs['Camera Frontala']->id, 'value_string' => '32MP'],
            ['spec_key_id' => $specs['Sistem de Operare']->id, 'value_string' => 'Android 14, One UI 6.1'],
            ['spec_key_id' => $specs['Greutate']->id, 'value_string' => '213g'],
            ['spec_key_id' => $specs['5G']->id, 'value_bool' => true],
            ['spec_key_id' => $specs['Rezistenta Apa']->id, 'value_string' => 'IP67'],
        ]);

        Offer::create([
            'product_id' => $a55->id,
            'merchant' => 'eMAG',
            'price' => 1899.99,
            'currency' => 'RON',
            'url_affiliate' => 'https://www.emag.ro/samsung-galaxy-a55',
            'in_stock' => true,
        ]);

        Offer::create([
            'product_id' => $a55->id,
            'merchant' => 'Flanco',
            'price' => 1949.99,
            'currency' => 'RON',
            'url_affiliate' => 'https://www.flanco.ro/samsung-galaxy-a55',
            'in_stock' => true,
        ]);

        // Samsung Galaxy A35 5G - Specificații complete
        $a35 = Product::create([
            'product_type_id' => $productType->id,
            'name' => 'Samsung Galaxy A35 5G',
            'brand' => 'Samsung',
            'mpn' => 'SM-A356BLVDEUE',
            'short_desc' => 'Smartphone accesibil cu ecran Super AMOLED FHD+ de 6.6", design modern și baterie de 5000mAh',
            'image_url' => 'https://images.samsung.com/ro/smartphones/galaxy-a35-5g/images/galaxy-a35-highlights-color-awesome-navy.jpg',
            'score' => 7.5,
        ]);

        $a35->specValues()->createMany([
            ['spec_key_id' => $specs['Display']->id, 'value_string' => 'Super AMOLED FHD+'],
            ['spec_key_id' => $specs['Display Size']->id, 'value_string' => '6.6" (167.64mm)'],
            ['spec_key_id' => $specs['Refresh Rate']->id, 'value_string' => '120Hz'],
            ['spec_key_id' => $specs['RAM']->id, 'value_string' => '8GB'],
            ['spec_key_id' => $specs['Stocare']->id, 'value_string' => '128GB (expandabil până la 1TB)'],
            ['spec_key_id' => $specs['Procesor']->id, 'value_string' => 'Exynos 1380 (5nm)'],
            ['spec_key_id' => $specs['Baterie']->id, 'value_string' => '5000mAh'],
            ['spec_key_id' => $specs['Camera Principala']->id, 'value_string' => '50MP OIS Wide + 8MP Ultra Wide + 5MP Macro'],
            ['spec_key_id' => $specs['Camera Frontala']->id, 'value_string' => '13MP'],
            ['spec_key_id' => $specs['Sistem de Operare']->id, 'value_string' => 'Android 14, One UI 6.1'],
            ['spec_key_id' => $specs['Greutate']->id, 'value_string' => '209g'],
            ['spec_key_id' => $specs['5G']->id, 'value_bool' => true],
            ['spec_key_id' => $specs['Rezistenta Apa']->id, 'value_string' => 'IP67'],
        ]);

        Offer::create([
            'product_id' => $a35->id,
            'merchant' => 'eMAG',
            'price' => 1499.99,
            'currency' => 'RON',
            'url_affiliate' => 'https://www.emag.ro/samsung-galaxy-a35',
            'in_stock' => true,
        ]);

        Offer::create([
            'product_id' => $a35->id,
            'merchant' => 'Altex',
            'price' => 1549.99,
            'currency' => 'RON',
            'url_affiliate' => 'https://www.altex.ro/samsung-galaxy-a35',
            'in_stock' => true,
        ]);

        // Samsung Galaxy Z Fold5 - Specificații complete
        $zFold5 = Product::create([
            'product_type_id' => $productType->id,
            'name' => 'Samsung Galaxy Z Fold5',
            'brand' => 'Samsung',
            'mpn' => 'SM-F946BZKDEUE',
            'short_desc' => 'Flagship pliabil premium cu ecran principal Dynamic AMOLED 2X de 7.6", design Flex Mode și S Pen support',
            'image_url' => 'https://images.samsung.com/ro/smartphones/galaxy-z-fold5/images/galaxy-z-fold5-highlights-color-icy-blue-back-open.jpg',
            'score' => 9.0,
        ]);

        $zFold5->specValues()->createMany([
            ['spec_key_id' => $specs['Display']->id, 'value_string' => 'Dynamic AMOLED 2X Pliabil + AMOLED 2X Extern'],
            ['spec_key_id' => $specs['Display Size']->id, 'value_string' => '7.6" Principal (193.1mm) + 6.2" Cover (157.5mm)'],
            ['spec_key_id' => $specs['Refresh Rate']->id, 'value_string' => '120Hz (ambele ecrane)'],
            ['spec_key_id' => $specs['RAM']->id, 'value_string' => '12GB'],
            ['spec_key_id' => $specs['Stocare']->id, 'value_string' => '512GB'],
            ['spec_key_id' => $specs['Procesor']->id, 'value_string' => 'Snapdragon 8 Gen 2 for Galaxy'],
            ['spec_key_id' => $specs['Baterie']->id, 'value_string' => '4400mAh (dual battery)'],
            ['spec_key_id' => $specs['Camera Principala']->id, 'value_string' => '50MP Wide OIS + 12MP Ultra Wide + 10MP Telephoto'],
            ['spec_key_id' => $specs['Camera Frontala']->id, 'value_string' => '10MP Cover + 4MP Under Display'],
            ['spec_key_id' => $specs['Sistem de Operare']->id, 'value_string' => 'Android 14, One UI 6'],
            ['spec_key_id' => $specs['Greutate']->id, 'value_string' => '253g'],
            ['spec_key_id' => $specs['5G']->id, 'value_bool' => true],
            ['spec_key_id' => $specs['Rezistenta Apa']->id, 'value_string' => 'IPX8'],
        ]);

        Offer::create([
            'product_id' => $zFold5->id,
            'merchant' => 'eMAG',
            'price' => 8999.99,
            'currency' => 'RON',
            'url_affiliate' => 'https://www.emag.ro/samsung-galaxy-z-fold5',
            'in_stock' => true,
        ]);

        Offer::create([
            'product_id' => $zFold5->id,
            'merchant' => 'Samsung Store',
            'price' => 8799.99,
            'currency' => 'RON',
            'url_affiliate' => 'https://www.samsung.com/ro/smartphones/galaxy-z-fold5',
            'in_stock' => true,
        ]);

        echo "✅ Seeder complete! Created category, 5 Samsung smartphones with specs and offers.\n";
    }
}

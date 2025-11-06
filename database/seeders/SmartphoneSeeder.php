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
                'name' => 'Stocare',
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
                'name' => 'Baterie',
            ]),
            'Camera' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => 'camera',
            ], [
                'name' => 'Camera',
            ]),
            '5G' => SpecKey::firstOrCreate([
                'product_type_id' => $productType->id,
                'slug' => '5g',
            ], [
                'name' => '5G',
            ]),
        ];

        // Samsung Galaxy S24 Ultra
        $s24Ultra = Product::create([
            'product_type_id' => $productType->id,
            'name' => 'Samsung Galaxy S24 Ultra',
            'brand' => 'Samsung',
            'mpn' => 'SM-S928BZKDEUE',
            'short_desc' => 'Flagship Samsung cu S Pen, cameră de 200MP și ecran Dynamic AMOLED 2X de 6.8"',
            'image_url' => 'https://images.samsung.com/ro/smartphones/galaxy-s24-ultra/images/galaxy-s24-ultra-highlights-color-titanium-black-back-mo.jpg',
            'score' => 9.5,
        ]);

        $s24Ultra->specValues()->createMany([
            ['spec_key_id' => $specs['Display']->id, 'value_string' => '6.8" Dynamic AMOLED 2X, 120Hz'],
            ['spec_key_id' => $specs['RAM']->id, 'value_string' => '12GB'],
            ['spec_key_id' => $specs['Stocare']->id, 'value_string' => '512GB'],
            ['spec_key_id' => $specs['Procesor']->id, 'value_string' => 'Snapdragon 8 Gen 3'],
            ['spec_key_id' => $specs['Baterie']->id, 'value_string' => '5000mAh'],
            ['spec_key_id' => $specs['Camera']->id, 'value_string' => '200MP + 50MP + 12MP + 10MP'],
            ['spec_key_id' => $specs['5G']->id, 'value_bool' => true],
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

        // Samsung Galaxy S23 FE
        $s23FE = Product::create([
            'product_type_id' => $productType->id,
            'name' => 'Samsung Galaxy S23 FE',
            'brand' => 'Samsung',
            'mpn' => 'SM-S711BLBDEUE',
            'short_desc' => 'Fan Edition cu ecran de 6.4" și cameră de 50MP',
            'image_url' => 'https://images.samsung.com/ro/smartphones/galaxy-s23-fe/images/galaxy-s23-fe-highlights-colors-graphite-back.jpg',
            'score' => 8.5,
        ]);

        $s23FE->specValues()->createMany([
            ['spec_key_id' => $specs['Display']->id, 'value_string' => '6.4" Dynamic AMOLED 2X, 120Hz'],
            ['spec_key_id' => $specs['RAM']->id, 'value_string' => '8GB'],
            ['spec_key_id' => $specs['Stocare']->id, 'value_string' => '256GB'],
            ['spec_key_id' => $specs['Procesor']->id, 'value_string' => 'Exynos 2200'],
            ['spec_key_id' => $specs['Baterie']->id, 'value_string' => '4500mAh'],
            ['spec_key_id' => $specs['Camera']->id, 'value_string' => '50MP + 12MP + 8MP'],
            ['spec_key_id' => $specs['5G']->id, 'value_bool' => true],
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

        // Samsung Galaxy A55
        $a55 = Product::create([
            'product_type_id' => $productType->id,
            'name' => 'Samsung Galaxy A55 5G',
            'brand' => 'Samsung',
            'mpn' => 'SM-A556BLVDEUE',
            'short_desc' => 'Midrange cu ecran Super AMOLED de 6.6" și cameră de 50MP',
            'image_url' => 'https://images.samsung.com/ro/smartphones/galaxy-a55-5g/images/galaxy-a55-highlights-color-awesome-navy.jpg',
            'score' => 8.0,
        ]);

        $a55->specValues()->createMany([
            ['spec_key_id' => $specs['Display']->id, 'value_string' => '6.6" Super AMOLED, 120Hz'],
            ['spec_key_id' => $specs['RAM']->id, 'value_string' => '8GB'],
            ['spec_key_id' => $specs['Stocare']->id, 'value_string' => '256GB'],
            ['spec_key_id' => $specs['Procesor']->id, 'value_string' => 'Exynos 1480'],
            ['spec_key_id' => $specs['Baterie']->id, 'value_string' => '5000mAh'],
            ['spec_key_id' => $specs['Camera']->id, 'value_string' => '50MP + 12MP + 5MP'],
            ['spec_key_id' => $specs['5G']->id, 'value_bool' => true],
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

        // Samsung Galaxy A35
        $a35 = Product::create([
            'product_type_id' => $productType->id,
            'name' => 'Samsung Galaxy A35 5G',
            'brand' => 'Samsung',
            'mpn' => 'SM-A356BLVDEUE',
            'short_desc' => 'Smartphone accesibil cu ecran Super AMOLED de 6.6"',
            'image_url' => 'https://images.samsung.com/ro/smartphones/galaxy-a35-5g/images/galaxy-a35-highlights-color-awesome-navy.jpg',
            'score' => 7.5,
        ]);

        $a35->specValues()->createMany([
            ['spec_key_id' => $specs['Display']->id, 'value_string' => '6.6" Super AMOLED, 120Hz'],
            ['spec_key_id' => $specs['RAM']->id, 'value_string' => '8GB'],
            ['spec_key_id' => $specs['Stocare']->id, 'value_string' => '128GB'],
            ['spec_key_id' => $specs['Procesor']->id, 'value_string' => 'Exynos 1380'],
            ['spec_key_id' => $specs['Baterie']->id, 'value_string' => '5000mAh'],
            ['spec_key_id' => $specs['Camera']->id, 'value_string' => '50MP + 8MP + 5MP'],
            ['spec_key_id' => $specs['5G']->id, 'value_bool' => true],
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

        // Samsung Galaxy Z Fold5
        $zFold5 = Product::create([
            'product_type_id' => $productType->id,
            'name' => 'Samsung Galaxy Z Fold5',
            'brand' => 'Samsung',
            'mpn' => 'SM-F946BZKDEUE',
            'short_desc' => 'Flagship pliabil cu ecran principal de 7.6"',
            'image_url' => 'https://images.samsung.com/ro/smartphones/galaxy-z-fold5/images/galaxy-z-fold5-highlights-color-icy-blue-back-open.jpg',
            'score' => 9.0,
        ]);

        $zFold5->specValues()->createMany([
            ['spec_key_id' => $specs['Display']->id, 'value_string' => '7.6" Dynamic AMOLED 2X pliabil + 6.2" extern'],
            ['spec_key_id' => $specs['RAM']->id, 'value_string' => '12GB'],
            ['spec_key_id' => $specs['Stocare']->id, 'value_string' => '512GB'],
            ['spec_key_id' => $specs['Procesor']->id, 'value_string' => 'Snapdragon 8 Gen 2'],
            ['spec_key_id' => $specs['Baterie']->id, 'value_string' => '4400mAh'],
            ['spec_key_id' => $specs['Camera']->id, 'value_string' => '50MP + 12MP + 10MP'],
            ['spec_key_id' => $specs['5G']->id, 'value_bool' => true],
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

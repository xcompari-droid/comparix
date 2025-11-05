<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ProductType;
use App\Models\Product;
use App\Models\Offer;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Farmă category
        $farmaCategory = Category::create([
            'name' => 'Farmă & Agricultură',
            'slug' => 'farma',
            'icon' => 'heroicon-o-home',
        ]);

        // Create Product Type
        $tractorType = ProductType::create([
            'category_id' => $farmaCategory->id,
            'name' => 'Tractoare',
            'slug' => 'tractoare',
        ]);

        // Create sample products
        $products = [
            [
                'name' => 'Tractor John Deere 6120M',
                'brand' => 'John Deere',
                'mpn' => 'JD-6120M',
                'ean' => '1234567890123',
                'short_desc' => 'Tractor agricol profesional cu putere de 120 CP, ideal pentru lucrări agricole medii și mari.',
                'score' => 85,
            ],
            [
                'name' => 'Tractor New Holland T7.270',
                'brand' => 'New Holland',
                'mpn' => 'NH-T7270',
                'ean' => '1234567890124',
                'short_desc' => 'Tractor de mare putere (270 CP) pentru ferme mari, echipat cu tehnologie modernă.',
                'score' => 90,
            ],
            [
                'name' => 'Tractor Massey Ferguson 5713S',
                'brand' => 'Massey Ferguson',
                'mpn' => 'MF-5713S',
                'ean' => '1234567890125',
                'short_desc' => 'Tractor versatil de 130 CP, perfect pentru diverse aplicații agricole.',
                'score' => 78,
            ],
            [
                'name' => 'Tractor Fendt 724 Vario',
                'brand' => 'Fendt',
                'mpn' => 'FE-724V',
                'ean' => '1234567890126',
                'short_desc' => 'Tractor premium cu transmisie Vario continuă și confort superior.',
                'score' => 92,
            ],
            [
                'name' => 'Tractor Case IH Puma 185',
                'brand' => 'Case IH',
                'mpn' => 'CI-P185',
                'ean' => '1234567890127',
                'short_desc' => 'Tractor robust de 185 CP pentru lucrări intensive.',
                'score' => 82,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create([
                'product_type_id' => $tractorType->id,
                'name' => $productData['name'],
                'brand' => $productData['brand'],
                'mpn' => $productData['mpn'],
                'ean' => $productData['ean'],
                'short_desc' => $productData['short_desc'],
                'score' => $productData['score'],
            ]);

            // Create multiple offers for each product
            $merchants = [
                ['name' => 'AgroShop.ro', 'discount' => 0],
                ['name' => 'FarmaOnline.ro', 'discount' => 5],
                ['name' => 'TractorDirect.ro', 'discount' => 3],
            ];

            $basePrice = rand(50000, 150000);

            foreach ($merchants as $merchant) {
                $price = $basePrice * (1 - $merchant['discount'] / 100);
                
                Offer::create([
                    'product_id' => $product->id,
                    'merchant' => $merchant['name'],
                    'price' => round($price, 2),
                    'currency' => 'RON',
                    'url_affiliate' => 'https://2performant.com/a/' . $product->id . '/' . $merchant['name'],
                    'in_stock' => rand(0, 10) > 1,
                    'last_seen_at' => now(),
                ]);
            }
        }

        $this->command->info('Created ' . count($products) . ' products with offers in Farmă category');
    }
}

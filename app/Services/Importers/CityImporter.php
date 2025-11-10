<?php

namespace App\Services\Importers;

use App\Models\Category;
use App\Models\ProductType;
use App\Models\Product;
use App\Models\SpecKey;
use App\Models\SpecValue;
use App\Models\Offer;
use Illuminate\Support\Str;

class CityImporter
{
    private Category $category;
    private ProductType $productType;
    private array $specKeys = [];
    private array $errors = [];
    private int $imported = 0;
    private int $failed = 0;

    public function importFromCsv(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $this->initializeCategory();
        $this->initializeSpecKeys();

        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle);
        
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            
            try {
                $this->importCity($data);
                $this->imported++;
            } catch (\Exception $e) {
                $this->failed++;
                $this->errors[] = [
                    'city' => $data['name'] ?? 'Unknown',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        fclose($handle);

        return [
            'imported' => $this->imported,
            'failed' => $this->failed,
            'errors' => $this->errors
        ];
    }

    private function initializeCategory(): void
    {
        $this->category = Category::firstOrCreate(
            ['slug' => 'orase'],
            [
                'name' => 'Orașe',
                'description' => 'Comparații între orașe din România - date demografice, economice, turistice și calitate a vieții',
            ]
        );

        $this->productType = ProductType::firstOrCreate(
            ['slug' => 'oras', 'category_id' => $this->category->id],
            ['name' => 'Oraș']
        );
    }

    private function initializeSpecKeys(): void
    {
        $specs = [
            // Geographic
            'region' => ['name' => 'Regiune', 'type' => 'text', 'unit' => null],
            'surface_km2' => ['name' => 'Suprafață', 'type' => 'numeric', 'unit' => 'km²'],
            
            // Demographics
            'population' => ['name' => 'Populație', 'type' => 'numeric', 'unit' => 'locuitori'],
            'population_density' => ['name' => 'Densitate populație', 'type' => 'numeric', 'unit' => 'loc/km²'],
            'male_percentage' => ['name' => 'Bărbați', 'type' => 'numeric', 'unit' => '%'],
            'female_percentage' => ['name' => 'Femei', 'type' => 'numeric', 'unit' => '%'],
            'median_age' => ['name' => 'Vârstă mediană', 'type' => 'numeric', 'unit' => 'ani'],
            'birth_rate_per_1000' => ['name' => 'Natalitate', 'type' => 'numeric', 'unit' => 'la 1000 loc'],
            'death_rate_per_1000' => ['name' => 'Mortalitate', 'type' => 'numeric', 'unit' => 'la 1000 loc'],
            'life_expectancy' => ['name' => 'Speranță de viață', 'type' => 'numeric', 'unit' => 'ani'],
            
            // Economy
            'gdp_per_capita_usd' => ['name' => 'PIB per capita', 'type' => 'numeric', 'unit' => 'USD'],
            'unemployment_rate' => ['name' => 'Rata șomajului', 'type' => 'numeric', 'unit' => '%'],
            
            // Infrastructure & Culture
            'universities_count' => ['name' => 'Universități', 'type' => 'numeric', 'unit' => 'bucăți'],
            'museums_count' => ['name' => 'Muzee', 'type' => 'numeric', 'unit' => 'bucăți'],
            'theaters_count' => ['name' => 'Teatre', 'type' => 'numeric', 'unit' => 'bucăți'],
            'green_spaces_km2' => ['name' => 'Spații verzi', 'type' => 'numeric', 'unit' => 'km²'],
            'public_transport_lines' => ['name' => 'Linii transport public', 'type' => 'numeric', 'unit' => 'linii'],
            
            // Climate
            'average_temperature_c' => ['name' => 'Temperatură medie', 'type' => 'numeric', 'unit' => '°C'],
            'annual_rainfall_mm' => ['name' => 'Precipitații anuale', 'type' => 'numeric', 'unit' => 'mm'],
            
            // Quality of Life (from JSON)
            'air_quality_index' => ['name' => 'Calitate aer (index)', 'type' => 'numeric', 'unit' => 'AQI'],
            'crime_index' => ['name' => 'Index criminalitate', 'type' => 'numeric', 'unit' => 'index'],
            'healthcare_index' => ['name' => 'Index sănătate', 'type' => 'numeric', 'unit' => 'index'],
            'traffic_index' => ['name' => 'Index trafic', 'type' => 'numeric', 'unit' => 'index'],
            'cost_living_index' => ['name' => 'Cost trai', 'type' => 'numeric', 'unit' => 'index'],
            
            // Amenities (from JSON)
            'restaurants' => ['name' => 'Restaurante', 'type' => 'text', 'unit' => null],
            'cafes' => ['name' => 'Cafenele', 'type' => 'text', 'unit' => null],
            'nightlife_venues' => ['name' => 'Cluburi/baruri', 'type' => 'text', 'unit' => null],
            'shopping_centers' => ['name' => 'Centre comerciale', 'type' => 'numeric', 'unit' => 'bucăți'],
            'hospitals' => ['name' => 'Spitale', 'type' => 'numeric', 'unit' => 'bucăți'],
            'sports_facilities' => ['name' => 'Facilități sport', 'type' => 'numeric', 'unit' => 'bucăți'],
            'bike_lanes_km' => ['name' => 'Piste biciclete', 'type' => 'numeric', 'unit' => 'km'],
            'avg_commute_min' => ['name' => 'Timp mediu navetă', 'type' => 'numeric', 'unit' => 'minute'],
            'wifi_spots' => ['name' => 'Puncte WiFi publice', 'type' => 'text', 'unit' => null],
            'coworking_spaces' => ['name' => 'Spații coworking', 'type' => 'numeric', 'unit' => 'bucăți'],
        ];

        foreach ($specs as $slug => $details) {
            $this->specKeys[$slug] = SpecKey::firstOrCreate(
                [
                    'slug' => $slug,
                    'product_type_id' => $this->productType->id
                ],
                [
                    'name' => $details['name'],
                    'type' => $details['type'],
                    'unit' => $details['unit'],
                ]
            );
        }
    }

    private function importCity(array $data): void
    {
        // Use city name as unique identifier
        $product = Product::withoutSyncingToSearch(function() use ($data) {
            return Product::updateOrCreate(
                [
                    'brand' => 'România',
                    'name' => $data['name'],
                ],
                [
                    'category_id' => $this->category->id,
                    'product_type_id' => $this->productType->id,
                    'short_desc' => $data['short_desc'] ?? '',
                    'image_url' => $this->getFirstImageUrl($data['image_urls'] ?? ''),
                ]
            );
        });

        // Create offer with symbolic price (cities don't have actual prices)
        $offer = Offer::updateOrCreate(
            ['product_id' => $product->id],
            [
                'price' => 0, // No actual price for cities
                'currency' => 'RON',
                'url_affiliate' => "https://ro.wikipedia.org/wiki/{$data['name']}",
                'merchant' => 'Wikipedia',
                'in_stock' => true,
                'last_seen_at' => now(),
            ]
        );

        // Add specifications
        $this->addSpecifications($product, $data);
    }

    private function addSpecifications(Product $product, array $data): void
    {
        // Parse specs_raw_json if available
        $jsonSpecs = [];
        if (!empty($data['specs_raw_json'])) {
            $jsonSpecs = json_decode($data['specs_raw_json'], true) ?? [];
        }

        // Geographic specs
        $this->addSpec($product, 'region', $data['region'] ?? null);
        $this->addSpec($product, 'surface_km2', $data['surface_km2'] ?? null);
        
        // Demographics
        $this->addSpec($product, 'population', $data['population'] ?? null);
        $this->addSpec($product, 'population_density', $data['population_density'] ?? null);
        $this->addSpec($product, 'male_percentage', $data['male_percentage'] ?? null);
        $this->addSpec($product, 'female_percentage', $data['female_percentage'] ?? null);
        $this->addSpec($product, 'median_age', $data['median_age'] ?? null);
        $this->addSpec($product, 'birth_rate_per_1000', $data['birth_rate_per_1000'] ?? null);
        $this->addSpec($product, 'death_rate_per_1000', $data['death_rate_per_1000'] ?? null);
        $this->addSpec($product, 'life_expectancy', $data['life_expectancy'] ?? null);
        
        // Economy
        $this->addSpec($product, 'gdp_per_capita_usd', $data['gdp_per_capita_usd'] ?? null);
        $this->addSpec($product, 'unemployment_rate', $data['unemployment_rate'] ?? null);
        
        // Infrastructure & Culture
        $this->addSpec($product, 'universities_count', $data['universities_count'] ?? null);
        $this->addSpec($product, 'museums_count', $data['museums_count'] ?? null);
        $this->addSpec($product, 'theaters_count', $data['theaters_count'] ?? null);
        $this->addSpec($product, 'green_spaces_km2', $data['green_spaces_km2'] ?? null);
        $this->addSpec($product, 'public_transport_lines', $data['public_transport_lines'] ?? null);
        
        // Climate
        $this->addSpec($product, 'average_temperature_c', $data['average_temperature_c'] ?? null);
        $this->addSpec($product, 'annual_rainfall_mm', $data['annual_rainfall_mm'] ?? null);
        
        // Quality of Life from JSON
        $this->addSpec($product, 'air_quality_index', $jsonSpecs['air_quality_index'] ?? null);
        $this->addSpec($product, 'crime_index', $jsonSpecs['crime_index'] ?? null);
        $this->addSpec($product, 'healthcare_index', $jsonSpecs['healthcare_index'] ?? null);
        $this->addSpec($product, 'traffic_index', $jsonSpecs['traffic_index'] ?? null);
        $this->addSpec($product, 'cost_living_index', $jsonSpecs['cost_living_index'] ?? null);
        
        // Amenities from JSON
        $this->addSpec($product, 'restaurants', $jsonSpecs['restaurants'] ?? null);
        $this->addSpec($product, 'cafes', $jsonSpecs['cafes'] ?? null);
        $this->addSpec($product, 'nightlife_venues', $jsonSpecs['nightlife_venues'] ?? null);
        $this->addSpec($product, 'shopping_centers', $jsonSpecs['shopping_centers'] ?? null);
        $this->addSpec($product, 'hospitals', $jsonSpecs['hospitals'] ?? null);
        $this->addSpec($product, 'sports_facilities', $jsonSpecs['sports_facilities'] ?? null);
        $this->addSpec($product, 'bike_lanes_km', $jsonSpecs['bike_lanes_km'] ?? null);
        $this->addSpec($product, 'avg_commute_min', $jsonSpecs['avg_commute_min'] ?? null);
        $this->addSpec($product, 'wifi_spots', $jsonSpecs['wifi_spots'] ?? null);
        $this->addSpec($product, 'coworking_spaces', $jsonSpecs['coworking_spaces'] ?? null);

        // Add tourist attractions as description
        if (!empty($data['tourist_attractions'])) {
            $attractions = explode('|', $data['tourist_attractions']);
            $product->long_desc = "## Obiective turistice\n\n" . implode("\n", array_map(fn($a) => "- {$a}", $attractions));
            $product->saveQuietly();
        }
    }

    private function addSpec(Product $product, string $specSlug, $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!isset($this->specKeys[$specSlug])) {
            return;
        }

        SpecValue::updateOrCreate(
            [
                'product_id' => $product->id,
                'spec_key_id' => $this->specKeys[$specSlug]->id,
            ],
            [
                'value_string' => (string) $value,
                'value_number' => is_numeric($value) ? (float) $value : null,
            ]
        );
    }

    private function getFirstImageUrl(string $imageUrls): string
    {
        $urls = explode('|', $imageUrls);
        return trim($urls[0] ?? '');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\SpecKey;
use App\Models\SpecValue;
use Illuminate\Support\Str;

class ImportVersusSmartphones extends Command
{
    protected $signature = 'import:versus-smartphones {--limit=108 : Number of smartphones to re-import}';
    protected $description = 'Re-import all smartphones from Versus.com with complete specifications';

    private $specKeyCache = [];

    public function handle()
    {
        $limit = $this->option('limit');
        
        $this->info("🔄 Re-importing {$limit} smartphones from Versus.com...\n");
        
        // Obține toate smartphone-urile existente
        $smartphones = Product::where('product_type_id', 1)
            ->limit($limit)
            ->get();
        
        $this->info("📱 Found {$smartphones->count()} smartphones to update\n");
        
        $bar = $this->output->createProgressBar($smartphones->count());
        $bar->start();
        
        $updated = 0;
        $failed = 0;
        
        foreach ($smartphones as $phone) {
            try {
                // Construiește slug-ul Versus din numele produsului
                $versusSlug = $this->buildVersusSlug($phone->name);
                $versusUrl = "https://versus.com/en/{$versusSlug}";
                
                // Încearcă să obțină specs de pe Versus
                $specs = $this->scrapeVersusSpecs($versusUrl);
                
                if ($specs && count($specs) > 5) {
                    // Șterge specs vechi
                    $phone->specValues()->delete();
                    
                    // Adaugă specs noi
                    foreach ($specs as $specName => $specValue) {
                        $this->addSpecification($phone, $specName, $specValue);
                    }
                    
                    // Actualizează source_url
                    $phone->source_url = $versusUrl;
                    $phone->save();
                    
                    $updated++;
                } else {
                    $failed++;
                    $this->newLine();
                    $this->warn("⚠️  {$phone->name}: No specs found at {$versusUrl}");
                }
                
                $bar->advance();
                
                // Delay pentru a nu suprasolicita serverul
                usleep(500000); // 0.5 secunde
                
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("✗ {$phone->name}: {$e->getMessage()}");
                $bar->advance();
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("✓ Import completed!");
        $this->info("  - Updated: {$updated}");
        $this->info("  - Failed: {$failed}");
        
        return 0;
    }
    
    private function buildVersusSlug(string $name): string
    {
        // Convertește "Samsung Galaxy S24 Ultra" în "samsung-galaxy-s24-ultra"
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return $slug;
    }
    
    private function scrapeVersusSpecs(string $url): ?array
    {
        // Folosește Node.js scraper
        $scraperPath = base_path('scraper.cjs');
        
        if (!file_exists($scraperPath)) {
            throw new \Exception("Scraper not found at {$scraperPath}");
        }
        
        // Rulează scraper-ul
        $command = "node " . escapeshellarg($scraperPath) . " " . escapeshellarg($url) . " 2>&1";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            return null;
        }
        
        // Parseează HTML-ul returnat
        $html = implode("\n", $output);
        
        return $this->parseVersusHtml($html);
    }
    
    private function parseVersusHtml(string $html): array
    {
        $specs = [];
        
        // Pattern pentru extractarea specificațiilor din HTML
        // Caută pentru span-uri cu clasa Property__label___zWFei (smartphone structure)
        preg_match_all('/<span class="Property__label___zWFei">([^<]+)<\/span>.*?<div class="Value__value___RhzFG">(.*?)<\/div>/s', 
            $html, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $specName = trim(strip_tags($match[1]));
            $specValueRaw = $match[2];
            
            // Extrage valorile diferit în funcție de tip
            // Pentru Boolean (Yes/No)
            if (preg_match('/Boolean__boolean_yes___SBedx/', $specValueRaw)) {
                $specValue = 'Yes';
            } elseif (preg_match('/Boolean__boolean_no___NI4kH/', $specValueRaw)) {
                $specValue = 'No';
            } 
            // Pentru Number
            elseif (preg_match('/<p class="Number__number___G9V3S">([^<]+)<\/p>/', $specValueRaw, $numMatch)) {
                $specValue = trim(strip_tags($numMatch[1]));
            }
            // Pentru String
            elseif (preg_match('/<div class="String__string___sxJBL">(.*?)<\/div>/s', $specValueRaw, $strMatch)) {
                $specValue = trim(strip_tags($strMatch[1]));
            }
            // Pentru FuzzyTime (dates)
            elseif (preg_match('/<div class="FuzzyTime__string___ven0k">(.*?)<\/div>/s', $specValueRaw, $dateMatch)) {
                $specValue = trim(strip_tags($dateMatch[1]));
            }
            // Fallback - strip all tags
            else {
                $specValue = trim(strip_tags($specValueRaw));
            }
            
            // Curăță valorile
            $specValue = html_entity_decode($specValue);
            $specValue = preg_replace('/\s+/', ' ', $specValue);
            $specValue = preg_replace('/Galaxy S\d+.*$/i', '', $specValue); // Remove phone name from value
            $specValue = trim($specValue);
            
            if ($specName && $specValue && !preg_match('/Unknown|Help us/i', $specValue)) {
                $specs[$specName] = $specValue;
            }
        }
        
        return $specs;
    }
    
    private function addSpecification(Product $product, string $specName, string $specValue): void
    {
        // Obține sau creează spec key
        $specKey = $this->getOrCreateSpecKey($specName);
        
        // Determină tipul valorii
        if (is_numeric($specValue)) {
            SpecValue::create([
                'product_id' => $product->id,
                'spec_key_id' => $specKey->id,
                'value_number' => (float)$specValue,
            ]);
        } elseif (in_array(strtolower($specValue), ['yes', 'no', 'da', 'nu'])) {
            SpecValue::create([
                'product_id' => $product->id,
                'spec_key_id' => $specKey->id,
                'value_bool' => in_array(strtolower($specValue), ['yes', 'da']),
            ]);
        } else {
            SpecValue::create([
                'product_id' => $product->id,
                'spec_key_id' => $specKey->id,
                'value_string' => $specValue,
            ]);
        }
    }
    
    private function getOrCreateSpecKey(string $name): SpecKey
    {
        $slug = Str::slug($name);
        $cacheKey = "1_{$slug}"; // Include product_type_id in cache key
        
        if (isset($this->specKeyCache[$cacheKey])) {
            return $this->specKeyCache[$cacheKey];
        }
        
        // Caută mai întâi după slug pentru product_type_id = 1 (smartphone)
        $specKey = SpecKey::where('slug', 'LIKE', "%{$slug}%")
            ->where('product_type_id', 1)
            ->first();
            
        if (!$specKey) {
            // Dacă nu există, creează unul nou cu un slug unic
            $baseSlug = $slug;
            $counter = 1;
            
            while (SpecKey::where('slug', $slug)->where('product_type_id', 1)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }
            
            $specKey = SpecKey::create([
                'name' => $name,
                'slug' => $slug,
                'product_type_id' => 1, // Smartphone type
            ]);
        }
        
        $this->specKeyCache[$name] = $specKey;
        
        return $specKey;
    }
}

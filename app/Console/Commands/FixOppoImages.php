<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class FixOppoImages extends Command
{
    protected $signature = 'comparix:fix-oppo-images';
    protected $description = 'Re-download and store OPPO images locally.';

    public function handle()
    {
        $products = Product::where('brand', 'OPPO')->get();

        foreach ($products as $product) {

            $image = $product->image_url ?? null;
            if (!$image) {
                $this->warn("No URL for {$product->name}");
                continue;
            }

            $this->info("Processing {$product->name} → {$image}");

            try {
                // remove old empty media
                $product->clearMediaCollection('gallery');

                // force https
                $image = preg_replace('#^http:#', 'https:', $image);

                // download & store
                $product
                    ->addMediaFromUrl($image)
                    ->toMediaCollection('gallery');

                $this->info("✅ Stored successfully");
            } catch (\Exception $e) {
                $this->error("❌ Failed: {$e->getMessage()}");
            }
        }
        return Command::SUCCESS;
    }
}

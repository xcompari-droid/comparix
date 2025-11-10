<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "🔄 Scanning for manually downloaded images...\n\n";

$imageDir = __DIR__ . '/public/images/products';
$products = Product::all();
$updated = 0;

// Scan directory for images
$files = glob($imageDir . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE);

foreach ($products as $product) {
    // Generate expected filename patterns
    $baseName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($product->name));
    $patterns = [
        $baseName . '.jpg',
        $baseName . '.jpeg',
        $baseName . '.png',
        $baseName . '.webp',
        str_replace(' ', '-', strtolower($product->name)) . '.jpg',
        str_replace(' ', '-', strtolower($product->name)) . '.png',
    ];
    
    foreach ($files as $file) {
        $fileName = basename($file);
        
        if (in_array($fileName, $patterns)) {
            $publicUrl = '/images/products/' . $fileName;
            $fileSize = round(filesize($file) / 1024, 2);
            
            // Update product if image is different or better
            if ($product->image_url !== $publicUrl) {
                Product::withoutSyncingToSearch(function() use ($product, $publicUrl) {
                    $product->update(['image_url' => $publicUrl]);
                });
                
                echo "✓ {$product->name}\n";
                echo "  → {$publicUrl} ({$fileSize} KB)\n\n";
                $updated++;
            }
            break;
        }
    }
}

if ($updated === 0) {
    echo "ℹ️  No new images found.\n\n";
    echo "📝 To add images manually:\n";
    echo "   1. Download product images from official websites\n";
    echo "   2. Save them in: public/images/products/\n";
    echo "   3. Use naming format: brand-model-name.jpg\n";
    echo "   4. Run this script again\n\n";
    echo "Example filenames:\n";
    foreach ($products->take(3) as $product) {
        $fileName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($product->name)) . '.jpg';
        echo "   - {$fileName}\n";
    }
} else {
    echo "\n✅ Updated {$updated} product(s) with manually downloaded images!\n";
}

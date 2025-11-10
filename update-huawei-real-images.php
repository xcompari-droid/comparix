<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "🖼️  Updating Huawei products with real images...\n\n";

// Real Huawei product images from official sources
$huaweiImages = [
    'Huawei Pura 70 Ultra' => 'https://consumer-img.huawei.com/content/dam/huawei-cbg-site/common/mkt/pdp/phones/pura-70-ultra/list-img/pura-70-ultra-green.png',
    'Huawei Pura 70 Pro' => 'https://consumer-img.huawei.com/content/dam/huawei-cbg-site/common/mkt/pdp/phones/pura-70-pro/list-img/pura-70-pro-white.png',
    'Huawei Pura 70' => 'https://consumer-img.huawei.com/content/dam/huawei-cbg-site/common/mkt/pdp/phones/pura-70/list-img/pura-70-pink.png',
    'Huawei Mate 60 Pro' => 'https://consumer-img.huawei.com/content/dam/huawei-cbg-site/common/mkt/pdp/phones/mate60-pro/list-img/mate60-pro-black.png',
    'Huawei Mate X5' => 'https://consumer-img.huawei.com/content/dam/huawei-cbg-site/common/mkt/pdp/phones/mate-x5/list-img/mate-x5-gold.png',
    'Huawei Nova 12 SE' => 'https://consumer-img.huawei.com/content/dam/huawei-cbg-site/common/mkt/pdp/phones/nova-12-se/list-img/nova-12-se-green.png',
    'Huawei Nova 12i' => 'https://consumer-img.huawei.com/content/dam/huawei-cbg-site/common/mkt/pdp/phones/nova-12i/list-img/nova-12i-black.png',
    'Huawei P60 Pro' => 'https://consumer-img.huawei.com/content/dam/huawei-cbg-site/common/mkt/pdp/phones/p60-pro/list-img/p60-pro-black.png',
];

$huaweiProducts = Product::where('brand', 'Huawei')->get();

foreach ($huaweiProducts as $product) {
    echo "Processing: {$product->name}\n";
    
    if (isset($huaweiImages[$product->name])) {
        $imageUrl = $huaweiImages[$product->name];
        
        // Test if image is accessible
        $headers = @get_headers($imageUrl);
        if ($headers && strpos($headers[0], '200')) {
            Product::withoutSyncingToSearch(function() use ($product, $imageUrl) {
                $product->update(['image_url' => $imageUrl]);
            });
            echo "  ✓ Updated with real image\n";
        } else {
            // Fallback to higher quality placeholder
            $shortName = str_replace('Huawei ', '', $product->name);
            $imageUrl = "https://dummyimage.com/600x600/d60000/ffffff.png&text=" . urlencode($shortName);
            Product::withoutSyncingToSearch(function() use ($product, $imageUrl) {
                $product->update(['image_url' => $imageUrl]);
            });
            echo "  ⚠ Image not accessible, using placeholder\n";
        }
    } else {
        $shortName = str_replace('Huawei ', '', $product->name);
        $imageUrl = "https://dummyimage.com/600x600/d60000/ffffff.png&text=" . urlencode($shortName);
        Product::withoutSyncingToSearch(function() use ($product, $imageUrl) {
            $product->update(['image_url' => $imageUrl]);
        });
        echo "  → Using placeholder\n";
    }
    
    echo "\n";
}

echo "✅ Done!\n";

<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

Product::withoutSyncingToSearch(function () {
    $gpus = Product::where('product_type_id', 4)->get(); // 4 = Placă video

    foreach ($gpus as $gpu) {
        if ($gpu->mpn) {
            $gpu->update(['source_url' => 'https://versus.com/en/' . $gpu->mpn]);
        }
    }

    echo "Updated {$gpus->count()} GPUs with source URLs\n";
});

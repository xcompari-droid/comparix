<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Category;

$totalProducts = Product::count();
echo "Total produse în baza de date: $totalProducts\n\n";

$categories = Category::all();
foreach ($categories as $cat) {
    $count = Product::whereHas('productType', function($q) use ($cat) {
        $q->where('category_id', $cat->id);
    })->count();
    
    echo "$cat->name: $count produse\n";
}

<?php
require __DIR__.'/vendor/autoload.php';
use App\Models\Product;

$laptops = Product::where('product_type_id', 9)->get();
$noImage = $laptops->filter(function($p){
    return !$p->image_url || strpos($p->image_url, 'http') === 0;
});
echo "Laptopuri fără imagine locală: ".$noImage->count()."\n";
foreach($noImage as $p){
    echo $p->id." - ".$p->name."\n";
}

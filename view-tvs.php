<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Category;

$category = Category::where('slug', 'televizoare')->first();
$tvs = Product::where('product_type_id', 8)
    ->with('specValues.specKey')
    ->orderBy('name')
    ->paginate(20);

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Televizoare - Comparix</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">📺 Televizoare</h1>
            <p class="text-gray-600">Total: <?= $tvs->total() ?> produse</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($tvs as $tv): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow">
                    <!-- Imagine TV -->
                    <div class="aspect-video bg-gray-100 flex items-center justify-center">
                        <img 
                            src="<?= $tv->image_url ?>" 
                            alt="<?= htmlspecialchars($tv->name) ?>"
                            class="w-full h-full object-cover"
                            onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($tv->brand) ?>&size=400&background=1f2937&color=fff'"
                        >
                    </div>

                    <!-- Detalii TV -->
                    <div class="p-4">
                        <div class="text-xs text-blue-600 font-semibold mb-1">
                            <?= htmlspecialchars($tv->brand) ?>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2 line-clamp-2">
                            <?= htmlspecialchars($tv->name) ?>
                        </h3>

                        <!-- Specificații cheie -->
                        <div class="space-y-1 text-sm text-gray-600">
                            <?php
                            $screenSize = $tv->specValues->firstWhere('specKey.name', 'screen size');
                            $resolution = $tv->specValues->firstWhere('specKey.name', 'resolution');
                            $displayTech = $tv->specValues->firstWhere('specKey.name', 'display technology');
                            $refreshRate = $tv->specValues->firstWhere('specKey.name', 'refresh rate');
                            ?>
                            
                            <?php if ($screenSize): ?>
                                <div class="flex items-center">
                                    <span class="text-gray-400 mr-2">📏</span>
                                    <span><?= $screenSize->value_string ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($resolution): ?>
                                <div class="flex items-center">
                                    <span class="text-gray-400 mr-2">🎬</span>
                                    <span><?= $resolution->value_string ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($displayTech): ?>
                                <div class="flex items-center">
                                    <span class="text-gray-400 mr-2">✨</span>
                                    <span><?= $displayTech->value_string ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($refreshRate): ?>
                                <div class="flex items-center">
                                    <span class="text-gray-400 mr-2">⚡</span>
                                    <span><?= $refreshRate->value_string ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Status imagine -->
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <?php if (strpos($tv->image_url, 'ui-avatars.com') !== false): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">
                                    🖼️ Placeholder
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800">
                                    ✅ Imagine reală
                                </span>
                            <?php endif; ?>
                            
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-100 text-blue-800 ml-1">
                                <?= $tv->specValues->count() ?> specs
                            </span>
                        </div>

                        <?php if ($tv->source_url): ?>
                            <a href="<?= $tv->source_url ?>" 
                               target="_blank"
                               class="mt-3 block w-full text-center bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors text-sm">
                                Vezi pe Versus →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginare -->
        <?php if ($tvs->hasPages()): ?>
            <div class="mt-8 flex justify-center space-x-2">
                <?php if ($tvs->currentPage() > 1): ?>
                    <a href="?page=<?= $tvs->currentPage() - 1 ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50">
                        ← Anterior
                    </a>
                <?php endif; ?>

                <span class="px-4 py-2 bg-blue-600 text-white rounded">
                    Pagina <?= $tvs->currentPage() ?> din <?= $tvs->lastPage() ?>
                </span>

                <?php if ($tvs->hasMorePages()): ?>
                    <a href="?page=<?= $tvs->currentPage() + 1 ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50">
                        Următoarea →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Statistici -->
        <div class="mt-8 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">📊 Statistici</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-3xl font-bold text-blue-600"><?= $tvs->total() ?></div>
                    <div class="text-sm text-gray-600">Total TV-uri</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-green-600">
                        <?= Product::where('product_type_id', 8)
                            ->where('image_url', 'NOT LIKE', '%ui-avatars%')
                            ->count() ?>
                    </div>
                    <div class="text-sm text-gray-600">Cu imagini reale</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-yellow-600">
                        <?= Product::where('product_type_id', 8)
                            ->where('image_url', 'LIKE', '%ui-avatars%')
                            ->count() ?>
                    </div>
                    <div class="text-sm text-gray-600">Cu placeholder</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-purple-600">17</div>
                    <div class="text-sm text-gray-600">Specs medie/TV</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

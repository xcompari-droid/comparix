// Route for mobile laptop compare page
use Illuminate\Support\Facades\Route;

Route::get('/m/compare/laptops', function () {
    // TODO: Replace with real DB query and mapping
    $items = [
        [
            'id' => 1,
            'name' => 'Laptop 1',
            'slug' => 'laptop-1',
            'image' => '/storage/laptops/laptop1.jpg',
            'price' => 3500,
            'specs' => [
                'cpu_model' => 'Intel i5',
                'gpu_model' => 'NVIDIA GTX 1650',
                'ram_gb' => 16,
                'storage_gb' => 512,
                'display_size_in' => 15.6,
                'display_brightness_nits' => 300,
                'battery_wh' => 50,
                'weight_kg' => 1.8,
            ],
            'metrics' => [
                'cpu_score' => 8500,
                'gpu_score' => 6000,
                'display_nits' => 300,
                'battery_wh' => 50,
                'weight_kg' => 1.8,
                'price_ron' => 3500,
            ],
        ],
        [
            'id' => 2,
            'name' => 'Laptop 2',
            'slug' => 'laptop-2',
            'image' => '/storage/laptops/laptop2.jpg',
            'price' => 4200,
            'specs' => [
                'cpu_model' => 'AMD Ryzen 7',
                'gpu_model' => 'NVIDIA RTX 3050',
                'ram_gb' => 16,
                'storage_gb' => 1024,
                'display_size_in' => 16,
                'display_brightness_nits' => 400,
                'battery_wh' => 60,
                'weight_kg' => 2.1,
            ],
            'metrics' => [
                'cpu_score' => 11000,
                'gpu_score' => 9000,
                'display_nits' => 400,
                'battery_wh' => 60,
                'weight_kg' => 2.1,
                'price_ron' => 4200,
            ],
        ],
    ];
    return Inertia::render('Compare/LaptopsMobile', [
        'items' => $items,
        'title' => 'Comparație laptopuri (mobil)'
    ]);
});

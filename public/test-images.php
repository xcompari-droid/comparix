<!DOCTYPE html>
<html>
<head>
    <title>Test Images</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .product { margin: 20px; padding: 20px; border: 1px solid #ccc; }
        img { max-width: 400px; border: 2px solid #0891b2; }
    </style>
</head>
<body>
    <h1>Test Product Images</h1>
    
    <div class="product">
        <h2>Test 1: Direct URL</h2>
        <img src="https://placehold.co/400x400/1428A0/ffffff/png?text=Samsung+S24" alt="Test 1">
        <p>URL: https://placehold.co/400x400/1428A0/ffffff/png?text=Samsung+S24</p>
    </div>

    <div class="product">
        <h2>Test 2: OPPO Color</h2>
        <img src="https://placehold.co/400x400/0891b2/ffffff/png?text=OPPO+Reno" alt="Test 2">
        <p>URL: https://placehold.co/400x400/0891b2/ffffff/png?text=OPPO+Reno</p>
    </div>

    <div class="product">
        <h2>Test 3: With + encoding</h2>
        <img src="https://placehold.co/400x400/1428A0/ffffff/png?text=S24+Ultra" alt="Test 3">
        <p>URL: https://placehold.co/400x400/1428A0/ffffff/png?text=S24+Ultra</p>
    </div>

    <?php
    // Get actual products from database
    $db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
    $stmt = $db->query('SELECT id, name, brand, image_url FROM products LIMIT 5');
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<div class="product">';
        echo '<h2>' . htmlspecialchars($row['name']) . '</h2>';
        echo '<p>Brand: ' . htmlspecialchars($row['brand']) . '</p>';
        echo '<img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['name']) . '">';
        echo '<p>URL: ' . htmlspecialchars($row['image_url']) . '</p>';
        echo '</div>';
    }
    ?>

</body>
</html>

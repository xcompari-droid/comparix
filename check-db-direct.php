<?php

$db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$stmt = $db->query('SELECT id, name, brand, image_url FROM products LIMIT 5');

echo "Products in database:" . PHP_EOL . PHP_EOL;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']}" . PHP_EOL;
    echo "Name: {$row['name']}" . PHP_EOL;
    echo "Brand: {$row['brand']}" . PHP_EOL;
    echo "Image URL: " . ($row['image_url'] ?? 'NULL') . PHP_EOL;
    echo str_repeat('-', 60) . PHP_EOL;
}

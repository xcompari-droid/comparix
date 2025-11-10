<?php

$db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');

echo "📊 Products by brand:" . PHP_EOL . PHP_EOL;

$stmt = $db->query('SELECT brand, COUNT(*) as count FROM products GROUP BY brand ORDER BY count DESC');

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['brand']}: {$row['count']} products" . PHP_EOL;
}

$total = $db->query('SELECT COUNT(*) FROM products')->fetchColumn();
echo PHP_EOL . "📱 Total products: {$total}" . PHP_EOL;

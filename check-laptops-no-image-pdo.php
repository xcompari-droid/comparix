<?php
// Script rapid PDO pentru a lista laptopurile fără imagine locală
$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_DATABASE') ?: 'comparix';
$user = getenv('DB_USERNAME') ?: 'comparix';
$pass = getenv('DB_PASSWORD') ?: 'BMSSDtZzx3gXfBxb7d9R';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    die('Conexiune eșuată: ' . $e->getMessage());
}
$sql = "SELECT id, name FROM products WHERE product_type_id = 9 AND (image_url IS NULL OR image_url = '' OR image_url LIKE 'http%')";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll();
echo "Laptopuri fără imagine locală: ".count($rows)."\n";
foreach($rows as $row) {
    echo $row['id']." - ".$row['name']."\n";
}

<?php

$pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$stmt = $pdo->query('SELECT * FROM spec_values ORDER BY id DESC LIMIT 10');

echo "📊 Last 10 spec_values in database:\n\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']}, Product: {$row['product_id']}, SpecKey: {$row['spec_key_id']}\n";
    echo "  value_text: '{$row['value_text']}'\n";
    echo "  value_numeric: '{$row['value_numeric']}'\n\n";
}

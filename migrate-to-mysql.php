<?php

// Script pentru migrare date din SQLite în MySQL
require 'vendor/autoload.php';

$sqliteDb = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$mysqlDb = new PDO(
    'mysql:host=127.0.0.1;dbname=comparix',
    'comparix',
    'BMSSDtZzx3gXfBxb7d9R',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "🔄 Starting SQLite to MySQL migration...\n\n";

// Tabele de migrat
$tables = [
    'users',
    'categories',
    'product_types',
    'spec_keys',
    'products',
    'spec_values',
    'comparisons',
    'offers',
    'affiliate_clicks'
];

foreach ($tables as $table) {
    echo "📦 Migrating table: $table\n";
    
    // Get all data from SQLite
    $stmt = $sqliteDb->query("SELECT * FROM $table");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rows)) {
        echo "   ⚠️  No data in $table\n";
        continue;
    }
    
    echo "   Found " . count($rows) . " rows\n";
    
    // Clear MySQL table
    $mysqlDb->exec("TRUNCATE TABLE $table");
    
    // Prepare insert statement
    $columns = array_keys($rows[0]);
    $placeholders = array_fill(0, count($columns), '?');
    
    $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $insertStmt = $mysqlDb->prepare($sql);
    
    // Insert each row
    $mysqlDb->beginTransaction();
    $count = 0;
    
    foreach ($rows as $row) {
        try {
            $insertStmt->execute(array_values($row));
            $count++;
            
            if ($count % 100 === 0) {
                echo "   Inserted $count rows...\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Error inserting row: " . $e->getMessage() . "\n";
        }
    }
    
    $mysqlDb->commit();
    echo "   ✅ Inserted $count rows into $table\n\n";
}

echo "✅ Migration complete!\n";

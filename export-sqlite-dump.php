<?php

// Export SQLite data to MySQL compatible SQL
$sqlite = new PDO('sqlite:database/database.sqlite');
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$output = "";

// Tables to export
$tables = ['categories', 'product_types', 'spec_keys', 'products', 'spec_values', 'comparisons', 'offers'];

foreach ($tables as $table) {
    echo "Exporting $table...\n";
    
    // Get data
    $stmt = $sqlite->query("SELECT * FROM $table");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rows)) {
        continue;
    }
    
    $output .= "\n-- Table: $table\n";
    $output .= "TRUNCATE TABLE `$table`;\n";
    
    foreach ($rows as $row) {
        $columns = array_keys($row);
        $values = array_map(function($v) {
            if ($v === null) return 'NULL';
            return "'" . addslashes($v) . "'";
        }, array_values($row));
        
        $output .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
    }
}

file_put_contents('mysql-import.sql', $output);
echo "\nExported to mysql-import.sql\n";
echo "Total size: " . number_format(strlen($output)) . " bytes\n";

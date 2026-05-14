<?php

try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=DevMart", "feminas", "admin");
    
    echo "Estructura de tabla 'vendedores':\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM vendedores");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ") " . ($col['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . " " . ($col['Key'] ? "KEY: " . $col['Key'] : "") . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

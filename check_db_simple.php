<?php

try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=DevMart", "feminas", "admin");
    
    // Get table columns
    $stmt = $pdo->query("SHOW COLUMNS FROM vendedores");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columnas en tabla 'vendedores':\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    echo "\n\nColumnas en tabla 'productos':\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM productos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

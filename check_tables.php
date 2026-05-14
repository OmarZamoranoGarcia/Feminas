<?php

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

try {
    // Get table columns
    $columns = DB::select("SHOW COLUMNS FROM vendedores");
    echo "Columnas en tabla 'vendedores':\n";
    foreach ($columns as $column) {
        echo "- " . $column->Field . " (" . $column->Type . ")\n";
    }
    
    echo "\n\nColumnas en tabla 'productos':\n";
    $columns = DB::select("SHOW COLUMNS FROM productos");
    foreach ($columns as $column) {
        echo "- " . $column->Field . " (" . $column->Type . ")\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

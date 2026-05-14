<?php

try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=DevMart", "feminas", "admin");
    
    echo "Vendedores en la BD:\n";
    $stmt = $pdo->query("SELECT id_vendedor, razon_social FROM vendedores LIMIT 5");
    $vendedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($vendedores)) {
        echo "- No hay vendedores en la BD\n";
    } else {
        foreach ($vendedores as $v) {
            echo "- ID: " . $v['id_vendedor'] . ", Razón Social: " . $v['razon_social'] . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

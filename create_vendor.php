<?php

try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=DevMart", "feminas", "admin");
    
    // Crear un vendedor
    $vendorId = 'vendedor-demo-001';
    $stmt = $pdo->prepare("INSERT INTO vendedores (id_vendedor, razon_social, rfc, descripcion) VALUES (?, ?, ?, ?)");
    
    $result = $stmt->execute([
        $vendorId,
        'Vendedor DevMart',
        'RFC0001',
        'Vendedor de demostración'
    ]);
    
    if ($result) {
        echo "✓ Vendedor creado exitosamente\n";
        echo "ID: $vendorId\n";
    } else {
        echo "✗ Error al crear vendedor\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

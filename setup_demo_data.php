<?php

try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=DevMart", "feminas", "admin");
    
    // Crear IDs simples
    $userId = 'user-demo-001';
    $vendorId = 'vendor-demo-001';
    $productId = 'product-demo-001';
    
    echo "1. Creando usuario...\n";
    $stmt = $pdo->prepare("INSERT INTO usuarios (id_usuario, tipo, email, password_hash, nombre, fecha_registro) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $userId,
        'vendedor',
        'vendedor@devmart.com',
        password_hash('password123', PASSWORD_BCRYPT),
        'Vendedor DevMart'
    ]);
    echo "✓ Usuario creado: $userId\n\n";
    
    echo "2. Creando vendedor...\n";
    $stmt = $pdo->prepare("INSERT INTO vendedores (id_vendedor, razon_social, rfc, descripcion) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $vendorId,
        'Tienda DevMart',
        'RFC0001ABC',
        'Vendedor de demostración'
    ]);
    echo "✓ Vendedor creado: $vendorId\n\n";
    
    echo "3. Creando producto...\n";
    $stmt = $pdo->prepare("INSERT INTO productos (id_producto, id_vendedor, nombre, descripcion, precio, stock, categoria, estado, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $productId,
        $vendorId,
        'Producto de Prueba',
        'Este es un producto de prueba creado desde PHP',
        199.99,
        50,
        'Electrónica',
        'activo'
    ]);
    echo "✓ Producto creado: $productId\n\n";
    
    echo "=== RESUMEN ===\n";
    echo "Usuario ID: $userId\n";
    echo "Vendedor ID: $vendorId\n";
    echo "Producto ID: $productId\n";
    echo "\n¡Todo creado exitosamente!\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

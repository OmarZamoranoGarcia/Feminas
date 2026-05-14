<?php

try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=DevMart", "feminas", "admin");
    
    echo "Estructura de tabla 'usuarios':\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ") " . ($col['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . " " . ($col['Key'] ? "KEY: " . $col['Key'] : "") . "\n";
    }
    
    echo "\n\nClave primaria: id_usuario\n";
    echo "Usuarios existentes:\n";
    $stmt = $pdo->query("SELECT id_usuario, email FROM usuarios LIMIT 3");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($users)) {
        echo "- No hay usuarios\n";
    } else {
        foreach ($users as $u) {
            echo "- ID: " . $u['id_usuario'] . ", Email: " . $u['email'] . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

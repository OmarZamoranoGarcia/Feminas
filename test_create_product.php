<?php

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Crear una petición POST simulada
$request = Request::create('/api/products', 'POST', [], [], [], [
    'HTTP_CONTENT_TYPE' => 'application/json',
    'HTTP_X_CSRF_TOKEN' => csrf_token(),
], json_encode([
    'vendor_id' => 'test-vendor-001',
    'name' => 'Producto desde Script',
    'description' => 'Este es un producto creado desde un script PHP',
    'price' => 199.99,
    'stock' => 25,
    'category' => 'Scripts',
    'status' => 'activo',
    'img' => 'https://images.pexels.com/photos/1181671/pexels-photo-1181671.jpeg'
]));

$app->instance('request', $request);

// Pasar la petición a través del kernel
$response = $kernel->handle($request);

echo "Status: " . $response->status() . "\n";
echo "Response: " . $response->getContent() . "\n";

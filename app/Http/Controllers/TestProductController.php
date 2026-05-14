<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Vendedor;
use Illuminate\Support\Str;

class TestProductController extends Controller
{
    public function createTestProduct()
    {
        try {
            // Crear producto de prueba
            $vendorId = 'test-vendor-001';
            $product = Producto::create([
                'id_producto' => Str::uuid()->toString(),
                'id_vendedor' => $vendorId,
                'nombre' => 'Producto de Prueba',
                'descripcion' => 'Este es un producto de prueba',
                'precio' => 99.99,
                'stock' => 10,
                'categoria' => 'Test',
                'estado' => 'activo',
                'fecha_creacion' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Producto de prueba creado exitosamente',
                'product' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * GET /api/products
     * Returns active products, optionally filtered by search term and category.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Producto::activo()
            ->with('vendedor:id_vendedor,razon_social');

        // Full-text search on nombre and descripcion
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($categoria = $request->query('category')) {
            $query->where('categoria', $categoria);
        }

        $productos = $query
            ->orderBy('fecha_creacion', 'desc')
            ->limit(50)
            ->get()
            ->map(fn (Producto $p) => [
                'id'       => $p->id_producto,
                'name'     => $p->nombre,
                'desc'     => $p->descripcion,
                'price'    => number_format((float) $p->precio, 2),
                'stock'    => $p->stock,
                'category' => $p->categoria ?? 'general',
                'img'      => $p->imagen_url
                    ?? "https://placehold.co/400x240/1a1f35/5a6cff?text=" . urlencode($p->nombre),
                'seller'   => $p->vendedor?->razon_social ?? 'DevMart',
            ]);

        return response()->json($productos);
    }

    /**
     * GET /api/products/{id}
     */
    public function show(string $id): JsonResponse
    {
        $producto = Producto::activo()
            ->with(['vendedor', 'resenas.comprador'])
            ->findOrFail($id);

        return response()->json([
            'id'          => $producto->id_producto,
            'name'        => $producto->nombre,
            'desc'        => $producto->descripcion,
            'price'       => number_format((float) $producto->precio, 2),
            'stock'       => $producto->stock,
            'category'    => $producto->categoria,
            'img'         => $producto->imagen_url,
            'seller'      => $producto->vendedor?->razon_social,
            'rating'      => $producto->vendedor?->calificacion_promedio,
            'reviews'     => $producto->resenas->map(fn ($r) => [
                'score'    => $r->calificacion,
                'comment'  => $r->comentario,
                'buyer'    => $r->comprador?->nombre,
                'date'     => $r->fecha?->toDateString(),
            ]),
        ]);
    }
}

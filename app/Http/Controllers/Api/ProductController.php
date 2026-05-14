<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

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

        // Full-text search
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
            ->map(fn (Producto $p) => $this->formatProduct($p));

        return response()->json($productos);
    }


    // GET /api/products/{id}

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


// POST /api/products

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_id'   => ['required', 'string', 'exists:vendedores,id_vendedor'],
            'name'        => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            'category'    => ['nullable', 'string', 'max:100'],
            'status'      => ['nullable', 'in:activo,agotado,oculto'],
            'img'         => ['nullable', 'string', 'max:500'],
        ]);

        $producto = Producto::create([
            'id_producto'  => Str::uuid()->toString(),
            'id_vendedor'  => $validated['vendor_id'],
            'nombre'       => $validated['name'],
            'descripcion'  => $validated['description'] ?? null,
            'precio'       => $validated['price'],
            'stock'        => $validated['stock'],
            'categoria'    => $validated['category'] ?? 'general',
            'imagen_url'   => $validated['img'] ?? null,
            'estado'       => $validated['status'] ?? 'activo',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado correctamente.',
            'product' => $this->formatProduct($producto->load('vendedor')),
        ], 201);
    }


// PUT /api/products/{id}

    public function update(Request $request, string $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);

        // Optional: verify ownership
        $vendorId = $request->input('vendor_id');
        if ($vendorId && $producto->id_vendedor !== $vendorId) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar este producto.',
            ], 403);
        }

        $validated = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'price'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock'       => ['sometimes', 'required', 'integer', 'min:0'],
            'category'    => ['nullable', 'string', 'max:100'],
            'status'      => ['nullable', 'in:activo,agotado,oculto'],
            'img'         => ['nullable', 'string', 'max:500'],
        ]);

        $producto->update([
            'nombre'      => $validated['name']        ?? $producto->nombre,
            'descripcion' => $validated['description'] ?? $producto->descripcion,
            'precio'      => $validated['price']       ?? $producto->precio,
            'stock'       => $validated['stock']       ?? $producto->stock,
            'categoria'   => $validated['category']    ?? $producto->categoria,
            'imagen_url'  => $validated['img']         ?? $producto->imagen_url,
            'estado'      => $validated['status']      ?? $producto->estado,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado correctamente.',
            'product' => $this->formatProduct($producto->fresh('vendedor')),
        ]);
    }


// DELETE /api/products/{id}

    public function destroy(Request $request, string $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);

        $vendorId = $request->query('vendor_id');
        if ($vendorId && $producto->id_vendedor !== $vendorId) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este producto.',
            ], 403);
        }

        $producto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado correctamente.',
        ]);
    }

    // Helpers

    private function formatProduct(Producto $p): array
    {
        return [
            'id'          => $p->id_producto,
            'name'        => $p->nombre,
            'description' => $p->descripcion,
            'price'       => number_format((float) $p->precio, 2),
            'stock'       => $p->stock,
            'category'    => $p->categoria ?? 'general',
            'status'      => $p->estado,
            'img'         => $p->imagen_url
                ?? 'https://placehold.co/400x240/1a1f35/5a6cff?text=' . urlencode($p->nombre),
            'seller'      => $p->vendedor?->razon_social ?? 'DevMart',
        ];
    }
}

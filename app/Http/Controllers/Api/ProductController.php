<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Usuario;
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
            ->with('vendedor:id_usuario,nombre,razon_social,calificacion_promedio');

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

        // Vendor filter (used by vendor panel to scope their own products)
        if ($vendorId = $request->query('vendor_id')) {
            $query->where('id_vendedor', $vendorId);
        }

        $productos = $query
            ->orderBy('fecha_creacion', 'desc')
            ->limit(50)
            ->get()
            ->map(fn (Producto $p) => $this->formatProduct($p));

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
            'id'        => $producto->id_producto,
            'name'      => $producto->nombre,
            'desc'      => $producto->descripcion,
            'price'     => number_format((float) $producto->precio, 2),
            'stock'     => $producto->stock,
            'category'  => $producto->categoria,
            'img'       => $producto->imagen_url,
            'seller'    => $producto->vendedor?->nombreComercial(),
            'vendor_id' => $producto->id_vendedor,
            'rating'    => $producto->vendedor?->calificacion_promedio,
            'reviews'   => $producto->resenas->map(fn ($r) => [
                'score'   => $r->calificacion,
                'comment' => $r->comentario,
                'buyer'   => $r->comprador?->nombre,
                'date'    => $r->fecha?->toDateString(),
            ]),
        ]);
    }

    /**
     * POST /api/products
     */
    public function store(Request $request): JsonResponse
    {
        $maxImageSizeMb = 2;
        $maxImageSize   = $maxImageSizeMb * 1024 * 1024;

        $validated = $request->validate([
            'vendor_id'   => ['required', 'string', 'exists:usuarios,id_usuario'],
            'name'        => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            'category'    => ['nullable', 'string', 'max:100'],
            'status'      => ['nullable', 'in:activo,agotado,oculto'],
            'img'         => ['nullable', 'string', function ($attribute, $value, $fail) use ($maxImageSize, $maxImageSizeMb) {
                if (!str_starts_with($value, 'data:image/')) {
                    return;
                }
                $parts = explode(',', $value, 2);
                if (count($parts) !== 2) {
                    return $fail('La imagen no es válida.');
                }
                $decoded = base64_decode($parts[1], true);
                if ($decoded === false) {
                    return $fail('La imagen no es válida.');
                }
                if (strlen($decoded) > $maxImageSize) {
                    return $fail("La imagen debe pesar como máximo {$maxImageSizeMb} MB.");
                }
            }],
        ]);

        // Ensure the user is allowed to sell
        $seller = Usuario::findOrFail($validated['vendor_id']);
        if (! $seller->esVendedor()) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no tiene permisos de vendedor.',
            ], 403);
        }

        $producto = Producto::create([
            'id_producto' => Str::uuid()->toString(),
            'id_vendedor' => $validated['vendor_id'],
            'nombre'      => $validated['name'],
            'descripcion' => $validated['description'] ?? null,
            'precio'      => $validated['price'],
            'stock'       => $validated['stock'],
            'categoria'   => $validated['category'] ?? 'general',
            'imagen_url'  => $validated['img'] ?? null,
            'estado'      => $validated['status'] ?? 'activo',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado correctamente.',
            'product' => $this->formatProduct($producto->load('vendedor')),
        ], 201);
    }

    /**
     * PUT /api/products/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);

        // Ownership check: only run when vendor_id is provided AND it differs from the product's vendor.
        $vendorId = $request->input('vendor_id');
        if ($vendorId && $producto->id_vendedor !== $vendorId) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar este producto.',
            ], 403);
        }

        $maxImageSizeMb = 2;
        $maxImageSize   = $maxImageSizeMb * 1024 * 1024;

        $validated = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'price'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock'       => ['sometimes', 'required', 'integer', 'min:0'],
            'category'    => ['nullable', 'string', 'max:100'],
            'status'      => ['nullable', 'in:activo,agotado,oculto'],
            'img'         => ['nullable', 'string', function ($attribute, $value, $fail) use ($maxImageSize, $maxImageSizeMb) {
                if (!str_starts_with($value, 'data:image/')) {
                    return;
                }
                $parts = explode(',', $value, 2);
                if (count($parts) !== 2) {
                    return $fail('La imagen no es válida.');
                }
                $decoded = base64_decode($parts[1], true);
                if ($decoded === false) {
                    return $fail('La imagen no es válida.');
                }
                if (strlen($decoded) > $maxImageSize) {
                    return $fail("La imagen debe pesar como máximo {$maxImageSizeMb} MB.");
                }
            }],
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

    /**
     * DELETE /api/products/{id}
     */
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

    private function formatProduct(Producto $p): array
    {
        return [
            'id'          => $p->id_producto,
            'vendor_id'   => $p->id_vendedor,
            'name'        => $p->nombre,
            'description' => $p->descripcion,
            'price'       => number_format((float) $p->precio, 2),
            'stock'       => $p->stock,
            'category'    => $p->categoria ?? 'general',
            'status'      => $p->estado,
            'img'         => $p->imagen_url
                ?? 'https://placehold.co/400x240/1a1f35/5a6cff?text=' . urlencode($p->nombre),
            'seller'      => $p->vendedor?->nombreComercial() ?? 'DevMart',
        ];
    }
}

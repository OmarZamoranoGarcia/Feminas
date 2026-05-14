<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
<<<<<<< HEAD
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::query();

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")
                  ->orWhere('descripcion', 'like', "%{$term}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('categoria', $request->input('category'));
        }

        if ($request->filled('vendor_id')) {
            $query->where('id_vendedor', $request->input('vendor_id'));
        }

        $products = $query->orderBy('fecha_creacion', 'desc')->get();

        return response()->json($products->map(function (Producto $product) {
            return [
                'id' => $product->id_producto,
                'name' => $product->nombre,
                'description' => $product->descripcion,
                'price' => $product->precio,
                'stock' => $product->stock,
                'category' => $product->categoria,
                'status' => $product->estado,
                'vendor_id' => $product->id_vendedor,
                'img' => 'https://images.pexels.com/photos/1181671/pexels-photo-1181671.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260',
                'published_at' => $product->fecha_creacion?->toDateTimeString(),
            ];
        }));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'vendor_id' => 'nullable|string|max:191',
            'img' => 'nullable|string|max:255',
        ]);

        $vendorId = $data['vendor_id'] ?? 'vendedor-demo-001';

        $product = Producto::create([
            'id_producto' => Str::uuid()->toString(),
            'id_vendedor' => $vendorId,
            'nombre' => $data['name'],
            'descripcion' => $data['description'] ?? null,
            'precio' => $data['price'],
            'stock' => $data['stock'],
            'categoria' => $data['category'] ?? 'general',
            'estado' => $data['status'] ?? 'activo',
            'fecha_creacion' => now(),
        ]);

        return response()->json(['success' => true, 'product' => $product], 201);
    }

    public function update(Request $request, string $id)
    {
        $product = Producto::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'vendor_id' => 'required|string|max:191',
            'img' => 'nullable|string|max:255',
        ]);

        if ($product->id_vendedor !== $data['vendor_id']) {
            return response()->json(['success' => false, 'message' => 'Solo puedes actualizar tus propios productos.'], 403);
        }

        $product->update([
            'nombre' => $data['name'],
            'descripcion' => $data['description'] ?? $product->descripcion,
            'precio' => $data['price'],
            'stock' => $data['stock'],
            'categoria' => $data['category'] ?? $product->categoria,
            'estado' => $data['status'] ?? $product->estado,
        ]);

        return response()->json(['success' => true, 'product' => $product]);
    }

    public function destroy(Request $request, string $id)
    {
        $product = Producto::findOrFail($id);
        $vendorId = $request->query('vendor_id') ?? $request->input('vendor_id');

        if (!$vendorId || $product->id_vendedor !== $vendorId) {
            return response()->json(['success' => false, 'message' => 'Solo puedes eliminar tus propios productos.'], 403);
        }

        $product->delete();

        return response()->json(['success' => true]);
=======
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
>>>>>>> 9aa5508 (Refactorized database)
    }
}

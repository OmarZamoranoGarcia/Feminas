<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * GET /api/cart
     * Returns cart items for a user or anonymous session.
     */
    public function index(Request $request): JsonResponse
    {
        $items = $this->getCartQuery($request)->with('producto.vendedor')->get();

        return response()->json($items->map(fn ($item) => $this->formatItem($item)));
    }

    /**
     * POST /api/cart
     * Adds or increments a product in the cart.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id'    => ['required', 'string', 'exists:productos,id_producto'],
            'qty'           => ['nullable', 'integer', 'min:1'],
            'user_id'       => ['nullable', 'string'],
            'session_token' => ['nullable', 'string'],
        ]);

        if (empty($validated['user_id']) && empty($validated['session_token'])) {
            return response()->json(['success' => false, 'message' => 'Se requiere user_id o session_token.'], 422);
        }

        $qtyToAdd = $validated['qty'] ?? 1;

        $existing = Carrito::where('id_producto', $validated['product_id'])
            ->when(!empty($validated['user_id']),
                fn ($q) => $q->where('id_usuario', $validated['user_id']),
                fn ($q) => $q->where('session_token', $validated['session_token'])
            )
            ->first();

        if ($existing) {
            $existing->load('producto');
            if (!$existing->producto) {
                return response()->json(['success' => false, 'message' => 'Producto no encontrado.'], 404);
            }

            $newQty = $existing->cantidad + $qtyToAdd;
            if ($newQty > $existing->producto->stock) {
                return response()->json([
                    'success' => false,
                    'message' => "No hay stock suficiente. Solo quedan {$existing->producto->stock} unidades.",
                ], 422);
            }

            $existing->cantidad = $newQty;
            $existing->save();
            $item = $existing->fresh('producto.vendedor');
        } else {
            $product = Producto::find($validated['product_id']);
            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Producto no encontrado.'], 404);
            }
            if ($qtyToAdd > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => "No hay stock suficiente. Solo quedan {$product->stock} unidades.",
                ], 422);
            }

            $item = Carrito::create([
                'id_carrito'    => Str::uuid()->toString(),
                'id_usuario'    => $validated['user_id'] ?? null,
                'session_token' => $validated['session_token'] ?? null,
                'id_producto'   => $validated['product_id'],
                'cantidad'      => $qtyToAdd,
            ]);
            $item->load('producto.vendedor');
        }

        return response()->json([
            'success' => true,
            'item'    => $this->formatItem($item),
        ], 201);
    }

    /**
     * PUT /api/cart/{id}
     * Update quantity for an existing cart item.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'qty'           => ['required', 'integer', 'min:1'],
            'user_id'       => ['nullable', 'string'],
            'session_token' => ['nullable', 'string'],
        ]);

        $item = Carrito::with('producto')->findOrFail($id);

        $userId       = $request->query('user_id');
        $sessionToken = $request->query('session_token');

        $ownsItem = ($userId && $item->id_usuario === $userId)
                 || ($sessionToken && $item->session_token === $sessionToken);

        if (!$ownsItem) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        if (!$item->producto) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado.'], 404);
        }

        if ($validated['qty'] > $item->producto->stock) {
            return response()->json([
                'success' => false,
                'message' => "No hay stock suficiente. Solo quedan {$item->producto->stock} unidades.",
            ], 422);
        }

        $item->cantidad = $validated['qty'];
        $item->save();

        return response()->json(['success' => true, 'item' => $this->formatItem($item->fresh('producto.vendedor'))]);
    }

    /**
     * DELETE /api/cart/{id}
     * Removes a single cart item.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $item = Carrito::findOrFail($id);

        // Verify ownership
        $userId       = $request->query('user_id');
        $sessionToken = $request->query('session_token');

        $ownsItem = ($userId && $item->id_usuario === $userId)
                 || ($sessionToken && $item->session_token === $sessionToken);

        if (!$ownsItem) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $item->delete();

        return response()->json(['success' => true]);
    }

    // Helpers

    private function getCartQuery(Request $request)
    {
        $userId       = $request->query('user_id');
        $sessionToken = $request->query('session_token');

        return Carrito::when($userId,
            fn ($q) => $q->where('id_usuario', $userId),
            fn ($q) => $q->where('session_token', $sessionToken)
        );
    }

    private function formatItem(Carrito $item): array
    {
        $p = $item->producto;

        return [
            'cart_id' => $item->id_carrito,
            'qty'     => $item->cantidad,
            'product' => $p ? [
                'id'    => $p->id_producto,
                'name'  => $p->nombre,
                'price' => number_format((float) $p->precio, 2),
                'img'   => $p->imagen_url
                    ?? 'https://placehold.co/400x240/1a1f35/5a6cff?text=' . urlencode($p->nombre),
                'stock' => $p->stock ?? 0,
            ] : null,
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Comision;
use App\Models\DetalleOrden;
use App\Models\Orden;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\SplitPago;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'          => ['required', 'string', 'exists:usuarios,id_usuario'],
            'shipping_address' => ['required', 'string'],
            'shipping_type'    => ['required', 'in:standard,express'],
            'shipping_cost'    => ['required', 'numeric', 'min:0'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'string', 'exists:productos,id_producto'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
            'items.*.price'      => ['required', 'numeric', 'min:0'],
            'items.*.vendor_id'  => ['required', 'string'],
            'card_last4'       => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            $productosTotal = collect($validated['items'])
                ->sum(fn ($i) => $i['price'] * $i['qty']);

            $total = $productosTotal + $validated['shipping_cost'];

            $orderId = Str::uuid()->toString();
            $pagoId  = Str::uuid()->toString();

            $orden = Orden::create([
                'id_orden'            => $orderId,
                'id_comprador'        => $validated['user_id'],
                'estado'              => 'pagado',
                'total'               => $total,
                'metodo_pago'         => 'tarjeta_simulada',
                'direccion_envio'     => $validated['shipping_address'],
                'id_transaccion_pago' => null,
            ]);

            foreach ($validated['items'] as $item) {
                $producto = Producto::find($item['product_id']);
                if (!$producto || $producto->stock < $item['qty']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuficiente para: " . ($producto->nombre ?? $item['product_id']),
                    ], 422);
                }

                DetalleOrden::create([
                    'id_detalle'      => Str::uuid()->toString(),
                    'id_orden'        => $orderId,
                    'id_producto'     => $item['product_id'],
                    'cantidad'        => $item['qty'],
                    'precio_unitario' => $item['price'],
                    'id_vendedor'     => $item['vendor_id'],
                ]);

                $producto->decrement('stock', $item['qty']);
            }

            $pago = Pago::create([
                'id_pago'            => $pagoId,
                'id_orden'           => $orderId,
                'monto'              => $total,
                'metodo'             => 'tarjeta_simulada',
                'referencia_externa' => 'SIM-' . strtoupper(Str::random(12)),
                'estado'             => 'capturado',
            ]);

            $orden->id_transaccion_pago = $pagoId;
            $orden->save();

            $vendorMap = [];
            foreach ($validated['items'] as $item) {
                $vid = $item['vendor_id'];
                if (!isset($vendorMap[$vid])) {
                    $vendorMap[$vid] = 0;
                }
                $vendorMap[$vid] += $item['price'] * $item['qty'];
            }

            $splits = [];
            foreach ($vendorMap as $vid => $amount) {
                $rate       = Comision::tasaParaCategoria(null) / 100;
                $commission = round($amount * $rate, 2);
                $net        = round($amount - $commission, 2);

                SplitPago::create([
                    'id_split'          => Str::uuid()->toString(),
                    'id_pago'           => $pagoId,
                    'id_vendedor'       => $vid,
                    'monto_vendedor'    => $net,
                    'monto_comision'    => $commission,
                    'estado_liberacion' => 'pendiente',
                ]);

                $splits[] = [
                    'vendor_id'  => $vid,
                    'seller'     => $this->resolveSellerName($vid),
                    'amount'     => $amount,
                    'commission' => $commission,
                    'net'        => $net,
                ];
            }

            $cartIds = collect($validated['items'])->pluck('cart_id')->filter()->values()->toArray();
            if (!empty($cartIds)) {
                Carrito::whereIn('id_carrito', $cartIds)->delete();
            } else {
                Carrito::where('id_usuario', $validated['user_id'])->delete();
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'order_id' => $orderId,
                'total'    => $total,
                'splits'   => $splits,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error procesando el pedido: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function resolveSellerName(string $vendorId): string
    {
        $user = \App\Models\Usuario::find($vendorId);
        return $user ? $user->nombreComercial() : 'Vendedor';
    }
}

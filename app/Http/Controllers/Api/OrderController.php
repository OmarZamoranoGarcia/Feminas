<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Comision;
use App\Models\DetalleOrden;
use App\Models\Orden;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\Splitpago;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Validación
        $validated = $request->validate([
            'user_id'          => ['required', 'string'],
            'session_token'    => ['nullable', 'string'],
            'shipping_address' => ['required', 'string'],
            'shipping_type'    => ['required', 'in:standard,express'],
            'shipping_cost'    => ['required', 'numeric', 'min:0'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'string'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
            'items.*.price'      => ['required', 'numeric', 'min:0'],
            'items.*.vendor_id'  => ['required', 'string'],
            'items.*.cart_id'    => ['nullable', 'string'],
            'card_last4'       => ['nullable', 'string'],
        ]);

        // Verificar que el usuario existe
        $usuario = Usuario::find($validated['user_id']);
        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        DB::beginTransaction();

        try {
            // 1. PRIMERO crear la orden
            $orderId = Str::uuid()->toString();

            \Log::info('Creando orden:', ['order_id' => $orderId, 'user_id' => $validated['user_id']]);

            $orden = new Orden();
            $orden->id_orden = $orderId;
            $orden->id_comprador = $validated['user_id'];
            $orden->estado = 'pagado';
            $orden->total = 0; // Temporal, se actualizará después
            $orden->metodo_pago = 'tarjeta_simulada';
            $orden->direccion_envio = $validated['shipping_address'];
            $orden->fecha_orden = now();
            $orden->save();

            \Log::info('Orden creada exitosamente:', ['order_id' => $orderId]);

            // 2. Procesar items y crear detalles
            $productosTotal = 0;
            $vendorMap = [];

            foreach ($validated['items'] as $item) {
                $producto = Producto::find($item['product_id']);

                if (!$producto) {
                    throw new \Exception("Producto no encontrado: {$item['product_id']}");
                }

                // Verificar stock
                if ($producto->stock < $item['qty']) {
                    throw new \Exception("Stock insuficiente para: {$producto->nombre}");
                }

                // Obtener vendor_id del producto (ignorar el que viene del frontend)
                $vendorId = $producto->id_vendedor;
                $precioTotal = $item['price'] * $item['qty'];
                $productosTotal += $precioTotal;

                // Acumular por vendedor
                if (!isset($vendorMap[$vendorId])) {
                    $vendorMap[$vendorId] = 0;
                }
                $vendorMap[$vendorId] += $precioTotal;

                // Crear detalle de orden
                $detalle = new DetalleOrden();
                $detalle->id_detalle = Str::uuid()->toString();
                $detalle->id_orden = $orderId;
                $detalle->id_producto = $item['product_id'];
                $detalle->cantidad = $item['qty'];
                $detalle->precio_unitario = $item['price'];
                $detalle->id_vendedor = $vendorId;
                $detalle->save();

                \Log::info('Detalle creado:', [
                    'detalle_id' => $detalle->id_detalle,
                    'orden_id' => $orderId,
                    'producto_id' => $item['product_id']
                ]);

                // Actualizar stock
                $producto->decrement('stock', $item['qty']);
            }

            // 3. Actualizar total de la orden
            $total = $productosTotal + $validated['shipping_cost'];
            $orden->total = $total;
            $orden->save();

            // 4. Crear pago
            $pagoId = Str::uuid()->toString();
            $pago = new Pago();
            $pago->id_pago = $pagoId;
            $pago->id_orden = $orderId;
            $pago->monto = $total;
            $pago->metodo = 'tarjeta_simulada';
            $pago->referencia_externa = 'SIM-' . strtoupper(Str::random(12));
            $pago->estado = 'capturado';
            $pago->save();

            // 5. Actualizar orden con ID del pago
            $orden->id_transaccion_pago = $pagoId;
            $orden->save();

            // 6. Crear splits de pago
            $splits = [];
            foreach ($vendorMap as $vid => $amount) {
                // Obtener tasa de comisión
                try {
                    $rate = Comision::tasaParaCategoria(null) / 100;
                } catch (\Exception $e) {
                    $rate = 0.10; // 10% por defecto
                }

                $commission = round($amount * $rate, 2);
                $net = round($amount - $commission, 2);

                // Crear split
                $split = new SplitPago();
                $split->id_split = Str::uuid()->toString();
                $split->id_pago = $pagoId;
                $split->id_vendedor = $vid;
                $split->monto_vendedor = $net;
                $split->monto_comision = $commission;
                $split->estado_liberacion = 'pendiente';
                $split->save();

                $splits[] = [
                    'vendor_id'  => $vid,
                    'seller'     => $this->resolveSellerName($vid),
                    'amount'     => $amount,
                    'commission' => $commission,
                    'net'        => $net,
                ];
            }

            // 7. Limpiar carrito
            $cartIds = collect($validated['items'])
                ->pluck('cart_id')
                ->filter()
                ->values()
                ->toArray();

            if (!empty($cartIds)) {
                Carrito::whereIn('id_carrito', $cartIds)->delete();
            } else {
                Carrito::where('id_usuario', $validated['user_id'])->delete();
            }

            DB::commit();

            \Log::info('Orden completada exitosamente:', [
                'order_id' => $orderId,
                'total' => $total,
                'items_count' => count($validated['items'])
            ]);

            return response()->json([
                'success'  => true,
                'order_id' => $orderId,
                'total'    => $total,
                'splits'   => $splits,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error en orden:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated ?? []
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error procesando el pedido: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function resolveSellerName(string $vendorId): string
    {
        try {
            $user = Usuario::find($vendorId);
            return $user ? $user->nombreComercial() : 'Vendedor';
        } catch (\Exception $e) {
            return 'Vendedor';
        }
    }
}

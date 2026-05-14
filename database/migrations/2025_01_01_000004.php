<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 5. ORDENES (sin FK a pagos todavía para evitar circular)
        Schema::create('ordenes', function (Blueprint $table) {
            $table->uuid('id_orden')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('id_comprador');
            $table->timestamp('fecha_orden')->useCurrent();
            $table->enum('estado', ['pendiente', 'pagado', 'enviado', 'entregado', 'cancelado'])->default('pendiente');
            $table->decimal('total', 12, 2)->default(0.0);
            $table->string('metodo_pago', 50)->nullable();
            $table->text('direccion_envio');
            $table->uuid('id_transaccion_pago')->nullable();

            $table->foreign('id_comprador')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('restrict');

            $table->index('id_comprador');
            $table->index('estado');
            $table->index('fecha_orden');
        });

        // 6. DETALLE_ORDEN
        Schema::create('detalle_orden', function (Blueprint $table) {
            $table->uuid('id_detalle')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('id_orden');
            $table->uuid('id_producto');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->uuid('id_vendedor');

            $table->foreign('id_orden')
                  ->references('id_orden')
                  ->on('ordenes')
                  ->onDelete('cascade');

            $table->foreign('id_producto')
                  ->references('id_producto')
                  ->on('productos')
                  ->onDelete('restrict');

            $table->foreign('id_vendedor')
                  ->references('id_vendedor')
                  ->on('vendedores')
                  ->onDelete('restrict');

            $table->index('id_orden');
            $table->index('id_producto');
            $table->index('id_vendedor');
        });

        // 7. PAGOS
        Schema::create('pagos', function (Blueprint $table) {
            $table->uuid('id_pago')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('id_orden');
            $table->decimal('monto', 12, 2);
            $table->string('metodo', 50);
            $table->string('referencia_externa', 255)->nullable();
            $table->enum('estado', ['autorizado', 'capturado', 'reembolsado'])->default('autorizado');
            $table->timestamp('fecha')->useCurrent();

            $table->foreign('id_orden')
                  ->references('id_orden')
                  ->on('ordenes')
                  ->onDelete('restrict');

            $table->unique('id_orden');
            $table->index('referencia_externa');
            $table->index('estado');
        });

        // Now add the circular FK from ordenes → pagos
        Schema::table('ordenes', function (Blueprint $table) {
            $table->foreign('id_transaccion_pago')
                  ->references('id_pago')
                  ->on('pagos')
                  ->onDelete('set null');
        });

        // 8. SPLIT_PAGOS
        Schema::create('split_pagos', function (Blueprint $table) {
            $table->uuid('id_split')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('id_pago');
            $table->uuid('id_vendedor');
            $table->decimal('monto_vendedor', 12, 2);
            $table->decimal('monto_comision', 12, 2);
            $table->enum('estado_liberacion', ['pendiente', 'liberado'])->default('pendiente');

            $table->foreign('id_pago')
                  ->references('id_pago')
                  ->on('pagos')
                  ->onDelete('cascade');

            $table->foreign('id_vendedor')
                  ->references('id_vendedor')
                  ->on('vendedores')
                  ->onDelete('restrict');

            $table->index('id_pago');
            $table->index('id_vendedor');
            $table->index('estado_liberacion');
        });

        // 9. ENVIOS
        Schema::create('envios', function (Blueprint $table) {
            $table->uuid('id_envio')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('id_orden');
            $table->string('transportadora', 100)->nullable();
            $table->string('numero_guia', 100)->nullable();
            $table->timestamp('fecha_envio')->nullable();
            $table->timestamp('fecha_entrega')->nullable();
            $table->string('estado_envio', 50)->default('pendiente');

            $table->foreign('id_orden')
                  ->references('id_orden')
                  ->on('ordenes')
                  ->onDelete('restrict');

            $table->unique('id_orden');
            $table->index('numero_guia');
            $table->index('estado_envio');
        });
    }

    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropForeign(['id_transaccion_pago']);
        });
        Schema::dropIfExists('envios');
        Schema::dropIfExists('split_pagos');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('detalle_orden');
        Schema::dropIfExists('ordenes');
    }
};

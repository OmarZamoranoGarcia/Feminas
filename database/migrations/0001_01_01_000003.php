<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 2. VENDEDORES (extends usuarios)
        Schema::create('vendedores', function (Blueprint $table) {
            $table->uuid('id_vendedor')->primary();
            $table->string('razon_social', 200);
            $table->string('rfc', 13)->unique()->nullable();
            $table->text('descripcion')->nullable();
            $table->decimal('calificacion_promedio', 2, 1)->default(0.0);
            $table->text('politicas_devolucion')->nullable();
            $table->string('banco_cuenta', 100)->nullable();

            $table->foreign('id_vendedor')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('cascade');

            $table->index('rfc');
            $table->index('calificacion_promedio');
        });

        // 3. PRODUCTOS
        Schema::create('productos', function (Blueprint $table) {
            $table->uuid('id_producto')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('id_vendedor');
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->integer('stock')->default(0);
            $table->string('categoria', 100)->nullable();
            $table->string('imagen_url', 500)->nullable();
            $table->enum('estado', ['activo', 'agotado', 'oculto'])->default('activo');
            $table->timestamp('fecha_creacion')->useCurrent();

            $table->foreign('id_vendedor')
                  ->references('id_vendedor')
                  ->on('vendedores')
                  ->onDelete('cascade');

            $table->index('id_vendedor');
            $table->index('categoria');
            $table->index('estado');
            $table->index('precio');
        });

        // 4. CARRITO
        Schema::create('carrito', function (Blueprint $table) {
            $table->uuid('id_carrito')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('id_usuario')->nullable();
            $table->string('session_token', 255)->nullable();
            $table->uuid('id_producto');
            $table->integer('cantidad');
            $table->timestamp('fecha_agregado')->useCurrent();

            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('cascade');

            $table->foreign('id_producto')
                  ->references('id_producto')
                  ->on('productos')
                  ->onDelete('cascade');

            $table->index('id_usuario');
            $table->index('session_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrito');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('vendedores');
    }
};

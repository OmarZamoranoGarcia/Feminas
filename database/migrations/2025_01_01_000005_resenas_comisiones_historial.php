<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resenas', function (Blueprint $table) {
            $table->uuid('id_review')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('id_producto');
            $table->uuid('id_comprador');
            $table->integer('calificacion');
            $table->text('comentario')->nullable();
            $table->timestamp('fecha')->useCurrent();
            $table->text('respuesta_vendedor')->nullable();

            $table->foreign('id_producto')->references('id_producto')->on('productos')->onDelete('cascade');
            $table->foreign('id_comprador')->references('id_usuario')->on('usuarios')->onDelete('restrict');

            $table->unique(['id_producto', 'id_comprador']);
            $table->index('calificacion');
        });

        Schema::create('comisiones', function (Blueprint $table) {
            $table->uuid('id_comision')->primary()->default(DB::raw('(UUID())'));
            $table->string('categoria', 100)->nullable();
            $table->decimal('porcentaje', 5, 2);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);

            $table->index('categoria');
        });

        Schema::create('historial_precios', function (Blueprint $table) {
            $table->uuid('id_historial')->primary()->default(DB::raw('(UUID())'));
            $table->uuid('id_producto');
            $table->decimal('precio_anterior', 10, 2);
            $table->decimal('precio_nuevo', 10, 2);
            $table->timestamp('fecha_cambio')->useCurrent();

            $table->foreign('id_producto')->references('id_producto')->on('productos')->onDelete('cascade');
            $table->index('id_producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_precios');
        Schema::dropIfExists('comisiones');
        Schema::dropIfExists('resenas');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vendedores')) {
            Schema::create('vendedores', function (Blueprint $table) {
                $table->char('id_vendedor', 36)->primary();
                $table->string('razon_social', 200);
                $table->string('rfc', 13)->unique()->nullable();
                $table->text('descripcion')->nullable();
                $table->decimal('calificacion_promedio', 2, 1)->default(0.0);
                $table->text('politicas_devolucion')->nullable();
                $table->string('banco_cuenta', 100)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendedores');
    }
};

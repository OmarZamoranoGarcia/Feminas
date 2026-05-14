<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('productos')) {
            Schema::create('productos', function (Blueprint $table) {
                $table->char('id_producto', 36)->primary();
                $table->char('id_vendedor', 36);
                $table->string('nombre', 250);
                $table->text('descripcion')->nullable();
                $table->decimal('precio', 10, 2)->default(0);
                $table->integer('stock')->default(0);
                $table->string('categoria', 120)->nullable();
                $table->string('estado', 50)->default('activo');
                $table->string('img', 255)->nullable();
                $table->string('seo_url', 255)->nullable();
                $table->timestamp('fecha_publicacion')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};

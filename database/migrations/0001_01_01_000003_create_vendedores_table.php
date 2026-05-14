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
                $table->char('id_usuario', 36)->nullable();
                $table->string('nombre', 150)->nullable();
                $table->string('tienda', 200)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendedores');
    }
};

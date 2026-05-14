<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Merges the `vendedores` table into `usuarios`.
 *
 * Every usuario with tipo = 'vendedor' (or 'admin') can now act as a seller.
 * The FK columns in productos, detalle_orden, split_pagos and resenas that
 * previously pointed to vendedores.id_vendedor are re-pointed to
 * usuarios.id_usuario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('razon_social', 200)->nullable()->after('telefono');
            $table->string('rfc', 13)->nullable()->unique()->after('razon_social');
            $table->text('descripcion')->nullable()->after('rfc');
            $table->decimal('calificacion_promedio', 2, 1)->default(0.0)->after('descripcion');
            $table->text('politicas_devolucion')->nullable()->after('calificacion_promedio');
            $table->string('banco_cuenta', 100)->nullable()->after('politicas_devolucion');
        });

        if (Schema::hasTable('vendedores')) {
            DB::statement('
                UPDATE usuarios u
                JOIN vendedores v ON v.id_vendedor = u.id_usuario
                SET
                    u.razon_social          = v.razon_social,
                    u.rfc                   = v.rfc,
                    u.descripcion           = v.descripcion,
                    u.calificacion_promedio = v.calificacion_promedio,
                    u.politicas_devolucion  = v.politicas_devolucion,
                    u.banco_cuenta          = v.banco_cuenta
            ');


            // productos.id_vendedor > usuarios.id_usuario
            Schema::table('productos', function (Blueprint $table) {
                $table->dropForeign(['id_vendedor']);
            });
            Schema::table('productos', function (Blueprint $table) {
                $table->foreign('id_vendedor')
                      ->references('id_usuario')
                      ->on('usuarios')
                      ->onDelete('cascade');
            });

            // detalle_orden.id_vendedor > usuarios.id_usuario
            Schema::table('detalle_orden', function (Blueprint $table) {
                $table->dropForeign(['id_vendedor']);
            });
            Schema::table('detalle_orden', function (Blueprint $table) {
                $table->foreign('id_vendedor')
                      ->references('id_usuario')
                      ->on('usuarios')
                      ->onDelete('restrict');
            });

            // split_pagos.id_vendedor > usuarios.id_usuario
            Schema::table('split_pagos', function (Blueprint $table) {
                $table->dropForeign(['id_vendedor']);
            });
            Schema::table('split_pagos', function (Blueprint $table) {
                $table->foreign('id_vendedor')
                      ->references('id_usuario')
                      ->on('usuarios')
                      ->onDelete('restrict');
            });

            Schema::dropIfExists('vendedores');
        }
    }

    public function down(): void
    {
        // Recreate vendedores
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
        });

        // Restore FK in productos
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['id_vendedor']);
        });
        Schema::table('productos', function (Blueprint $table) {
            $table->foreign('id_vendedor')
                  ->references('id_vendedor')
                  ->on('vendedores')
                  ->onDelete('cascade');
        });

        // Restore FK in detalle_orden
        Schema::table('detalle_orden', function (Blueprint $table) {
            $table->dropForeign(['id_vendedor']);
        });
        Schema::table('detalle_orden', function (Blueprint $table) {
            $table->foreign('id_vendedor')
                  ->references('id_vendedor')
                  ->on('vendedores')
                  ->onDelete('restrict');
        });

        // Restore FK in split_pagos
        Schema::table('split_pagos', function (Blueprint $table) {
            $table->dropForeign(['id_vendedor']);
        });
        Schema::table('split_pagos', function (Blueprint $table) {
            $table->foreign('id_vendedor')
                  ->references('id_vendedor')
                  ->on('vendedores')
                  ->onDelete('restrict');
        });

        // Remove seller columns from usuarios
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn([
                'razon_social', 'rfc', 'descripcion',
                'calificacion_promedio', 'politicas_devolucion', 'banco_cuenta',
            ]);
        });
    }
};

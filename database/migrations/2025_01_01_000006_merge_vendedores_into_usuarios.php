<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * On a fresh install this migration is a no-op — 000000 and 000003 already
 * set up the correct schema (usuarios with seller columns, productos FK-ing
 * usuarios directly, no vendedores table).
 *
 * On an existing database created before the refactor this migration copies
 * vendor profile data from the old vendedores table into usuarios, re-points
 * the foreign keys, drops vendedores, and removes the old tipo enum column.
 */
return new class extends Migration
{
    public function up(): void
    {

        if (Schema::hasTable('vendedores')) {
            DB::statement('
                UPDATE usuarios u
                JOIN vendedores v ON v.id_vendedor = u.id_usuario
                SET
                    u.razon_social          = COALESCE(u.razon_social, v.razon_social),
                    u.rfc                   = COALESCE(u.rfc, v.rfc),
                    u.descripcion           = COALESCE(u.descripcion, v.descripcion),
                    u.calificacion_promedio = v.calificacion_promedio,
                    u.politicas_devolucion  = COALESCE(u.politicas_devolucion, v.politicas_devolucion),
                    u.banco_cuenta          = COALESCE(u.banco_cuenta, v.banco_cuenta)
            ');

            if (Schema::hasTable('productos')) {
                try { Schema::table('productos', fn ($t) => $t->dropForeign(['id_vendedor'])); }
                catch (\Throwable) {}
                Schema::table('productos', fn ($t) => $t->foreign('id_vendedor')
                    ->references('id_usuario')->on('usuarios')->onDelete('cascade'));
            }

            if (Schema::hasTable('detalle_orden')) {
                try { Schema::table('detalle_orden', fn ($t) => $t->dropForeign(['id_vendedor'])); }
                catch (\Throwable) {}
                Schema::table('detalle_orden', fn ($t) => $t->foreign('id_vendedor')
                    ->references('id_usuario')->on('usuarios')->onDelete('restrict'));
            }

            if (Schema::hasTable('split_pagos')) {
                try { Schema::table('split_pagos', fn ($t) => $t->dropForeign(['id_vendedor'])); }
                catch (\Throwable) {}
                Schema::table('split_pagos', fn ($t) => $t->foreign('id_vendedor')
                    ->references('id_usuario')->on('usuarios')->onDelete('restrict'));
            }

            Schema::dropIfExists('vendedores');
        }

        if (Schema::hasColumn('usuarios', 'tipo')) {
            Schema::table('usuarios', fn ($t) => $t->dropColumn('tipo'));
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('usuarios', 'tipo')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->enum('tipo', ['comprador', 'vendedor', 'admin'])
                      ->default('comprador')
                      ->after('id_usuario');
            });
            DB::statement("UPDATE usuarios SET tipo = CASE WHEN is_admin = 1 THEN 'admin' ELSE 'comprador' END");
        }
    }
};

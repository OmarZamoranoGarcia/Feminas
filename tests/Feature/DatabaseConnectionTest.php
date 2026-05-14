<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    /**
     * Verifica que la aplicación pueda conectarse a la base de datos configurada en el .env.
     */
    public function test_database_is_reachable(): void
    {
        try {
            // Intenta obtener el PDO para disparar la conexión
            DB::connection()->getPdo();
            
            $this->assertTrue(true, 'Conexión exitosa.');
        } catch (\Exception $e) {
            $this->fail('Error de conexión a la base de datos. Revisa tu archivo .env: ' . $e->getMessage());
        }
    }
}
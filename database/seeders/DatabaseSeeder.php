<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Vendedor;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $vendorId = 'vendedor-demo-001';
        Vendedor::firstOrCreate([
            'id_vendedor' => $vendorId,
        ], [
            'nombre' => 'DevMart Demo',
            'tienda' => 'Tienda DevMart',
        ]);

        Producto::firstOrCreate([
            'id_producto' => 'producto-demo-001',
        ], [
            'id_vendedor' => $vendorId,
            'nombre' => 'Curso de API REST',
            'descripcion' => 'Aprende a construir APIs seguras y escalables con Laravel.',
            'precio' => 49.99,
            'stock' => 15,
            'categoria' => 'backend',
            'estado' => 'activo',
            'img' => 'https://images.pexels.com/photos/1181671/pexels-photo-1181671.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260',
            'seo_url' => 'curso-de-api-rest',
            'fecha_publicacion' => now(),
        ]);
    }
}

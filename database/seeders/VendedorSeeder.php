<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendedor;

class VendedorSeeder extends Seeder
{
    public function run(): void
    {
        Vendedor::updateOrCreate(
            ['id_vendedor' => 'vendedor-demo-001'],
            [
                'razon_social' => 'DevMart Tienda Demo',
                'rfc' => 'DEMO123456789',
                'descripcion' => 'Vendedor por defecto para pruebas de desarrollo.',
            ]
        );
    }
}
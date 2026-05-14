<?php

namespace Database\Seeders;

use App\Models\Comision;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin account — elevated privileges
        Usuario::create([
            'id_usuario'    => Str::uuid()->toString(),
            'is_admin'      => true,
            'email'         => 'admin@devmart.test',
            'password_hash' => Hash::make('password'),
            'nombre'        => 'Admin DevMart',
            'razon_social'  => 'DevMart',
        ]);

        // Regular users — every one of them can buy AND sell
        $vendedoresData = [
            [
                'nombre'       => 'Carlos Backend',
                'email'        => 'carlos@devmart.test',
                'razon_social' => 'Backend Solutions SA',
                'rfc'          => 'BSS010101AAA',
            ],
            [
                'nombre'       => 'Ana Frontend',
                'email'        => 'ana@devmart.test',
                'razon_social' => 'Frontend Lab',
                'rfc'          => 'FRL020202BBB',
            ],
        ];

        $vendedores = [];
        foreach ($vendedoresData as $vData) {
            $vendedores[] = Usuario::create([
                'id_usuario'    => Str::uuid()->toString(),
                'is_admin'      => false,
                'email'         => $vData['email'],
                'password_hash' => Hash::make('password'),
                'nombre'        => $vData['nombre'],
                'razon_social'  => $vData['razon_social'],
                'rfc'           => $vData['rfc'],
                'descripcion'   => 'Especialistas en soluciones digitales para desarrolladores.',
            ]);
        }

        $productosData = [
            [
                'vendedor' => 0,
                'nombre'   => 'API de Autenticación JWT',
                'desc'     => 'Módulo listo para producción: login, refresh tokens, roles y middleware incluidos.',
                'precio'   => 49.99,
                'stock'    => 999,
                'cat'      => 'backend',
            ],
            [
                'vendedor' => 0,
                'nombre'   => 'Microservicio de Pagos',
                'desc'     => 'Integración con Stripe y PayPal. Webhooks, split-payments y reportes incluidos.',
                'precio'   => 129.00,
                'stock'    => 500,
                'cat'      => 'backend',
            ],
            [
                'vendedor' => 0,
                'nombre'   => 'Kit ORM Multi-tenant',
                'desc'     => 'Gestión de bases de datos multi-tenant con Eloquent. Soporte MariaDB y PostgreSQL.',
                'precio'   => 79.99,
                'stock'    => 200,
                'cat'      => 'backend',
            ],
            [
                'vendedor' => 1,
                'nombre'   => 'Dashboard React Admin',
                'desc'     => 'Plantilla de panel de administración con Tailwind CSS, dark mode y gráficas integradas.',
                'precio'   => 59.99,
                'stock'    => 999,
                'cat'      => 'frontend',
            ],
            [
                'vendedor' => 1,
                'nombre'   => 'Componentes UI Vue 3',
                'desc'     => 'Librería de 50+ componentes accesibles para Vue 3 con Composition API.',
                'precio'   => 39.99,
                'stock'    => 999,
                'cat'      => 'frontend',
            ],
            [
                'vendedor' => 1,
                'nombre'   => 'Landing Page Starter Kit',
                'desc'     => 'Plantilla de página de aterrizaje con animaciones CSS, formularios y SEO optimizado.',
                'precio'   => 24.99,
                'stock'    => 999,
                'cat'      => 'frontend',
            ],
        ];

        foreach ($productosData as $pData) {
            Producto::create([
                'id_producto' => Str::uuid()->toString(),
                'id_vendedor' => $vendedores[$pData['vendedor']]->id_usuario,
                'nombre'      => $pData['nombre'],
                'descripcion' => $pData['desc'],
                'precio'      => $pData['precio'],
                'stock'       => $pData['stock'],
                'categoria'   => $pData['cat'],
                'estado'      => 'activo',
            ]);
        }

        // A regular user to test buying
        Usuario::create([
            'id_usuario'    => Str::uuid()->toString(),
            'is_admin'      => false,
            'email'         => 'test@example.com',
            'password_hash' => Hash::make('password'),
            'nombre'        => 'Test User',
        ]);

        Comision::create([
            'id_comision'  => Str::uuid()->toString(),
            'categoria'    => null,
            'porcentaje'   => 10.00,
            'fecha_inicio' => now()->toDateString(),
            'activo'       => true,
        ]);
    }
}

<?php

namespace Database\Seeders;

<<<<<<< HEAD
use App\Models\Producto;
use App\Models\Vendedor;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
=======
use App\Models\Comision;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\Vendedor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
>>>>>>> 9aa5508 (Refactorized database)
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin user
        Usuario::create([
            'id_usuario'    => Str::uuid()->toString(),
            'tipo'          => 'admin',
            'email'         => 'admin@devmart.test',
            'password_hash' => Hash::make('password'),
            'nombre'        => 'Admin DevMart',
        ]);

        // Vendor users + vendedor records
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
            $uuid = Str::uuid()->toString();
            $usuario = Usuario::create([
                'id_usuario'    => $uuid,
                'tipo'          => 'vendedor',
                'email'         => $vData['email'],
                'password_hash' => Hash::make('password'),
                'nombre'        => $vData['nombre'],
            ]);
            $vendedor = Vendedor::create([
                'id_vendedor'  => $uuid,
                'razon_social' => $vData['razon_social'],
                'rfc'          => $vData['rfc'],
                'descripcion'  => 'Especialistas en soluciones digitales para desarrolladores.',
            ]);
            $vendedores[] = $vendedor;
        }

        // Products
        $productosData = [
            // Carlos – backend
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
            // Ana – frontend
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
                'id_vendedor' => $vendedores[$pData['vendedor']]->id_vendedor,
                'nombre'      => $pData['nombre'],
                'descripcion' => $pData['desc'],
                'precio'      => $pData['precio'],
                'stock'       => $pData['stock'],
                'categoria'   => $pData['cat'],
                'estado'      => 'activo',
            ]);
        }

        // Default buyer
        Usuario::create([
            'id_usuario'    => Str::uuid()->toString(),
            'tipo'          => 'comprador',
            'email'         => 'test@example.com',
            'password_hash' => Hash::make('password'),
            'nombre'        => 'Test User',
        ]);

        // Default commission
        Comision::create([
            'id_comision'  => Str::uuid()->toString(),
            'categoria'    => null,
            'porcentaje'   => 10.00,
            'fecha_inicio' => now()->toDateString(),
            'activo'       => true,
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

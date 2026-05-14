# DevMart — Marketplace de Desarrollo

DevMart es una plataforma de marketplace multi-vendedor construida con **Laravel 13** (backend + API REST) y un frontend en **Bootstrap 5** servido directamente por Blade. Permite a compradores explorar y añadir productos al carrito, y a vendedores administrar su catálogo desde un panel dedicado.

---

## Tabla de Contenidos

1. [Tecnologías](#tecnologías)
2. [Estructura del Proyecto](#estructura-del-proyecto)
3. [Arquitectura y Flujo](#arquitectura-y-flujo)
4. [Base de Datos](#base-de-datos)
5. [API REST](#api-rest)
6. [Autenticación](#autenticación)
7. [Vistas y Frontend](#vistas-y-frontend)
8. [Requisitos Previos](#requisitos-previos)
9. [Instalación y Configuración](#instalación-y-configuración)
10. [Comandos de Desarrollo](#comandos-de-desarrollo)
11. [Usuarios de Prueba](#usuarios-de-prueba)
12. [Variables de Entorno](#variables-de-entorno)

---

## Tecnologías

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.3 + Laravel 13 |
| Base de datos | MariaDB / MySQL |
| ORM | Eloquent |
| Frontend | Bootstrap 5.3 + Bootstrap Icons |
| Estilos adicionales | CSS personalizado (`public/css/style.css`) + Tailwind (vía Vite) |
| Build tool | Vite 8 + laravel-vite-plugin |
| Autenticación | Sesiones de Laravel (session-based, sin tokens JWT) |
| Testing | PHPUnit 12 |

---

## Estructura del Proyecto

```
devmart/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php      # Login, logout, /me
│   │   │   ├── CartController.php      # Gestión del carrito
│   │   │   └── ProductController.php   # CRUD de productos
│   │   ├── Auth/
│   │   │   └── RegisterController.php  # Registro de usuarios
│   │   └── WelcomeController.php       # Página principal
│   └── Models/
│       ├── Usuario.php       # Usuarios (compradores, vendedores, admin)
│       ├── Vendedor.php      # Perfil de vendedor
│       ├── Producto.php      # Productos del catálogo
│       ├── Carrito.php       # Items del carrito
│       ├── Orden.php         # Órdenes de compra
│       ├── DetalleOrden.php  # Líneas de cada orden
│       ├── Pago.php          # Registros de pago
│       ├── SplitPago.php     # Distribución de pagos por vendedor
│       ├── Envio.php         # Información de envíos
│       ├── Resena.php        # Reseñas de productos
│       ├── Comision.php      # Tasas de comisión por categoría
│       └── HistorialPrecio.php # Historial de cambios de precio
├── database/
│   ├── migrations/           # Migraciones de todas las tablas
│   ├── seeders/
│   │   └── DatabaseSeeder.php  # Datos de prueba (admin, vendedores, productos)
│   └── .sql                  # Script SQL original (MariaDB)
├── resources/views/
│   ├── welcome.blade.php     # Página principal / catálogo
│   ├── login.blade.php       # Formulario de login
│   ├── register.blade.php    # Formulario de registro
│   ├── admin.blade.php       # Panel de gestión de productos
│   └── partials/
│       └── auth-sync.blade.php  # Sincronización de sesión con localStorage
├── routes/
│   ├── web.php               # Rutas web (vistas Blade)
│   └── api.php               # Rutas de la API REST
├── public/
│   └── css/style.css         # Tema visual personalizado
└── bootstrap/
    └── app.php               # Configuración del kernel y middlewares
```

---

## Arquitectura y Flujo

DevMart sigue un enfoque de **SPA liviana sobre Blade**: las vistas son páginas Blade estáticas que usan JavaScript (`fetch`) para comunicarse con la API REST en `/api/*`. No hay framework de frontend separado (React/Vue) — todo corre en el mismo servidor Laravel.

```
Navegador
   │
   ├── GET /             → welcome.blade.php  (catálogo)
   ├── GET /login        → login.blade.php
   ├── GET /register     → register.blade.php
   ├── GET /admin        → admin.blade.php    (panel vendedor)
   │
   └── fetch() → API REST (/api/*)
          ├── POST /api/login
          ├── GET  /api/me
          ├── GET  /api/products
          ├── POST /api/products
          ├── PUT  /api/products/{id}
          ├── DELETE /api/products/{id}
          ├── GET  /api/cart
          ├── POST /api/cart
          └── DELETE /api/cart/{id}
```

La **sesión** se mantiene en el servidor (cookie de sesión). El estado del usuario también se persiste en `localStorage` para que la UI pueda reaccionar sin hacer fetch adicionales. Al cargar cada página, el partial `auth-sync.blade.php` llama a `/api/me` para sincronizar ambas fuentes de verdad.

---

## Base de Datos

El esquema completo está en `database/.sql`. Las migraciones de Laravel replican ese mismo esquema y son la forma recomendada de crear las tablas.

### Tablas principales

| Tabla | Descripción |
|---|---|
| `usuarios` | Todos los usuarios: compradores, vendedores y admins |
| `vendedores` | Extiende `usuarios` con datos comerciales (razón social, RFC, etc.) |
| `productos` | Catálogo de productos por vendedor |
| `carrito` | Ítems del carrito; soporta usuarios autenticados y sesiones anónimas |
| `ordenes` | Cabecera de órdenes de compra |
| `detalle_orden` | Líneas de producto por orden |
| `pagos` | Registro de transacciones de pago |
| `split_pagos` | Distribución del pago entre cada vendedor y la comisión del marketplace |
| `envios` | Datos de envío y seguimiento |
| `resenas` | Reseñas de compradores sobre productos |
| `comisiones` | Porcentajes de comisión globales o por categoría |
| `historial_precios` | Registro automático de cambios de precio |

### Relaciones clave

- Un `Usuario` con `tipo = 'vendedor'` tiene un registro en `Vendedor` con el mismo UUID.
- Un `Vendedor` tiene muchos `Producto`.
- Un `Carrito` pertenece a un `Usuario` **o** a un `session_token` (para usuarios no autenticados).
- Una `Orden` tiene muchos `DetalleOrden`, un `Pago` y un `Envio`.
- Un `Pago` tiene muchos `SplitPago` (uno por vendedor involucrado en la orden).

---

## API REST

Todas las rutas están bajo el prefijo `/api`. La API usa sesiones de Laravel (cookies), por lo que los endpoints de escritura requieren el header `X-CSRF-TOKEN`.

### Autenticación

| Método | Ruta | Descripción |
|---|---|---|
| POST | `/api/login` | Inicia sesión. Devuelve datos del usuario. |
| POST | `/api/logout` | Cierra la sesión activa. |
| GET | `/api/me` | Devuelve el usuario autenticado o `401`. |

### Productos

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/products` | Lista productos activos. Acepta `?search=` y `?category=`. |
| GET | `/api/products/{id}` | Detalle de un producto con reseñas. |
| POST | `/api/products` | Crea un producto (requiere `vendor_id`). |
| PUT | `/api/products/{id}` | Actualiza un producto. |
| DELETE | `/api/products/{id}` | Elimina un producto. |

### Carrito

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/cart` | Lista ítems del carrito. Requiere `?user_id=` o `?session_token=`. |
| POST | `/api/cart` | Añade o incrementa un producto en el carrito. |
| DELETE | `/api/cart/{id}` | Elimina un ítem del carrito. |

---

## Autenticación

El sistema usa **autenticación por sesión** (no JWT). El flujo es:

1. El cliente envía `POST /api/login` con email y contraseña.
2. El servidor valida, crea la sesión (`session()->put('user_id', ...)`) y devuelve los datos del usuario en JSON.
3. El cliente guarda los datos en `localStorage` y la cookie de sesión se gestiona automáticamente por el navegador.
4. Cada página incluye `auth-sync.blade.php`, que llama a `GET /api/me` para validar que la sesión sigue activa y mantener `localStorage` sincronizado.

Los middlewares `EncryptCookies`, `AddQueuedCookiesToResponse` y `StartSession` se aplican también a las rutas API (configurado en `bootstrap/app.php`) para que las sesiones funcionen en la SPA.

El CSRF está **desactivado para `/api/*`** ya que el token se pasa manualmente como header `X-CSRF-TOKEN` desde el frontend.

---

## Vistas y Frontend

| Vista | Ruta | Descripción |
|---|---|---|
| `welcome.blade.php` | `/` | Catálogo con búsqueda, filtros por categoría y carrito lateral |
| `login.blade.php` | `/login` | Formulario de inicio de sesión |
| `register.blade.php` | `/register` | Formulario de registro |
| `admin.blade.php` | `/admin` | Panel de gestión de productos del vendedor |

El tema visual (`public/css/style.css`) define un sistema de diseño con variables CSS para modo claro/oscuro. El modo oscuro se activa con el atributo `data-bs-theme="dark"` en el `<html>` y se persiste en `localStorage`.

---

## Requisitos Previos

- **PHP** >= 8.3 con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`
- **Composer** >= 2
- **Node.js** >= 18 + **npm**
- **MariaDB** >= 10.6 o **MySQL** >= 8.0
- (Opcional) **Laravel Artisan** disponible globalmente

---

## Instalación y Configuración

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd devmart
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Copiar y configurar el entorno

```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` con los datos de la base de datos:

```env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=marketplace
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 4. Crear la base de datos

```sql
CREATE DATABASE marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Ejecutar migraciones y seeders

```bash
# Solo migraciones (crea las tablas)
php artisan migrate

# Migraciones + datos de prueba
php artisan migrate --seed
```

Los seeders crean automáticamente:
- Un usuario **admin** (`admin@devmart.test` / `password`)
- Dos vendedores con productos de ejemplo
- Un comprador de prueba (`test@example.com` / `password`)
- Una comisión global del 10%

### 6. Instalar dependencias de Node y compilar assets

```bash
npm install
npm run build
```

### 7. Configurar el almacenamiento

```bash
php artisan storage:link
```

---

## Comandos de Desarrollo

### Iniciar el servidor de desarrollo completo

```bash
composer run dev
```

Esto lanza en paralelo:
- `php artisan serve` — servidor PHP en `http://localhost:8000`
- `npm run dev` — Vite con hot-reload
- `php artisan queue:listen` — procesador de colas
- `php artisan pail` — visor de logs en tiempo real

### Solo el servidor web

```bash
php artisan serve
```

### Solo Vite (assets)

```bash
npm run dev
```

### Ejecutar tests

```bash
composer run test
# o directamente:
php artisan test
```

### Limpiar cachés

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

## Usuarios de Prueba

Creados por el seeder (`php artisan migrate --seed`):

| Rol | Email | Contraseña |
|---|---|---|
| Admin | `admin@devmart.test` | `password` |
| Vendedor (Backend) | `carlos@devmart.test` | `password` |
| Vendedor (Frontend) | `ana@devmart.test` | `password` |
| Comprador | `test@example.com` | `password` |

---

## Variables de Entorno

| Variable | Descripción | Valor por defecto |
|---|---|---|
| `APP_NAME` | Nombre de la aplicación | `Laravel` |
| `APP_ENV` | Entorno (`local`, `production`) | `local` |
| `APP_KEY` | Clave de cifrado (generada con `artisan key:generate`) | — |
| `APP_DEBUG` | Mostrar errores detallados | `true` |
| `APP_URL` | URL base de la aplicación | `http://localhost` |
| `DB_CONNECTION` | Driver de BD (`mariadb`, `mysql`) | `mariadb` |
| `DB_HOST` | Host de la BD | `localhost` |
| `DB_PORT` | Puerto de la BD | `3306` |
| `DB_DATABASE` | Nombre de la base de datos | `marketplace` |
| `DB_USERNAME` | Usuario de la BD | — |
| `DB_PASSWORD` | Contraseña de la BD | — |
| `SESSION_DRIVER` | Driver de sesión (`database`, `file`) | `database` |
| `SESSION_LIFETIME` | Duración de la sesión en minutos | `120` |
| `CACHE_STORE` | Driver de caché | `database` |
| `QUEUE_CONNECTION` | Driver de colas | `database` |

---

## Notas Adicionales

- El carrito funciona tanto para **usuarios autenticados** (identificados por `user_id`) como para **visitantes anónimos** (identificados por un `session_token` generado en el cliente y guardado en `localStorage`).
- La tabla `split_pagos` está diseñada para soportar pagos divididos entre múltiples vendedores en una misma orden, descontando la comisión del marketplace por cada venta.
- Los triggers SQL del archivo `database/.sql` (historial de precios y recalculado de calificación promedio de vendedores) deben ejecutarse manualmente si se usa el script SQL en lugar de las migraciones de Laravel.
- El archivo `public/css/style.css` define el tema completo con soporte para modo claro y oscuro usando variables CSS nativas, compatible con el sistema `data-bs-theme` de Bootstrap 5.
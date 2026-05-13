<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - DevMart</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <style>
        body { transition: background-color 0.3s ease, color 0.3s ease; }
        .dark-mode-toggle { cursor: pointer; font-size: 1.5rem; }
        .admin-container { min-height: 80vh; }
    </style>

    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">DevMart <span class="badge bg-primary fs-6">Admin</span></a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Ver Sitio</a></li>
                    <li class="nav-item ms-3">
                        <button class="btn btn-link nav-link dark-mode-toggle" id="darkModeToggle">☀️</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5 admin-container">
        <h1 class="mb-4">Panel de Administración</h1>

        <!-- Navegación por Pestañas -->
        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="productos-tab" data-bs-toggle="tab" data-bs-target="#productos" type="button" role="tab">CRUD Productos</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ordenes-tab" data-bs-toggle="tab" data-bs-target="#ordenes" type="button" role="tab">Ver Órdenes</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock" type="button" role="tab">Ver Stock</button>
            </li>
        </ul>

        <!-- Contenido de las Pestañas -->
        <div class="tab-content pt-3" id="adminTabsContent">
            
            <!-- Sección CRUD Productos -->
            <div class="tab-pane fade show active" id="productos" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Gestión de Productos</h3>
                    <button class="btn btn-success">+ Nuevo Producto</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover border">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Categoría</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#1</td>
                                <td>API de Autenticación</td>
                                <td>$49.99</td>
                                <td>Backend</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">Editar</button>
                                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sección Órdenes -->
            <div class="tab-pane fade" id="ordenes" role="tabpanel">
                <h3>Últimas Órdenes</h3>
                <div class="table-responsive">
                    <table class="table table-striped border">
                        <thead>
                            <tr>
                                <th>ID Orden</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#ORD-552</td>
                                <td>Juan Pérez</td>
                                <td>2024-05-13</td>
                                <td>$120.00</td>
                                <td><span class="badge bg-success">Completado</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sección Stock -->
            <div class="tab-pane fade" id="stock" role="tabpanel">
                <h3>Control de Inventario</h3>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-center p-3 mb-3">
                            <h5>Total de Licencias</h5>
                            <p class="display-6 fw-bold">1,240</p>
                        </div>
                    </div>
                </div>
                <p class="text-muted italic">Aquí se mostrarán las alertas de stock bajo y disponibilidad de servicios.</p>
            </div>
        </div>
    </div>

    <footer class="bg-body-tertiary text-center py-4 mt-5 border-top">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} DevMart Admin. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButton = document.getElementById('darkModeToggle');
            const htmlElement = document.documentElement;
            toggleButton.innerHTML = htmlElement.getAttribute('data-bs-theme') === 'dark' ? '🌙' : '☀️';
            toggleButton.addEventListener('click', () => {
                const newTheme = htmlElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
                htmlElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                toggleButton.innerHTML = newTheme === 'dark' ? '🌙' : '☀️';
            });
        });
    </script>
</body>
</html>
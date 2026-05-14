<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - DevMart</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <style>
        body { transition: background-color 0.3s ease, color 0.3s ease; }
        .dark-mode-toggle { cursor: pointer; font-size: 1.5rem; }
    </style>

    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top py-3">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">DevMart</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="ms-auto d-flex align-items-center flex-wrap gap-2 nav-actions">
                    <a class="nav-link" href="{{ route('home') }}">Inicio</a>
                    <a class="btn btn-ghost" href="{{ route('register') }}">Registrarse</a>
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('admin') }}">Admin</a>
                    <button class="btn btn-link dark-mode-toggle" id="darkModeToggle">☀️</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-5 py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card glass shadow border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h3 class="mb-0">Bienvenido de nuevo</h3>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted text-center mb-4">Ingresa tus credenciales para acceder</p>
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" placeholder="nombre@ejemplo.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Ingresar</button>
                            </div>
                        </form>
                        <div class="text-center mt-4">
                            <p class="mb-2 text-muted small">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
                            <a href="{{ route('home') }}" class="text-decoration-none">← Volver al inicio</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-body-tertiary text-center py-4 mt-5 border-top">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} DevMart. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Lógica de Login
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Simulamos el guardado de sesión
            localStorage.setItem('userLoggedIn', 'true');
            // Simulamos un carrito inicial si no existe para que veas algo en el panel
            if (!localStorage.getItem('cart')) {
                localStorage.setItem('cart', JSON.stringify([{id: 1, name: 'API de Pagos', price: 29.99}]));
            }
            window.location.href = "{{ route('home') }}";
        });

        // Lógica de Dark Mode (Debe repetirse en cada archivo ahora)
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButton = document.getElementById('darkModeToggle');
            if (toggleButton) {
                const htmlElement = document.documentElement;
                toggleButton.innerHTML = htmlElement.getAttribute('data-bs-theme') === 'dark' ? '🌙' : '☀️';

                toggleButton.addEventListener('click', () => {
                    const newTheme = htmlElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
                    htmlElement.setAttribute('data-bs-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    toggleButton.innerHTML = newTheme === 'dark' ? '🌙' : '☀️';
                });
            }
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - DevMart</title>
    
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
    <nav class="navbar navbar-expand-lg bg-body-tertiary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">DevMart</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Iniciar Sesión</a></li>
                    <li class="nav-item ms-3">
                        <button class="btn btn-link nav-link dark-mode-toggle" id="darkModeToggle">☀️</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5 py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h3 class="mb-0">Únete a DevMart</h3>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted text-center mb-4">Completa tus datos para crear una cuenta</p>
                        <form id="registerForm" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre Completo</label>
                                <input type="text" name="name" class="form-control" id="name" placeholder="Tu nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" id="email" placeholder="nombre@ejemplo.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" id="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Registrarse</button>
                            </div>
                        </form>
                        <div class="text-center mt-4">
                            <p class="mb-2 text-muted small">¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
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
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("{{ route('register.store') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('¡Registro exitoso! Ahora puedes iniciar sesión con tu cuenta real.');
                    window.location.href = "{{ route('login') }}";
                } else {
                    alert('Error en el registro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al procesar el registro. Asegúrate de que la base de datos esté conectada.');
            });
        });

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
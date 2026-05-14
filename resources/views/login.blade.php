<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - DevMart</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.auth-sync')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

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
            <div class="ms-auto d-flex align-items-center gap-2">
                <a class="nav-link" href="{{ route('home') }}">Inicio</a>
                <a class="btn btn-ghost" href="{{ route('register') }}">Registrarse</a>
                <button class="btn btn-link" id="darkModeToggle" style="font-size:1.5rem">☀️</button>
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
                        <div id="errorAlert" class="alert alert-danger d-none" role="alert"></div>

                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email"
                                       placeholder="nombre@ejemplo.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <span id="submitLabel">Ingresar</span>
                                    <span id="submitSpinner" class="d-none">
                                        <span class="spinner-border spinner-border-sm me-1"></span>Entrando...
                                    </span>
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-2 text-muted small">
                                ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a>
                            </p>
                            <a href="{{ route('home') }}" class="text-decoration-none">← Volver al inicio</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn    = document.getElementById('submitBtn');
        const submitLabel  = document.getElementById('submitLabel');
        const submitSpinner = document.getElementById('submitSpinner');
        const errorAlert   = document.getElementById('errorAlert');

        // Loading state
        submitBtn.disabled = true;
        submitLabel.classList.add('d-none');
        submitSpinner.classList.remove('d-none');
        errorAlert.classList.add('d-none');

        try {
            const res = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                },
                body: JSON.stringify({
                    email:    document.getElementById('email').value,
                    password: document.getElementById('password').value,
                }),
            });

            const data = await res.json();

            if (data.success) {
                // Persist user info — vendor_id will be the real UUID from the DB
                localStorage.setItem('userLoggedIn', 'true');
                localStorage.setItem('userId',    data.user.id);
                localStorage.setItem('userName',  data.user.name);
                localStorage.setItem('userTipo',  data.user.tipo);
                localStorage.setItem('vendorId',  data.user.vendor_id ?? '');

                window.location.href = "{{ route('home') }}";
            } else {
                errorAlert.textContent = data.message ?? 'Error al iniciar sesión.';
                errorAlert.classList.remove('d-none');
            }
        } catch {
            errorAlert.textContent = 'Error de conexión con el servidor.';
            errorAlert.classList.remove('d-none');
        }

        submitBtn.disabled = false;
        submitLabel.classList.remove('d-none');
        submitSpinner.classList.add('d-none');
    });

    // Dark mode
    const toggle = document.getElementById('darkModeToggle');
    const html   = document.documentElement;
    toggle.textContent = html.getAttribute('data-bs-theme') === 'dark' ? '🌙' : '☀️';
    toggle.addEventListener('click', () => {
        const t = html.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
        html.setAttribute('data-bs-theme', t);
        localStorage.setItem('theme', t);
        toggle.textContent = t === 'dark' ? '🌙' : '☀️';
    });
    </script>
</body>
</html>

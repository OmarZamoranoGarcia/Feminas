<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - DevMart</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="ms-auto d-flex align-items-center flex-wrap gap-2">
                    <a class="nav-link" href="{{ route('home') }}">Inicio</a>
                    <a class="btn btn-ghost" href="{{ route('login') }}">Iniciar Sesión</a>
                    <button class="btn btn-link" id="darkModeToggle" style="font-size:1.5rem">☀️</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-5 py-4">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card glass shadow border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h3 class="mb-0">Únete a DevMart</h3>
                    </div>
                    <div class="card-body p-4">

                        <p class="text-muted small text-center mb-4">
                            Con una sola cuenta puedes <strong>comprar y vender</strong> en DevMart.
                        </p>

                        <div id="errorAlert" class="alert alert-danger d-none" role="alert"></div>

                        <form id="registerForm">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label fw-medium">Nombre Completo</label>
                                <input type="text" name="name" class="form-control" id="name"
                                       placeholder="Tu nombre completo" required autocomplete="name">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-medium">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" id="email"
                                       placeholder="nombre@ejemplo.com" required autocomplete="email">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-medium">Contraseña</label>
                                <input type="password" name="password" class="form-control" id="password"
                                       required autocomplete="new-password" minlength="8">
                                <div class="form-text">Mínimo 8 caracteres.</div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-medium">Confirmar Contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control"
                                       id="password_confirmation" required autocomplete="new-password">
                            </div>

                            {{-- Optional seller profile — collapsible --}}
                            <div class="mb-4">
                                <button class="btn btn-link p-0 text-decoration-none text-muted small"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#sellerProfileCollapse"
                                        aria-expanded="false">
                                    <i class="bi bi-shop me-1"></i>
                                    Añadir perfil de vendedor <span class="text-muted">(opcional)</span>
                                    <i class="bi bi-chevron-down ms-1"></i>
                                </button>
                                <div class="collapse mt-3" id="sellerProfileCollapse">
                                    <div class="p-3 rounded-3 border" style="background:var(--surface-soft)">
                                        <div class="mb-3">
                                            <label for="razon_social" class="form-label fw-medium">Nombre de Tienda / Razón Social</label>
                                            <input type="text" name="razon_social" class="form-control" id="razon_social"
                                                   placeholder="Nombre que verán tus compradores">
                                            <div class="form-text">Si lo dejas vacío, usaremos tu nombre completo.</div>
                                        </div>
                                        <div class="mb-0">
                                            <label for="rfc" class="form-label fw-medium">RFC <span class="text-muted fw-normal">(opcional)</span></label>
                                            <input type="text" name="rfc" class="form-control" id="rfc"
                                                   placeholder="RFC000000AAA" maxlength="13">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill" id="submitBtn">
                                    <span id="submitLabel">
                                        <i class="bi bi-person-plus me-1"></i>Crear cuenta
                                    </span>
                                    <span id="submitSpinner" class="d-none">
                                        <span class="spinner-border spinner-border-sm me-1"></span>Creando...
                                    </span>
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-2 text-muted small">
                                ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
                            </p>
                            <a href="{{ route('home') }}" class="text-decoration-none small">← Volver al inicio</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 mt-auto">
        <div class="container">
            <p class="mb-0 text-secondary small">&copy; {{ date('Y') }} DevMart. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn     = document.getElementById('submitBtn');
            const submitLabel   = document.getElementById('submitLabel');
            const submitSpinner = document.getElementById('submitSpinner');
            const errorAlert    = document.getElementById('errorAlert');

            submitBtn.disabled = true;
            submitLabel.classList.add('d-none');
            submitSpinner.classList.remove('d-none');
            errorAlert.classList.add('d-none');

            const form = e.target;
            const body = {
                name:                  form.name.value.trim(),
                email:                 form.email.value.trim(),
                password:              form.password.value,
                password_confirmation: form.password_confirmation.value,
                razon_social:          form.razon_social.value.trim() || null,
                rfc:                   form.rfc.value.trim() || null,
            };

            try {
                const res  = await fetch("{{ route('register.store') }}", {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(body),
                });
                const data = await res.json();

                if (data.success) {
                    window.location.href = "{{ route('login') }}";
                } else {
                    errorAlert.textContent = data.message ?? 'Error en el registro.';
                    errorAlert.classList.remove('d-none');
                    submitBtn.disabled = false;
                    submitLabel.classList.remove('d-none');
                    submitSpinner.classList.add('d-none');
                }
            } catch {
                errorAlert.textContent = 'Error de conexión con el servidor.';
                errorAlert.classList.remove('d-none');
                submitBtn.disabled = false;
                submitLabel.classList.remove('d-none');
                submitSpinner.classList.add('d-none');
            }
        });

        const toggle = document.getElementById('darkModeToggle');
        const html   = document.documentElement;
        toggle.textContent = html.getAttribute('data-bs-theme') === 'dark' ? '🌙' : '☀️';
        toggle.addEventListener('click', () => {
            const t = html.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-bs-theme', t);
            localStorage.setItem('theme', t);
            toggle.textContent = t === 'dark' ? '🌙' : '☀️';
        });
    });
    </script>
</body>
</html>

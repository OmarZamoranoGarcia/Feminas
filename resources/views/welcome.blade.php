<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevMart - Tu Marketplace de Desarrollo</title>
    
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tu CSS Modularizado -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .hero-section {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background-color: var(--bs-body-tertiary-bg);
            color: var(--bs-body-color);
        }
        .feature-card {
            background-color: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 0.5rem;
            padding: 1.5rem;
            transition: transform 0.2s ease-in-out;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .img-placeholder {
            width: 100%;
            height: 200px;
            background-color: var(--bs-tertiary-bg);
            border: 1px dashed var(--bs-border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bs-secondary-color);
            font-size: 1.2rem;
            margin-bottom: 1rem;
            border-radius: 0.3rem;
        }
        .dark-mode-toggle {
            cursor: pointer;
            font-size: 1.5rem;
        }
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="{{ route('home') }}">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Vender</a></li>
                    <li class="nav-item"><a class="nav-link fw-bold text-primary" href="{{ route('login') }}">Iniciar Sesión</a></li>
                    <li class="nav-item"><a class="nav-link text-danger fw-bold" href="{{ route('admin') }}">Admin</a></li>
                    <li class="nav-item ms-3">
                        <button class="btn btn-link nav-link dark-mode-toggle" id="darkModeToggle" aria-label="Toggle dark mode">☀️</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero-section py-5">
        <div class="container">
            <h1 class="display-3 fw-bold mb-4">Encuentra y Vende Soluciones de Desarrollo</h1>
            <p class="lead mb-5">DevMart es el marketplace definitivo para desarrolladores. Descubre herramientas, componentes y servicios que impulsarán tus proyectos.</p>
            <a href="#" class="btn btn-primary btn-lg me-3">Explorar Productos</a>
            <a href="#" class="btn btn-outline-secondary btn-lg">Vender en DevMart</a>
            <div class="mt-4">
                <a href="{{ route('login') }}" class="text-decoration-none fw-bold">¿Ya tienes cuenta? Inicia sesión aquí</a>
            </div>
        </div>
    </header>

    <section class="container my-5 py-5">
        <h2 class="text-center mb-5 display-5 fw-bold">¿Por qué DevMart?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="img-placeholder">Imagen Característica 1</div>
                    <h3 class="h5 fw-bold">Amplia Selección</h3>
                    <p>Desde APIs hasta plantillas de UI, encuentra todo lo que necesitas en un solo lugar.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="img-placeholder">Imagen Característica 2</div>
                    <h3 class="h5 fw-bold">Calidad Verificada</h3>
                    <p>Productos y servicios revisados por la comunidad para asegurar la excelencia.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="img-placeholder">Imagen Característica 3</div>
                    <h3 class="h5 fw-bold">Comunidad Activa</h3>
                    <p>Conecta con otros desarrolladores, comparte conocimientos y crece profesionalmente.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-body-tertiary text-center py-4 mt-5 border-top">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} DevMart. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
@extends('app')

@section('title', 'DevMart - Tu Marketplace de Desarrollo')

@push('styles')
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
            background-color: var(--bs-body-bg);
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
@endpush

@section('content')

    <header class="hero-section bg-body-tertiary py-5">
        <div class="container">
            <h1 class="display-3 fw-bold mb-4">Encuentra y Vende Soluciones de Desarrollo</h1>
            <p class="lead mb-5">DevMart es el marketplace definitivo para desarrolladores. Descubre herramientas, componentes y servicios que impulsarán tus proyectos.</p>
            <a href="#" class="btn btn-primary btn-lg me-3">Explorar Productos</a>
            <a href="#" class="btn btn-outline-secondary btn-lg">Vender en DevMart</a>
            <div class="mt-4">
                <a href="/login" class="text-decoration-none fw-bold">¿Ya tienes cuenta? Inicia sesión aquí</a>
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

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButton = document.getElementById('darkModeToggle');
            const htmlElement = document.documentElement;

            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                htmlElement.setAttribute('data-bs-theme', savedTheme);
                toggleButton.innerHTML = savedTheme === 'dark' ? '🌙' : '☀️';
            }

            toggleButton.addEventListener('click', () => {
                let currentTheme = htmlElement.getAttribute('data-bs-theme');
                let newTheme = currentTheme === 'light' ? 'dark' : 'light';
                htmlElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                toggleButton.innerHTML = newTheme === 'dark' ? '🌙' : '☀️';
            });
        });
    </script>
@endpush
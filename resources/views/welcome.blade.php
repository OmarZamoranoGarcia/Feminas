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
                    <li class="nav-item" id="registerNavItem">
                        <a class="nav-link" href="{{ route('register') }}">Registrarse</a>
                    </li>
                    <li class="nav-item" id="loginNavItem">
                        <a class="nav-link fw-bold text-primary" href="{{ route('login') }}">Iniciar Sesión</a>
                    </li>
                    <li class="nav-item d-none" id="userNavItem">
                        <button class="btn btn-outline-primary ms-lg-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#userPanel">👤 Mi Panel</button>
                    </li>
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
                <div id="authLinksHero">
                    <a href="{{ route('login') }}" class="text-decoration-none fw-bold me-3">¿Ya tienes cuenta? Inicia sesión</a>
                    <a href="{{ route('register') }}" class="text-decoration-none fw-bold">Crear una cuenta</a>
                </div>
            </div>
        </div>
    </header>

    <section class="container my-5 py-4">
        <div class="row mb-5 justify-content-center">
            <div class="col-md-8 text-center">
                <h2 class="display-6 fw-bold mb-4">Catálogo de Soluciones</h2>
                <div class="input-group input-group-lg shadow-sm">
                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre (ej: API, React...)" aria-label="Buscador">
                    <button class="btn btn-primary" id="btnSearch" type="button">Buscar</button>
                </div>
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-secondary filter-btn active" data-category="">Todos</button>
                    <button class="btn btn-sm btn-outline-secondary filter-btn" data-category="backend">Backend</button>
                    <button class="btn btn-sm btn-outline-secondary filter-btn" data-category="frontend">Frontend</button>
                </div>
            </div>
        </div>

        <!-- Grid de Productos dinámico -->
        <div class="row g-4" id="productGrid">
            <!-- Se cargan vía API -->
        </div>

        <!-- Spinner para carga perezosa -->
        <div id="loader" class="text-center my-5 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    </section>

    <!-- Panel de Usuario / Carrito (Offcanvas) -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="userPanel" aria-labelledby="userPanelLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="userPanelLabel">👤 Mi Cuenta</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mb-4">
                <h6 class="fw-bold mb-3">🛒 Mi Carrito</h6>
                <ul id="cartList" class="list-group list-group-flush mb-3">
                    <!-- Se llena con JS -->
                </ul>
                <div class="d-grid">
                    <button class="btn btn-primary" id="checkoutBtn">Proceder al Checkout</button>
                </div>
            </div>
            <hr>
            <div class="d-grid mt-auto">
                <button class="btn btn-outline-danger btn-sm" id="logoutBtn">Cerrar Sesión</button>
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
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButton = document.getElementById('darkModeToggle');
            const loginNavItem = document.getElementById('loginNavItem');
            const registerNavItem = document.getElementById('registerNavItem');
            const authLinksHero = document.getElementById('authLinksHero');
            const userNavItem = document.getElementById('userNavItem');
            const cartList = document.getElementById('cartList');
            const logoutBtn = document.getElementById('logoutBtn');
            const productGrid = document.getElementById('productGrid');
            const searchInput = document.getElementById('searchInput');
            const btnSearch = document.getElementById('btnSearch');
            const loader = document.getElementById('loader');

            let currentCategory = '';

            // --- Función para cargar productos desde la API ---
            async function fetchProducts() {
                loader.classList.remove('d-none');
                const query = searchInput.value;
                try {
                    const response = await fetch(`/api/products?search=${query}&category=${currentCategory}`);
                    const products = await response.json();
                    renderProducts(products);
                } catch (e) { console.error("Error cargando productos", e); }
                loader.classList.add('d-none');
            }

            function renderProducts(products) {
                if(products.length === 0) {
                    productGrid.innerHTML = '<div class="col-12 text-center text-muted">No se encontraron productos</div>';
                    return;
                }
                productGrid.innerHTML = products.map(p => `
                    <div class="col-md-4">
                        <div class="feature-card h-100">
                            <img src="${p.img}" class="img-fluid rounded mb-3" alt="${p.name}">
                            <h4 class="h5 fw-bold">${p.name}</h4>
                            <p class="text-muted small">${p.category.toUpperCase()}</p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="fw-bold fs-5">$${p.price}</span>
                                <button class="btn btn-sm btn-primary">Añadir</button>
                            </div>
                        </div>
                    </div>
                `).join('');
            }

            // Eventos de Búsqueda y Filtro
            btnSearch.addEventListener('click', fetchProducts);
            searchInput.addEventListener('keypress', (e) => { if(e.key === 'Enter') fetchProducts(); });
            
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    currentCategory = btn.dataset.category;
                    fetchProducts();
                });
            });

            // Inicializar carga
            fetchProducts();

            // --- Lógica de Sesión y Panel ---
            const isLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
            
            if (isLoggedIn) {
                loginNavItem.classList.add('d-none');
                registerNavItem.classList.add('d-none');
                if (authLinksHero) authLinksHero.classList.add('d-none');
                userNavItem.classList.remove('d-none');
                
                // Cargar Carrito
                const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                if (cart.length === 0) {
                    cartList.innerHTML = '<li class="list-group-item text-center text-muted">El carrito está vacío</li>';
                    document.getElementById('checkoutBtn').disabled = true;
                } else {
                    cartList.innerHTML = cart.map(item => `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ${item.name}
                            <span class="badge bg-primary rounded-pill">$${item.price}</span>
                        </li>
                    `).join('');
                }
            }

            logoutBtn.addEventListener('click', () => {
                localStorage.removeItem('userLoggedIn');
                window.location.reload();
            });

            // --- Lógica de Dark Mode ---
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
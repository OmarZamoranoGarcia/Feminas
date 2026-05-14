<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevMart - Tu Marketplace de Desarrollo</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.auth-sync')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <style>
        body { transition: background-color 0.35s ease, color 0.35s ease; }
        .dark-mode-toggle { cursor: pointer; font-size: 1.5rem; }
        .product-card-img { width: 100%; height: 180px; object-fit: cover; border-radius: .5rem; }
        .btn-add-cart { transition: transform .15s ease; }
        .btn-add-cart:active { transform: scale(.93); }
        .cart-badge {
            position: absolute; top: -6px; right: -6px;
            background: #dc3545; color: #fff;
            border-radius: 999px; font-size: .65rem;
            min-width: 18px; height: 18px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
        }
    </style>

    <script>
        (function() {
            const t = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', t);
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
                    <a id="registerNavItem" class="btn btn-ghost" href="{{ route('register') }}">Registrarse</a>
                    <a id="loginNavItem"    class="btn btn-primary" href="{{ route('login') }}">Iniciar Sesión</a>
                    <a id="userNavItem"     class="btn btn-outline-primary btn-sm d-none" href="{{ route('admin') }}">Mi Panel</a>
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('admin') }}">Admin</a>

                    <!-- Cart trigger button -->
                    <button class="btn btn-ghost position-relative" type="button"
                            data-bs-toggle="offcanvas" data-bs-target="#userPanel"
                            id="cartToggleBtn">
                        <i class="bi bi-cart3 fs-5"></i>
                        <span class="cart-badge d-none" id="cartBadge">0</span>
                    </button>

                    <button class="btn btn-link dark-mode-toggle" id="darkModeToggle" aria-label="Toggle dark mode">☀️</button>
                </div>
            </div>
        </div>
    </nav>

    <header class="hero-section py-5">
        <div class="container">
            <div class="hero-panel mx-auto text-center">
                <h1 class="fw-bold mb-4">Encuentra y Vende Productos en DevMart</h1>
                <p class="lead mb-5">DevMart es el marketplace definitivo para ti. Descubre una gran variedad de productos.</p>
                <div class="hero-cta-group justify-content-center" id="authLinksHero">
                    <a href="#productSection" class="btn btn-primary btn-lg">Explorar Productos</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-lg">Vender en DevMart</a>
                </div>
            </div>
        </div>
    </header>

    <section class="container my-5 py-4" id="productSection">
        <div class="row mb-5 justify-content-center">
            <div class="col-md-8 text-center">
                <h2 class="display-6 fw-bold mb-4">Catálogo de Productos</h2>
                <div class="input-group input-group-lg shadow-sm">
                    <input type="text" id="searchInput" class="form-control"
                           placeholder="Buscar por nombre (ej: API, React...)" aria-label="Buscador">
                    <button class="btn btn-primary" id="btnSearch" type="button">Buscar</button>
                </div>
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-secondary filter-btn active" data-category="">Todos</button>
                    <button class="btn btn-sm btn-outline-secondary filter-btn" data-category="backend">Backend</button>
                    <button class="btn btn-sm btn-outline-secondary filter-btn" data-category="frontend">Frontend</button>
                </div>
            </div>
        </div>

        <div class="row g-4" id="productGrid"></div>

        <div id="loader" class="text-center my-5 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    </section>

    <!-- Cart / User Panel  -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="userPanel" aria-labelledby="userPanelLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold" id="userPanelLabel">
                <i class="bi bi-cart3 me-2 text-primary"></i>Mi Carrito
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <ul id="cartList" class="list-group list-group-flush mb-3 flex-grow-1"></ul>

            <div class="border-top pt-3">
                <div class="d-flex justify-content-between fw-bold mb-3">
                    <span>Total</span>
                    <span id="cartTotal">$0.00</span>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary rounded-pill" id="checkoutBtn" disabled>
                        <i class="bi bi-credit-card me-1"></i>Proceder al Checkout
                    </button>
                    <button class="btn btn-outline-danger btn-sm rounded-pill" id="logoutBtn">
                        <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999">
        <div id="cartToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive">
            <div class="d-flex">
                <div class="toast-body" id="toastMsg">Producto añadido al carrito</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
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
        // Constants & state
        const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const isLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
        const userId    = localStorage.getItem('userId') ?? null;

        // Anonymous session token – persists across page loads
        let sessionToken = localStorage.getItem('sessionToken');
        if (!sessionToken) {
            sessionToken = crypto.randomUUID();
            localStorage.setItem('sessionToken', sessionToken);
        }

        // Cart identity params
        function cartParams() {
            return userId
                ? `user_id=${encodeURIComponent(userId)}`
                : `session_token=${encodeURIComponent(sessionToken)}`;
        }
        function cartBody(extra = {}) {
            const base = userId
                ? { user_id: userId }
                : { session_token: sessionToken };
            return { ...base, ...extra };
        }

        // UI refs
        const loginNavItem    = document.getElementById('loginNavItem');
        const registerNavItem = document.getElementById('registerNavItem');
        const userNavItem     = document.getElementById('userNavItem');
        const productGrid     = document.getElementById('productGrid');
        const searchInput     = document.getElementById('searchInput');
        const btnSearch       = document.getElementById('btnSearch');
        const loader          = document.getElementById('loader');
        const cartList        = document.getElementById('cartList');
        const cartBadge       = document.getElementById('cartBadge');
        const cartTotalEl     = document.getElementById('cartTotal');
        const checkoutBtn     = document.getElementById('checkoutBtn');
        const logoutBtn       = document.getElementById('logoutBtn');
        const toast           = new bootstrap.Toast(document.getElementById('cartToast'), { delay: 2200 });
        const toastMsg        = document.getElementById('toastMsg');

        let currentCategory = '';

        // Auth UI
        if (isLoggedIn) {
            loginNavItem.classList.add('d-none');
            registerNavItem.classList.add('d-none');
            userNavItem.classList.remove('d-none');
        }

        logoutBtn.addEventListener('click', () => {
            localStorage.removeItem('userLoggedIn');
            localStorage.removeItem('userId');
            window.location.reload();
        });

        // Product fetch & render
        async function fetchProducts() {
            loader.classList.remove('d-none');
            productGrid.innerHTML = '';
            try {
                const url = `/api/products?search=${encodeURIComponent(searchInput.value)}&category=${encodeURIComponent(currentCategory)}`;
                const res = await fetch(url);
                const products = await res.json();
                renderProducts(products);
            } catch (e) {
                productGrid.innerHTML = '<div class="col-12 text-center text-danger">Error cargando productos.</div>';
            }
            loader.classList.add('d-none');
        }

        function renderProducts(products) {
            if (!products.length) {
                productGrid.innerHTML = '<div class="col-12 text-center text-muted py-5"><i class="bi bi-inbox fs-2 d-block mb-2"></i>No se encontraron productos.</div>';
                return;
            }
            productGrid.innerHTML = products.map(p => `
                <div class="col-md-4">
                    <div class="feature-card h-100 d-flex flex-column">
                        <img src="${p.img}" class="product-card-img mb-3" alt="${p.name}">
                        <p class="text-muted small mb-1 text-uppercase fw-semibold">${p.category}</p>
                        <h4 class="h5 fw-bold flex-grow-1">${p.name}</h4>
                        <p class="text-muted small mb-2">${p.seller}</p>
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                            <span class="fw-bold fs-5">$${p.price}</span>
                            <button
                                class="btn btn-sm btn-primary btn-add-cart"
                                data-product-id="${p.id}"
                                data-product-name="${p.name}"
                                data-product-price="${p.price}"
                                ${p.stock === 0 ? 'disabled' : ''}
                            >
                                ${p.stock === 0
                                    ? '<i class="bi bi-x-circle me-1"></i>Agotado'
                                    : '<i class="bi bi-cart-plus me-1"></i>Añadir'}
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Add to cart
        productGrid.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-add-cart');
            if (!btn) return;

            const productId   = btn.dataset.productId;
            const productName = btn.dataset.productName;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            try {
                const res = await fetch('/api/cart', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify(cartBody({ product_id: productId, qty: 1 })),
                });

                if (res.ok) {
                    toastMsg.textContent = `"${productName}" añadido al carrito`;
                    toast.show();
                    await loadCart();
                } else {
                    const err = await res.json();
                    alert('Error: ' + (err.message ?? 'No se pudo añadir'));
                }
            } catch {
                alert('Error de conexión.');
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Añadir';
        });

        // Load & render cart
        async function loadCart() {
            try {
                const res = await fetch(`/api/cart?${cartParams()}`);
                const items = await res.json();
                renderCart(items);
            } catch {
                cartList.innerHTML = '<li class="list-group-item text-danger">Error cargando carrito.</li>';
            }
        }

        function renderCart(items) {
            const count = items.reduce((s, i) => s + i.qty, 0);
            const total = items.reduce((s, i) => s + (parseFloat(i.product.price) * i.qty), 0);

            // Badge
            cartBadge.textContent = count;
            cartBadge.classList.toggle('d-none', count === 0);

            // Total & checkout
            cartTotalEl.textContent = '$' + total.toFixed(2);
            checkoutBtn.disabled = count === 0;

            if (!items.length) {
                cartList.innerHTML = '<li class="list-group-item text-center text-muted py-4"><i class="bi bi-cart-x fs-2 d-block mb-1"></i>Tu carrito está vacío</li>';
                return;
            }

            cartList.innerHTML = items.map(item => `
                <li class="list-group-item px-0 d-flex align-items-center gap-2">
                    <img src="${item.product.img}" width="46" height="46"
                         style="object-fit:cover;border-radius:.4rem" alt="${item.product.name}">
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold text-truncate">${item.product.name}</div>
                        <small class="text-muted">×${item.qty} — $${(parseFloat(item.product.price) * item.qty).toFixed(2)}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-danger btn-remove-cart flex-shrink-0"
                            data-cart-id="${item.cart_id}" title="Eliminar">
                        <i class="bi bi-trash pointer-events-none"></i>
                    </button>
                </li>
            `).join('');
        }

        // Remove from cart
        document.getElementById('cartList').addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-remove-cart');
            if (!btn) return;

            btn.disabled = true;
            const cartId = btn.dataset.cartId;

            try {
                const res = await fetch(`/api/cart/${cartId}?${cartParams()}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                });
                if (res.ok) await loadCart();
            } catch {
                alert('Error al eliminar del carrito.');
                btn.disabled = false;
            }
        });

        // Search & filter
        btnSearch.addEventListener('click', fetchProducts);
        searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') fetchProducts(); });

        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentCategory = btn.dataset.category;
                fetchProducts();
            });
        });

        // Dark mode
        const toggleButton = document.getElementById('darkModeToggle');
        const html = document.documentElement;
        toggleButton.innerHTML = html.getAttribute('data-bs-theme') === 'dark' ? '🌙' : '☀️';
        toggleButton.addEventListener('click', () => {
            const newTheme = html.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            toggleButton.innerHTML = newTheme === 'dark' ? '🌙' : '☀️';
        });

        // Init
        fetchProducts();
        loadCart();
    });
    </script>
</body>
</html>

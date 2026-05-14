<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevMart - Tu Marketplace de Desarrollo</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <style>
        body { transition: background-color 0.35s ease, color 0.35s ease; }
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
        .nav-auth { visibility: hidden; }
    </style>

    {{-- Apply saved theme immediately to avoid flash --}}
    <script>
        (function() {
            var t = localStorage.getItem('theme') || 'light';
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
                <div class="ms-auto d-flex align-items-center flex-wrap gap-2 nav-actions nav-auth">

                    {{-- Logged OUT --}}
                    <a id="registerNavItem" class="btn btn-ghost" href="{{ route('register') }}">Registrarse</a>
                    <a id="loginNavItem"    class="btn btn-primary" href="{{ route('login') }}">Iniciar Sesión</a>

                    {{-- Logged IN: vendor or admin only --}}
                    <a id="adminNavItem" class="btn btn-outline-primary btn-sm d-none" href="{{ route('admin') }}">
                        <i class="bi bi-grid me-1"></i>Mi Panel
                    </a>

                    {{-- Logged IN: shown for all authenticated users --}}
                    <span id="userGreeting" class="text-muted small d-none"></span>
                    <button class="btn btn-outline-danger btn-sm d-none" id="logoutNavItem" type="button">
                        <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                    </button>

                    <!-- Cart trigger -->
                    <button class="btn btn-ghost position-relative" type="button"
                            data-bs-toggle="offcanvas" data-bs-target="#cartDrawer"
                            id="cartToggleBtn">
                        <i class="bi bi-cart3 fs-5"></i>
                        <span class="cart-badge d-none" id="cartBadge">0</span>
                    </button>

                    {{-- Dark mode toggle --}}
                    <button class="btn btn-ghost" id="darkModeToggle"
                            style="cursor:pointer;font-size:1.2rem;border:none;background:none;padding:0.5rem;"
                            aria-label="Cambiar tema"></button>
                </div>
            </div>
        </div>
    </nav>

    <header class="hero-section py-5">
        <div class="container">
            <div class="hero-panel mx-auto text-center">
                <h1 class="fw-bold mb-4">Encuentra y Vende Productos en DevMart</h1>
                <p class="lead mb-5">DevMart es el marketplace definitivo para ti. Descubre una gran variedad de productos.</p>
                <div class="hero-cta-group justify-content-center">
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

    <!-- Toast notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999">
        <div id="cartToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive">
            <div class="d-flex">
                <div class="toast-body" id="toastMsg">Producto añadido al carrito</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <!-- Cart Drawer -->
    @include('cart-drawer')

    <footer class="bg-body-tertiary text-center py-4 mt-5 border-top">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} DevMart. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
        var html   = document.documentElement;
        var toggle = document.getElementById('darkModeToggle');
        if (!toggle) return;

        function applyTheme(theme) {
            html.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            toggle.textContent = theme === 'dark' ? '🌙' : '☀️';
        }

        applyTheme(html.getAttribute('data-bs-theme') || 'light');

        toggle.addEventListener('click', function () {
            var next = html.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
            applyTheme(next);
        });
    })();

    document.addEventListener('DOMContentLoaded', async () => {
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        // ── 1. Auth check ──────────────────────────────────────────────────────
        let currentUser = null;
        try {
            const meRes  = await fetch('/api/me', { credentials: 'same-origin' });
            const meData = await meRes.json();
            if (meData.success) currentUser = meData.user;
        } catch (err) {
            console.warn('Could not reach /api/me:', err.message);
        }

        const loginNavItem    = document.getElementById('loginNavItem');
        const registerNavItem = document.getElementById('registerNavItem');
        const adminNavItem    = document.getElementById('adminNavItem');
        const userGreeting    = document.getElementById('userGreeting');
        const logoutNavItem   = document.getElementById('logoutNavItem');

        if (currentUser) {
            loginNavItem.classList.add('d-none');
            registerNavItem.classList.add('d-none');
            logoutNavItem.classList.remove('d-none');
            userGreeting.textContent = `Hola, ${currentUser.name}`;
            userGreeting.classList.remove('d-none');

            if (currentUser.tipo === 'vendedor' || currentUser.tipo === 'admin') {
                adminNavItem.classList.remove('d-none');
            }

            localStorage.setItem('vendorId', currentUser.vendor_id ?? '');
            localStorage.setItem('userTipo', currentUser.tipo);
        }

        document.querySelector('.nav-auth').style.visibility = 'visible';

        logoutNavItem?.addEventListener('click', async () => {
            logoutNavItem.disabled = true;
            logoutNavItem.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saliendo...';
            try {
                await fetch('/api/logout', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                });
            } catch { /* best-effort */ }
            localStorage.removeItem('vendorId');
            localStorage.removeItem('userTipo');
            window.location.href = "{{ route('home') }}";
        });

        // ── 2. Cart identity helpers (exposed globally for cart-drawer) ────────
        const userId = currentUser ? currentUser.id : null;

        let sessionToken = localStorage.getItem('sessionToken');
        if (!sessionToken) {
            sessionToken = typeof crypto !== 'undefined' && crypto.randomUUID
                ? crypto.randomUUID()
                : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
                    const r = Math.random() * 16 | 0;
                    return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
                  });
            localStorage.setItem('sessionToken', sessionToken);
        }

        // Exposed so cart-drawer.blade.php can build DELETE query strings
        window.cartParams = function () {
            return userId
                ? `user_id=${encodeURIComponent(userId)}`
                : `session_token=${encodeURIComponent(sessionToken)}`;
        };

        function cartBody(extra = {}) {
            const base = userId ? { user_id: userId } : { session_token: sessionToken };
            return { ...base, ...extra };
        }

        // ── 3. UI refs ─────────────────────────────────────────────────────────
        const productGrid = document.getElementById('productGrid');
        const searchInput = document.getElementById('searchInput');
        const btnSearch   = document.getElementById('btnSearch');
        const loader      = document.getElementById('loader');
        const cartBadge   = document.getElementById('cartBadge');
        const toastMsg    = document.getElementById('toastMsg');

        function showToast(msg) {
            try {
                toastMsg.textContent = msg;
                bootstrap.Toast.getOrCreateInstance(
                    document.getElementById('cartToast'), { delay: 2200 }
                ).show();
            } catch (e) {
                console.warn('Toast error:', e);
            }
        }

        let currentCategory = '';

        // ── 4. Products ────────────────────────────────────────────────────────
        async function fetchProducts() {
            loader.classList.remove('d-none');
            productGrid.innerHTML = '';
            try {
                const url = `/api/products?search=${encodeURIComponent(searchInput.value)}&category=${encodeURIComponent(currentCategory)}`;
                const res = await fetch(url);
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                renderProducts(await res.json());
            } catch (err) {
                productGrid.innerHTML = `<div class="col-12 text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle fs-2 d-block mb-2"></i>
                    Error cargando productos: ${err.message}<br>
                    <small class="text-muted">Comprueba que el servidor esté corriendo en <code>http://localhost:8000</code></small>
                </div>`;
            }
            loader.classList.add('d-none');
        }

        function renderProducts(products) {
            if (!products.length) {
                productGrid.innerHTML = '<div class="col-12 text-center text-muted py-5"><i class="bi bi-inbox fs-2 d-block mb-2"></i>No se encontraron productos.</div>';
                return;
            }

            const canManage = currentUser &&
                (currentUser.tipo === 'vendedor' || currentUser.tipo === 'admin');

            productGrid.innerHTML = products.map(p => `
                <div class="col-md-4">
                    <div class="feature-card h-100 d-flex flex-column">
                        <img src="${p.img}" class="product-card-img mb-3" alt="${p.name}">
                        <p class="text-muted small mb-1 text-uppercase fw-semibold">${p.category}</p>
                        <h4 class="h5 fw-bold flex-grow-1">${p.name}</h4>
                        <p class="text-muted small mb-2">${p.seller}</p>
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top gap-2">
                            <span class="fw-bold fs-5">$${p.price}</span>
                            <div class="d-flex gap-1">
                                ${canManage ? `
                                    <a href="{{ route('admin') }}" class="btn btn-sm btn-outline-secondary" title="Gestionar en panel">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>` : ''}
                                <button
                                    class="btn btn-sm btn-primary btn-add-cart"
                                    data-product-id="${p.id}"
                                    data-product-name="${p.name}"
                                    ${p.stock === 0 ? 'disabled' : ''}
                                >
                                    ${p.stock === 0
                                        ? '<i class="bi bi-x-circle me-1"></i>Agotado'
                                        : '<i class="bi bi-cart-plus me-1"></i>Añadir'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

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
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify(cartBody({ product_id: productId, qty: 1 })),
                });

                if (res.ok) {
                    showToast(`"${productName}" añadido al carrito`);
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

        // ── 5. Cart (exposed globally so cart-drawer can trigger a reload) ─────
        async function loadCart() {
            try {
                const res   = await fetch(`/api/cart?${window.cartParams()}`, { credentials: 'same-origin' });
                const items = await res.json();
                updateBadge(items);
                // Sync the drawer (uses syncFromApi, not the old in-memory approach)
                if (window.cartDrawer) window.cartDrawer.syncFromApi(items);
            } catch {
                console.warn('Error cargando carrito.');
            }
        }

        // Exposed so cart-drawer can call it after a DELETE
        window.reloadCart = loadCart;

        function updateBadge(items) {
            const count = items.reduce((s, i) => s + i.qty, 0);
            cartBadge.textContent = count;
            cartBadge.classList.toggle('d-none', count === 0);
        }

        // ── 6. Search & filters ────────────────────────────────────────────────
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

        // ── Init ───────────────────────────────────────────────────────────────
        fetchProducts();
        loadCart();
    });
    </script>
</body>
</html>
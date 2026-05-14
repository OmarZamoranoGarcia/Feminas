<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - DevMart</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* Tipografía más limpia */
        body { 
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            transition: background 0.3s ease, color 0.3s ease; 
            /* Degradado base para modo claro */
            background: linear-gradient(135deg, #f6f8fd 0%, #f1f5f9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Degradado para modo oscuro */
        [data-bs-theme="dark"] body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        /* Navbar con efecto cristal (Glassmorphism) */
        .navbar {
            backdrop-filter: blur(12px);
            background-color: rgba(var(--bs-body-bg-rgb), 0.85) !important;
            border-bottom: 1px solid var(--bs-border-color-translucent);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
        }

        .dark-mode-toggle { cursor: pointer; font-size: 1.3rem; transition: transform 0.2s; }
        .dark-mode-toggle:hover { transform: scale(1.1); }
        
        .admin-container { flex: 1; }

        /* Tarjeta principal que envuelve el contenido */
        .admin-card {
            background-color: var(--bs-body-bg);
            border-radius: 1.25rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            border: 1px solid var(--bs-border-color-translucent);
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .admin-card {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(255,255,255,0.05);
        }

        /* Ajustes de la tabla */
        .table-responsive {
            border-radius: 0.75rem;
            overflow: hidden;
            border: 1px solid var(--bs-border-color-translucent);
        }
        
        .table { margin-bottom: 0; }
        .product-card img { max-height: 60px; object-fit: cover; border-radius: 0.5rem; }

        /* Botón de nuevo producto con gradiente */
        .btn-gradient {
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
            border: none;
            color: white;
            transition: opacity 0.2s, transform 0.2s;
        }
        .btn-gradient:hover {
            opacity: 0.9;
            color: white;
            transform: translateY(-2px);
        }

        /* Estilo para modales más redondeados */
        .modal-content {
            border-radius: 1.25rem;
            border: none;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
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
    <nav class="navbar navbar-expand-lg sticky-top py-3">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4 d-flex align-items-center gap-2" href="{{ route('home') }}">
                <i class="bi bi-braces text-primary"></i> DevMart 
                <span class="badge bg-primary fs-6 rounded-pill">Mi Panel</span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="ms-auto d-flex align-items-center flex-wrap gap-3 nav-actions mt-3 mt-lg-0">
                    <a class="nav-link fw-medium" href="{{ route('home') }}"><i class="bi bi-house-door me-1"></i> Ver Sitio</a>
                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#userPanel">
                        <i class="bi bi-person-circle me-1"></i> Cuenta
                    </button>
                    <button class="btn btn-link dark-mode-toggle text-decoration-none p-0" id="darkModeToggle">☀️</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-5 admin-container">
        
        <div id="notLoggedAlert" class="alert alert-warning d-none shadow-sm rounded-4 border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> Debes iniciar sesión para ver y administrar tus productos. <a href="{{ route('login') }}" class="alert-link">Iniciar sesión</a> o <a href="{{ route('register') }}" class="alert-link">registrarte</a>.
        </div>

        <div class="admin-card">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
                <div>
                    <h1 class="mb-1 h3 fw-bold"><i class="bi bi-grid-1x2 me-2 text-primary"></i> Gestión de Productos</h1>
                    <p class="text-secondary mb-0">Crea, edita o elimina tus productos directamente desde tu panel de vendedor.</p>
                </div>
                <button class="btn btn-gradient rounded-pill px-4 shadow-sm" id="newProductButton">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo Producto
                </button>
            </div>

            <div class="table-responsive shadow-sm">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr class="text-secondary text-uppercase" style="font-size: 0.85rem;">
                            <th class="ps-3">Imagen</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <tr>
                            <td colspan="7" class="text-center text-primary py-5 fw-bold">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Cargando productos...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="productModalLabel">Agregar nuevo producto</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="productForm">
                    <div class="modal-body pt-4">
                        <input type="hidden" id="productId" value="">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="productName" class="form-label fw-medium">Nombre del Producto</label>
                                <input type="text" class="form-control rounded-3" id="productName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="productCategory" class="form-label fw-medium">Categoría</label>
                                <input type="text" class="form-control rounded-3" id="productCategory" placeholder="Backend, Frontend, UI...">
                            </div>
                            <div class="col-md-6">
                                <label for="productPrice" class="form-label fw-medium">Precio ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control rounded-3" id="productPrice" required>
                            </div>
                            <div class="col-md-6">
                                <label for="productStock" class="form-label fw-medium">Stock Disponible</label>
                                <input type="number" min="0" class="form-control rounded-3" id="productStock" required>
                            </div>
                            <div class="col-12">
                                <label for="productDescription" class="form-label fw-medium">Descripción</label>
                                <textarea class="form-control rounded-3" id="productDescription" rows="4"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="productStatus" class="form-label fw-medium">Estado</label>
                                <select class="form-select rounded-3" id="productStatus">
                                    <option value="activo">Activo</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="agotado">Agotado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="saveProductButton">Guardar producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end shadow" tabindex="-1" id="userPanel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold"><i class="bi bi-person-circle me-2 text-primary"></i> Mi Cuenta</h5>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <p class="text-secondary small mb-4">Accede a tus datos de vendedor y tu carrito de compras.</p>
            <div class="mb-4 bg-body-tertiary p-3 rounded-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-cart3 me-2"></i>Carrito</h6>
                <ul id="cartList" class="list-group list-group-flush bg-transparent mb-3"></ul>
                <div class="d-grid">
                    <button class="btn btn-primary rounded-pill shadow-sm" id="checkoutBtn">Proceder al Checkout</button>
                </div>
            </div>
            <hr class="mt-auto opacity-10">
            <div class="d-grid">
                <button class="btn btn-outline-danger rounded-pill" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</button>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 mt-auto">
        <div class="container">
            <p class="mb-0 text-secondary small">&copy; {{ date('Y') }} DevMart Admin. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButton = document.getElementById('darkModeToggle');
            const logoutBtn = document.getElementById('logoutBtn');
            const cartList = document.getElementById('cartList');
            const notLoggedAlert = document.getElementById('notLoggedAlert');
            const productTableBody = document.getElementById('productTableBody');
            const newProductButton = document.getElementById('newProductButton');
            const productModal = new bootstrap.Modal(document.getElementById('productModal'));
            const productForm = document.getElementById('productForm');
            const productIdInput = document.getElementById('productId');
            const productNameInput = document.getElementById('productName');
            const productDescriptionInput = document.getElementById('productDescription');
            const productPriceInput = document.getElementById('productPrice');
            const productStockInput = document.getElementById('productStock');
            const productCategoryInput = document.getElementById('productCategory');
            const productStatusInput = document.getElementById('productStatus');
            const saveProductButton = document.getElementById('saveProductButton');

            const vendorId = localStorage.getItem('vendorId') || 'vendedor-demo-001';
            if (!localStorage.getItem('vendorId')) {
                localStorage.setItem('vendorId', vendorId);
            }

            const isLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
            if (!isLoggedIn) {
                notLoggedAlert.classList.remove('d-none');
                productTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-lock fs-3 d-block mb-2"></i>Debes iniciar sesión para administrar productos.</td></tr>';
                newProductButton.disabled = true;
            }

            async function fetchProducts() {
                if (!isLoggedIn) return;

                const response = await fetch(`/api/products?vendor_id=${encodeURIComponent(vendorId)}`);
                const products = await response.json();
                renderProducts(products);
            }

            function renderProducts(products) {
                if (!products.length) {
                    productTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>No tienes productos publicados todavía.</td></tr>';
                    return;
                }

                productTableBody.innerHTML = products.map(product => `
                    <tr>
                        <td class="align-middle ps-3"><img src="${product.img || 'https://via.placeholder.com/60'}" class="img-thumbnail product-card shadow-sm" alt="${product.name}"></td>
                        <td class="align-middle">
                            <strong class="text-body">${product.name}</strong><br>
                            <small class="text-secondary">${product.description ? product.description.substring(0, 40) + '...' : 'Sin descripción'}</small>
                        </td>
                        <td class="align-middle fw-medium">$${product.price}</td>
                        <td class="align-middle">${product.stock}</td>
                        <td class="align-middle"><span class="badge text-bg-light border">${product.category || 'general'}</span></td>
                        <td class="align-middle"><span class="badge bg-${product.status === 'activo' ? 'success' : (product.status === 'agotado' ? 'danger' : 'warning')} text-white shadow-sm">${product.status}</span></td>
                        <td class="align-middle text-end pe-3">
                            <button class="btn btn-sm btn-light border me-1" data-action="edit" data-id="${product.id}" title="Editar"><i class="bi bi-pencil-square text-primary pointer-events-none"></i></button>
                            <button class="btn btn-sm btn-light border" data-action="delete" data-id="${product.id}" title="Eliminar"><i class="bi bi-trash text-danger pointer-events-none"></i></button>
                        </td>
                    </tr>
                `).join('');
            }

            function resetForm() {
                productIdInput.value = '';
                productNameInput.value = '';
                productDescriptionInput.value = '';
                productPriceInput.value = '';
                productStockInput.value = '';
                productCategoryInput.value = '';
                productStatusInput.value = 'activo';
                saveProductButton.innerHTML = '<i class="bi bi-check-lg me-1"></i> Guardar producto';
                document.getElementById('productModalLabel').textContent = 'Agregar nuevo producto';
            }

            newProductButton.addEventListener('click', () => {
                resetForm();
                productModal.show();
            });

            productForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const payload = {
                    vendor_id: vendorId,
                    name: productNameInput.value.trim(),
                    description: productDescriptionInput.value.trim(),
                    price: parseFloat(productPriceInput.value) || 0,
                    stock: parseInt(productStockInput.value, 10) || 0,
                    category: productCategoryInput.value.trim() || 'general',
                    status: productStatusInput.value,
                };

                const existingProductId = productIdInput.value;
                const url = existingProductId ? `/api/products/${existingProductId}` : '/api/products';
                const method = existingProductId ? 'PUT' : 'POST';
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                try {
                    const response = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!response.ok) {
                        alert('No se pudo guardar el producto. Verifica tu backend.');
                        return;
                    }

                    await fetchProducts();
                    productModal.hide();
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error de conexión.');
                }
            });

            productTableBody.addEventListener('click', async (event) => {
                const button = event.target.closest('button');
                if (!button) return;

                const action = button.dataset.action;
                const id = button.dataset.id;
                if (!id) return;

                if (action === 'edit') {
                    const response = await fetch(`/api/products?vendor_id=${encodeURIComponent(vendorId)}`);
                    const products = await response.json();
                    const product = products.find(item => item.id == id);
                    if (!product) return;

                    productIdInput.value = product.id;
                    productNameInput.value = product.name;
                    productDescriptionInput.value = product.description || '';
                    productPriceInput.value = product.price;
                    productStockInput.value = product.stock;
                    productCategoryInput.value = product.category || '';
                    productStatusInput.value = product.status || 'activo';
                    saveProductButton.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Actualizar producto';
                    document.getElementById('productModalLabel').textContent = 'Editar producto';
                    productModal.show();
                }

                if (action === 'delete' && confirm('¿Estás seguro de eliminar este producto?')) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const response = await fetch(`/api/products/${id}?vendor_id=${encodeURIComponent(vendorId)}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    if (response.ok) {
                        await fetchProducts();
                    } else {
                        alert('Error al eliminar.');
                    }
                }
            });

            if (isLoggedIn) {
                const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                cartList.innerHTML = cart.length === 0
                    ? '<li class="list-group-item bg-transparent text-muted px-0 text-center py-4"><i class="bi bi-cart-x fs-2 d-block mb-2"></i>Vacio</li>'
                    : cart.map(item => `<li class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center">${item.name}<span class="badge bg-primary rounded-pill">$${item.price}</span></li>`).join('');
            } else {
                cartList.innerHTML = '<li class="list-group-item bg-transparent px-0 text-muted">Inicia sesión.</li>';
            }

            logoutBtn.addEventListener('click', () => {
                localStorage.removeItem('userLoggedIn');
                window.location.href = "{{ route('home') }}";
            });

            const htmlElement = document.documentElement;
            const updateIcon = (theme) => {
                toggleButton.innerHTML = theme === 'dark' ? '<i class="bi bi-moon-stars-fill text-warning"></i>' : '<i class="bi bi-sun-fill text-warning"></i>';
            };
            
            updateIcon(htmlElement.getAttribute('data-bs-theme'));

            toggleButton.addEventListener('click', () => {
                const newTheme = htmlElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
                htmlElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateIcon(newTheme);
            });

            fetchProducts();
        });
    </script>
</body>
</html>
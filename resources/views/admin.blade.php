<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - DevMart</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <style>
        body { transition: background-color 0.3s ease, color 0.3s ease; }
        .dark-mode-toggle { cursor: pointer; font-size: 1.5rem; }
        .admin-container { min-height: 80vh; }
        .product-card img { max-height: 80px; object-fit: cover; }
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
            <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">DevMart <span class="badge bg-primary fs-6">Mi Panel</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="ms-auto d-flex align-items-center flex-wrap gap-2 nav-actions">
                    <a class="nav-link" href="{{ route('home') }}">Ver Sitio</a>
                    <button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#userPanel">👤 Cuenta</button>
                    <button class="btn btn-link dark-mode-toggle" id="darkModeToggle">☀️</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-5 admin-container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="mb-1">Gestión de mis productos</h1>
                <p class="text-muted mb-0">Crea, edita o elimina tus productos directamente desde tu panel de vendedor.</p>
            </div>
            <button class="btn btn-success" id="newProductButton">+ Nuevo Producto</button>
        </div>

        <div id="notLoggedAlert" class="alert alert-warning d-none" role="alert">
            Debes iniciar sesión para ver y administrar tus productos. <a href="{{ route('login') }}" class="alert-link">Iniciar sesión</a> o <a href="{{ route('register') }}" class="alert-link">registrarte</a>.
        </div>

        <div class="table-responsive">
            <table class="table table-hover border align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    <tr>
                        <td colspan="7" class="text-center text-muted">Cargando productos...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Agregar nuevo producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="productForm">
                    <div class="modal-body">
                        <input type="hidden" id="productId" value="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="productName" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="productName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="productCategory" class="form-label">Categoría</label>
                                <input type="text" class="form-control" id="productCategory" placeholder="Backend, Frontend, UI...">
                            </div>
                            <div class="col-md-6">
                                <label for="productPrice" class="form-label">Precio</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="productPrice" required>
                            </div>
                            <div class="col-md-6">
                                <label for="productStock" class="form-label">Stock</label>
                                <input type="number" min="0" class="form-control" id="productStock" required>
                            </div>
                            <div class="col-12">
                                <label for="productDescription" class="form-label">Descripción</label>
                                <textarea class="form-control" id="productDescription" rows="4"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="productImg" class="form-label">URL de imagen</label>
                                <input type="url" class="form-control" id="productImg" placeholder="https://...">
                            </div>
                            <div class="col-md-6">
                                <label for="productStatus" class="form-label">Estado</label>
                                <select class="form-select" id="productStatus">
                                    <option value="activo">Activo</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="agotado">Agotado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="saveProductButton">Guardar producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="userPanel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">👤 Mi Cuenta</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <p class="text-muted small">Accede a tus datos de vendedor y tu carrito de compras.</p>
            <div class="mb-4">
                <h6>🛒 Carrito</h6>
                <ul id="cartList" class="list-group list-group-flush mb-3"></ul>
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
            <p class="mb-0">&copy; {{ date('Y') }} DevMart Admin. Todos los derechos reservados.</p>
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
            const productImgInput = document.getElementById('productImg');
            const saveProductButton = document.getElementById('saveProductButton');

            const vendorId = localStorage.getItem('vendorId') || 'vendedor-demo-001';
            if (!localStorage.getItem('vendorId')) {
                localStorage.setItem('vendorId', vendorId);
            }

            const isLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
            if (!isLoggedIn) {
                notLoggedAlert.classList.remove('d-none');
                productTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Debes iniciar sesión para administrar productos.</td></tr>';
                newProductButton.disabled = true;
            }

            async function fetchProducts() {
                if (!isLoggedIn) {
                    return;
                }

                const response = await fetch(`/api/products?vendor_id=${encodeURIComponent(vendorId)}`);
                const products = await response.json();
                renderProducts(products);
            }

            function renderProducts(products) {
                if (!products.length) {
                    productTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No tienes productos publicados todavía.</td></tr>';
                    return;
                }

                productTableBody.innerHTML = products.map(product => `
                    <tr>
                        <td class="align-middle"><img src="${product.img}" class="img-thumbnail product-card" alt="${product.name}"></td>
                        <td class="align-middle">
                            <strong>${product.name}</strong><br>
                            <small class="text-muted">${product.description || 'Sin descripción'}</small>
                        </td>
                        <td class="align-middle">$${product.price}</td>
                        <td class="align-middle">${product.stock}</td>
                        <td class="align-middle">${product.category || 'general'}</td>
                        <td class="align-middle"><span class="badge bg-${product.status === 'activo' ? 'success' : 'secondary'}">${product.status}</span></td>
                        <td class="align-middle text-end">
                            <button class="btn btn-sm btn-outline-primary me-2" data-action="edit" data-id="${product.id}">Editar</button>
                            <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${product.id}">Eliminar</button>
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
                productImgInput.value = '';
                saveProductButton.textContent = 'Guardar producto';
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
                    img: productImgInput.value.trim() || null,
                };

                const existingProductId = productIdInput.value;
                const url = existingProductId ? `/api/products/${existingProductId}` : '/api/products';
                const method = existingProductId ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method,
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    const error = await response.json();
                    alert(error.message || 'No se pudo guardar el producto.');
                    return;
                }

                await fetchProducts();
                productModal.hide();
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
                    const product = products.find(item => item.id === id);
                    if (!product) {
                        alert('Producto no encontrado.');
                        return;
                    }

                    productIdInput.value = product.id;
                    productNameInput.value = product.name;
                    productDescriptionInput.value = product.description || '';
                    productPriceInput.value = product.price;
                    productStockInput.value = product.stock;
                    productCategoryInput.value = product.category || '';
                    productStatusInput.value = product.status || 'activo';
                    productImgInput.value = product.img || '';
                    saveProductButton.textContent = 'Actualizar producto';
                    document.getElementById('productModalLabel').textContent = 'Editar producto';
                    productModal.show();
                }

                if (action === 'delete' && confirm('¿Eliminar este producto?')) {
                    const response = await fetch(`/api/products/${id}?vendor_id=${encodeURIComponent(vendorId)}`, {
                        method: 'DELETE',
                    });
                    if (!response.ok) {
                        const error = await response.json();
                        alert(error.message || 'No se pudo eliminar el producto.');
                        return;
                    }
                    await fetchProducts();
                }
            });

            if (isLoggedIn) {
                const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                cartList.innerHTML = cart.length === 0
                    ? '<li class="list-group-item text-muted">El carrito está vacío</li>'
                    : cart.map(item => `<li class="list-group-item d-flex justify-content-between align-items-center">${item.name}<span class="badge bg-primary rounded-pill">$${item.price}</span></li>`).join('');
            } else {
                cartList.innerHTML = '<li class="list-group-item text-muted">Debes iniciar sesión para ver tu carrito.</li>';
            }

            logoutBtn.addEventListener('click', () => {
                localStorage.removeItem('userLoggedIn');
                window.location.href = "{{ route('home') }}";
            });

            const htmlElement = document.documentElement;
            toggleButton.innerHTML = htmlElement.getAttribute('data-bs-theme') === 'dark' ? '🌙' : '☀️';
            toggleButton.addEventListener('click', () => {
                const newTheme = htmlElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
                htmlElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                toggleButton.innerHTML = newTheme === 'dark' ? '🌙' : '☀️';
            });

            fetchProducts();
        });
    </script>
</body>
</html>
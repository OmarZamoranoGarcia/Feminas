
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - DevMart</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.auth-sync')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            transition: background 0.3s ease, color 0.3s ease;
            background: linear-gradient(135deg, #f6f8fd 0%, #f1f5f9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        [data-bs-theme="dark"] body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        .navbar {
            backdrop-filter: blur(12px);
            background-color: rgba(var(--bs-body-bg-rgb), 0.85) !important;
            border-bottom: 1px solid var(--bs-border-color-translucent);
            box-shadow: 0 4px 30px rgba(0,0,0,.03);
        }
        .dark-mode-toggle { cursor: pointer; font-size: 1.3rem; transition: transform .2s; }
        .dark-mode-toggle:hover { transform: scale(1.1); }
        .admin-container { flex: 1; }
        .admin-card {
            background-color: var(--bs-body-bg);
            border-radius: 1.25rem;
            box-shadow: 0 10px 40px rgba(0,0,0,.08);
            padding: 2rem;
            border: 1px solid var(--bs-border-color-translucent);
        }
        [data-bs-theme="dark"] .admin-card {
            box-shadow: 0 10px 40px rgba(0,0,0,.3);
            border-color: rgba(255,255,255,.05);
        }
        .table-responsive {
            border-radius: .75rem;
            overflow: hidden;
            border: 1px solid var(--bs-border-color-translucent);
        }
        .table { margin-bottom: 0; }
       
        .product-thumb {
            width: 52px; height: 52px;
            object-fit: cover;
            border-radius: .5rem;
            /* Agregamos una transición suave para el cambio de tamaño y la sombra */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative; /* Necesario para que el z-index funcione */
            z-index: 1;
        }

        /* Esta es la nueva regla que se activa al pasar el cursor */
        .product-thumb:hover {
            transform: scale(1.8); /* Aumenta el tamaño al 180% */
            z-index: 10; /* Asegura que la imagen sobresalga por encima de otras filas */
            box-shadow: 0 8px 15px rgba(0,0,0,0.2); /* Le da una pequeña sombra para dar profundidad */
        }
        .btn-gradient {
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
            border: none; color: white;
            transition: opacity .2s, transform .2s;
        }
        .btn-gradient:hover { opacity: .9; color: white; transform: translateY(-2px); }
        .modal-content { border-radius: 1.25rem; border: none; box-shadow: 0 20px 50px rgba(0,0,0,.15); }
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
            <a class="navbar-brand fw-bold fs-4 d-flex align-items-center gap-2" href="{{ route('home') }}">
                <i class="bi bi-braces text-primary"></i> DevMart
                <span class="badge bg-primary fs-6 rounded-pill">Mi Panel</span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button"
                    data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="ms-auto d-flex align-items-center flex-wrap gap-3 mt-3 mt-lg-0">
                    <a class="nav-link fw-medium" href="{{ route('home') }}">
                        <i class="bi bi-house-door me-1"></i>Ver Sitio
                    </a>
                    <button class="btn btn-link dark-mode-toggle text-decoration-none p-0" id="darkModeToggle">☀️</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-5 admin-container">

        <!-- Not logged in warning -->
        <div id="notLoggedAlert" class="alert alert-warning d-none shadow-sm rounded-4 border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Debes iniciar sesión para administrar tus productos.
            <a href="{{ route('login') }}" class="alert-link">Iniciar sesión</a> o
            <a href="{{ route('register') }}" class="alert-link">registrarte</a>.
        </div>

        <div class="admin-card">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
                <div>
                    <h1 class="mb-1 h3 fw-bold">
                        <i class="bi bi-grid-1x2 me-2 text-primary"></i>Gestión de Productos
                    </h1>
                    <p class="text-secondary mb-0">Crea, edita o elimina tus productos directamente desde tu panel.</p>
                </div>
                <button class="btn btn-gradient rounded-pill px-4 shadow-sm" id="newProductButton">
                    <i class="bi bi-plus-lg me-1"></i>Nuevo Producto
                </button>
            </div>

            <!-- Feedback banner -->
            <div id="feedbackBanner" class="alert d-none mb-3 rounded-3" role="alert"></div>

            <div class="table-responsive shadow-sm">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr class="text-secondary text-uppercase" style="font-size:.85rem">
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

    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1"
         aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="productModalLabel">Agregar nuevo producto</h5>
                    <button type="button" class="btn-close shadow-none"
                            data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-4">
                    <input type="hidden" id="productId">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="productName" class="form-label fw-medium">Nombre del Producto</label>
                            <input type="text" class="form-control rounded-3" id="productName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="productCategory" class="form-label fw-medium">Categoría</label>
                            <input type="text" class="form-control rounded-3" id="productCategory"
                                   placeholder="Backend, Frontend, UI...">
                        </div>
                        <div class="col-md-6">
                            <label for="productPrice" class="form-label fw-medium">Precio ($)</label>
                            <input type="number" step="0.01" min="0"
                                   class="form-control rounded-3" id="productPrice" required>
                        </div>
                        <div class="col-md-6">
                            <label for="productStock" class="form-label fw-medium">Stock Disponible</label>
                            <input type="number" min="0"
                                   class="form-control rounded-3" id="productStock" required>
                        </div>
                        <div class="col-12">
                            <label for="productDescription" class="form-label fw-medium">Descripción</label>
                            <textarea class="form-control rounded-3"
                                      id="productDescription" rows="4"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="productStatus" class="form-label fw-medium">Estado</label>
                            <select class="form-select rounded-3" id="productStatus">
                                <option value="activo">Activo</option>
                                <option value="agotado">Agotado</option>
                                <option value="oculto">Oculto</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm"
                            id="saveProductButton">
                        <span id="saveBtnLabel"><i class="bi bi-check-lg me-1"></i>Guardar producto</span>
                        <span id="saveBtnSpinner" class="d-none">
                            <span class="spinner-border spinner-border-sm me-1"></span>Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete confirm modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center p-3">
                <div class="modal-body">
                    <i class="bi bi-trash-fill text-danger fs-1 d-block mb-2"></i>
                    <h6 class="fw-bold">¿Eliminar producto?</h6>
                    <p class="text-muted small mb-0" id="deleteProductName"></p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0">
                    <button class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">
                        <span id="deleteBtnLabel">Eliminar</span>
                        <span id="deleteBtnSpinner" class="d-none">
                            <span class="spinner-border spinner-border-sm"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 mt-auto">
        <div class="container">
            <p class="mb-0 text-secondary small">&copy; {{ date('Y') }} DevMart Admin.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // State
        const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const isLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
        // vendorId persisted in localStorage after real login
        const vendorId = localStorage.getItem('vendorId') ?? 'vendedor-demo-001';

        // UI Refs
        const notLoggedAlert     = document.getElementById('notLoggedAlert');
        const feedbackBanner     = document.getElementById('feedbackBanner');
        const productTableBody   = document.getElementById('productTableBody');
        const newProductButton   = document.getElementById('newProductButton');
        const productModalEl     = document.getElementById('productModal');
        const productModal       = new bootstrap.Modal(productModalEl);
        const deleteModalEl      = document.getElementById('deleteModal');
        const deleteModal        = new bootstrap.Modal(deleteModalEl);
        const saveProductButton  = document.getElementById('saveProductButton');
        const saveBtnLabel       = document.getElementById('saveBtnLabel');
        const saveBtnSpinner     = document.getElementById('saveBtnSpinner');
        const confirmDeleteBtn   = document.getElementById('confirmDeleteBtn');
        const deleteBtnLabel     = document.getElementById('deleteBtnLabel');
        const deleteBtnSpinner   = document.getElementById('deleteBtnSpinner');
        const deleteProductName  = document.getElementById('deleteProductName');

        // Form fields
        const productIdInput     = document.getElementById('productId');
        const productNameInput   = document.getElementById('productName');
        const productDescInput   = document.getElementById('productDescription');
        const productPriceInput  = document.getElementById('productPrice');
        const productStockInput  = document.getElementById('productStock');
        const productCatInput    = document.getElementById('productCategory');
        const productStatusInput = document.getElementById('productStatus');

        let pendingDeleteId   = null;
        let pendingDeleteName = '';

        // Auth gate
        if (!isLoggedIn) {
            notLoggedAlert.classList.remove('d-none');
            newProductButton.disabled = true;
            productTableBody.innerHTML = `
                <tr><td colspan="7" class="text-center text-muted py-5">
                    <i class="bi bi-lock fs-2 d-block mb-2"></i>
                    Inicia sesión para administrar productos.
                </td></tr>`;
            return; // stop further execution
        }

        // Feedback helpers
        function showBanner(msg, type = 'success') {
            feedbackBanner.className = `alert alert-${type} rounded-3`;
            feedbackBanner.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill me-2"></i>${msg}`;
            feedbackBanner.classList.remove('d-none');
            setTimeout(() => feedbackBanner.classList.add('d-none'), 4000);
        }

        // Load products
        async function fetchProducts() {
            productTableBody.innerHTML = `
                <tr><td colspan="7" class="text-center text-primary py-5 fw-bold">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </td></tr>`;
            try {
                const res = await fetch(`/api/products?vendor_id=${encodeURIComponent(vendorId)}`);
                const products = await res.json();
                renderTable(products);
            } catch {
                productTableBody.innerHTML = `
                    <tr><td colspan="7" class="text-center text-danger py-4">
                        Error cargando productos.
                    </td></tr>`;
            }
        }

        function statusBadge(status) {
            const map = { activo: 'success', agotado: 'danger', oculto: 'secondary', pendiente: 'warning' };
            return `<span class="badge bg-${map[status] ?? 'secondary'} text-white">${status}</span>`;
        }

        function renderTable(products) {
            if (!products.length) {
                productTableBody.innerHTML = `
                    <tr><td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                        No tienes productos publicados todavía.
                    </td></tr>`;
                return;
            }
            productTableBody.innerHTML = products.map(p => `
                <tr>
                    <td class="ps-3">
                        <img src="${p.img}" class="product-thumb" alt="${p.name}">
                    </td>
                    <td>
                        <strong>${p.name}</strong><br>
                        <small class="text-secondary">${(p.description ?? '').substring(0, 50)}${(p.description ?? '').length > 50 ? '…' : ''}</small>
                    </td>
                    <td class="fw-medium">$${p.price}</td>
                    <td>${p.stock}</td>
                    <td><span class="badge text-bg-light border">${p.category}</span></td>
                    <td>${statusBadge(p.status)}</td>
                    <td class="text-end pe-3">
                        <button class="btn btn-sm btn-light border me-1"
                                data-action="edit"
                                data-id="${p.id}"
                                data-name="${p.name}"
                                data-desc="${(p.description ?? '').replace(/"/g, '&quot;')}"
                                data-price="${p.price}"
                                data-stock="${p.stock}"
                                data-category="${p.category}"
                                data-status="${p.status}"
                                title="Editar">
                            <i class="bi bi-pencil-square text-primary"></i>
                        </button>
                        <button class="btn btn-sm btn-light border"
                                data-action="delete"
                                data-id="${p.id}"
                                data-name="${p.name}"
                                title="Eliminar">
                            <i class="bi bi-trash text-danger"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // New product
        function resetForm() {
            productIdInput.value      = '';
            productNameInput.value    = '';
            productDescInput.value    = '';
            productPriceInput.value   = '';
            productStockInput.value   = '';
            productCatInput.value     = '';
            productStatusInput.value  = 'activo';
            document.getElementById('productModalLabel').textContent = 'Agregar nuevo producto';
            saveBtnLabel.innerHTML = '<i class="bi bi-check-lg me-1"></i>Guardar producto';
        }

        newProductButton.addEventListener('click', () => {
            resetForm();
            productModal.show();
        });

        // Table action delegation
        productTableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-action]');
            if (!btn) return;

            if (btn.dataset.action === 'edit') {
                productIdInput.value      = btn.dataset.id;
                productNameInput.value    = btn.dataset.name;
                productDescInput.value    = btn.dataset.desc;
                productPriceInput.value   = btn.dataset.price;
                productStockInput.value   = btn.dataset.stock;
                productCatInput.value     = btn.dataset.category;
                productStatusInput.value  = btn.dataset.status;
                document.getElementById('productModalLabel').textContent = 'Editar producto';
                saveBtnLabel.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Actualizar producto';
                productModal.show();
            }

            if (btn.dataset.action === 'delete') {
                pendingDeleteId   = btn.dataset.id;
                pendingDeleteName = btn.dataset.name;
                deleteProductName.textContent = pendingDeleteName;
                deleteModal.show();
            }
        });

        // Save product
        saveProductButton.addEventListener('click', async () => {
            // Basic HTML5 validation
            const fields = [productNameInput, productPriceInput, productStockInput];
            if (fields.some(f => !f.value.trim())) {
                showBanner('Por favor completa los campos obligatorios.', 'warning');
                return;
            }

            saveBtnLabel.classList.add('d-none');
            saveBtnSpinner.classList.remove('d-none');
            saveProductButton.disabled = true;

            const existingId = productIdInput.value;
            const url    = existingId ? `/api/products/${existingId}` : '/api/products';
            const method = existingId ? 'PUT' : 'POST';

            const payload = {
                vendor_id:   vendorId,
                name:        productNameInput.value.trim(),
                description: productDescInput.value.trim(),
                price:       parseFloat(productPriceInput.value),
                stock:       parseInt(productStockInput.value, 10),
                category:    productCatInput.value.trim() || 'general',
                status:      productStatusInput.value,
            };

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    productModal.hide();
                    showBanner(existingId ? 'Producto actualizado correctamente.' : 'Producto creado correctamente.');
                    await fetchProducts();
                } else {
                    const msg = data.message ?? JSON.stringify(data.errors ?? 'Error desconocido');
                    showBanner('Error: ' + msg, 'danger');
                }
            } catch {
                showBanner('Error de conexión con el servidor.', 'danger');
            }

            saveBtnLabel.classList.remove('d-none');
            saveBtnSpinner.classList.add('d-none');
            saveProductButton.disabled = false;
        });

        // Confirm delete
        confirmDeleteBtn.addEventListener('click', async () => {
            if (!pendingDeleteId) return;

            deleteBtnLabel.classList.add('d-none');
            deleteBtnSpinner.classList.remove('d-none');
            confirmDeleteBtn.disabled = true;

            try {
                const res = await fetch(
                    `/api/products/${pendingDeleteId}?vendor_id=${encodeURIComponent(vendorId)}`,
                    {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': CSRF },
                    }
                );

                const data = await res.json();

                if (res.ok && data.success) {
                    deleteModal.hide();
                    showBanner(`"${pendingDeleteName}" eliminado correctamente.`);
                    await fetchProducts();
                } else {
                    showBanner('Error al eliminar: ' + (data.message ?? ''), 'danger');
                }
            } catch {
                showBanner('Error de conexión.', 'danger');
            }

            deleteBtnLabel.classList.remove('d-none');
            deleteBtnSpinner.classList.add('d-none');
            confirmDeleteBtn.disabled = false;
            pendingDeleteId = null;
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
    });
    </script>
</body>
</html>

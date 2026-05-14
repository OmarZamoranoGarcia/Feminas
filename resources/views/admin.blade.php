<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - DevMart</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            z-index: 1;
        }
        .product-thumb:hover {
            transform: scale(1.8);
            z-index: 10;
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        .btn-gradient {
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
            border: none; color: white;
            transition: opacity .2s, transform .2s;
        }
        .btn-gradient:hover { opacity: .9; color: white; transform: translateY(-2px); }
        .modal-content { border-radius: 1.25rem; border: none; box-shadow: 0 20px 50px rgba(0,0,0,.15); }
        .auth-dependent { visibility: hidden; }
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
                <div class="ms-auto d-flex align-items-center flex-wrap gap-3 mt-3 mt-lg-0 auth-dependent">
                    <a class="nav-link fw-medium" href="{{ route('home') }}">
                        <i class="bi bi-house-door me-1"></i>Ver Sitio
                    </a>
                    <button class="btn btn-outline-danger btn-sm" id="logoutBtn" type="button">
                        <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                    </button>
                    <button class="btn btn-link dark-mode-toggle text-decoration-none p-0" id="darkModeToggle">☀️</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-5 admin-container">

        {{-- Only shown when not logged in --}}
        <div id="notLoggedAlert" class="alert alert-warning shadow-sm rounded-4 border-0" role="alert" style="display: none;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Debes iniciar sesión para administrar tus productos.
            <a href="{{ route('login') }}" class="alert-link">Iniciar sesión</a> o
            <a href="{{ route('register') }}" class="alert-link">registrarte</a>.
        </div>

        <div class="admin-card" id="adminCard" style="display: none;">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
                <div>
                    <h1 class="mb-1 h3 fw-bold">
                        <i class="bi bi-grid-1x2 me-2 text-primary"></i>Mis Productos
                    </h1>
                    <p class="text-secondary mb-0" id="panelSubtitle">Crea, edita o elimina tus productos.</p>
                </div>
                <button class="btn btn-gradient rounded-pill px-4 shadow-sm" id="newProductButton">
                    <i class="bi bi-plus-lg me-1"></i>Nuevo Producto
                </button>
            </div>

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
                                Verificando sesión...
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
                        <div class="col-md-6">
                            <label for="productImage" class="form-label fw-medium">Imagen del Producto</label>
                            <input type="file" class="form-control rounded-3" id="productImage" accept="image/*">
                            <div class="form-text text-muted">Límite de imagen: 2 MB.</div>
                            <div class="mt-2 text-center">
                                <img id="imagePreview" src="" class="img-thumbnail d-none" 
                                     style="max-height: 100px; width: auto; object-fit: contain;">
                            </div>
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
    document.addEventListener('DOMContentLoaded', async () => {
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        let currentUser = null;
        try {
            const res  = await fetch('/api/me', { credentials: 'same-origin' });
            const data = await res.json();
            if (data.success) currentUser = data.user;
        } catch { /* server unreachable */ }

        const notLoggedAlert = document.getElementById('notLoggedAlert');
        const adminCard      = document.getElementById('adminCard');
        const authDependent  = document.querySelector('.auth-dependent');

        if (authDependent) authDependent.style.visibility = 'visible';

        if (!currentUser) {
            notLoggedAlert.style.display = 'block';
            document.getElementById('productTableBody').innerHTML = `
                <tr><td colspan="7" class="text-center text-muted py-5">
                    <i class="bi bi-lock fs-2 d-block mb-2"></i>
                    Inicia sesión para administrar productos.
                </td></tr>`;
            return;
        }

        adminCard.style.display = 'block';

        if (currentUser.razon_social) {
            document.getElementById('panelSubtitle').textContent =
                `Gestionando productos de "${currentUser.razon_social}"`;
        }

        const vendorId = currentUser.vendor_id ?? currentUser.id;

        document.getElementById('logoutBtn').addEventListener('click', async () => {
            const btn = document.getElementById('logoutBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saliendo...';
            try {
                await fetch('/api/logout', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                });
            } catch { /* ignore */ }
            window.location.href = "{{ route('home') }}";
        });

        const feedbackBanner    = document.getElementById('feedbackBanner');
        const productModal      = new bootstrap.Modal(document.getElementById('productModal'));
        const deleteModal       = new bootstrap.Modal(document.getElementById('deleteModal'));
        const saveProductButton = document.getElementById('saveProductButton');
        const saveBtnLabel      = document.getElementById('saveBtnLabel');
        const saveBtnSpinner    = document.getElementById('saveBtnSpinner');
        const confirmDeleteBtn  = document.getElementById('confirmDeleteBtn');
        const deleteBtnLabel    = document.getElementById('deleteBtnLabel');
        const deleteBtnSpinner  = document.getElementById('deleteBtnSpinner');
        const deleteProductName = document.getElementById('deleteProductName');
        const productTableBody  = document.getElementById('productTableBody');

        const productIdInput     = document.getElementById('productId');
        const productNameInput   = document.getElementById('productName');
        const productDescInput   = document.getElementById('productDescription');
        const productPriceInput  = document.getElementById('productPrice');
        const productStockInput  = document.getElementById('productStock');
        const productCatInput    = document.getElementById('productCategory');
        const productStatusInput = document.getElementById('productStatus');

        const productImageInput  = document.getElementById('productImage');
        const imagePreview       = document.getElementById('imagePreview');
        const MAX_IMAGE_SIZE_MB  = 2;
        const MAX_IMAGE_SIZE     = MAX_IMAGE_SIZE_MB * 1024 * 1024;

        let pendingDeleteId   = null;
        let pendingDeleteName = '';

        function showBanner(msg, type = 'success') {
            feedbackBanner.className = `alert alert-${type} rounded-3`;
            feedbackBanner.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill me-2"></i>${msg}`;
            feedbackBanner.classList.remove('d-none');
            setTimeout(() => feedbackBanner.classList.add('d-none'), 4000);
        }

        function statusBadge(status) {
            const map = { activo: 'success', agotado: 'danger', oculto: 'secondary' };
            return `<span class="badge bg-${map[status] ?? 'secondary'} text-white">${status}</span>`;
        }

        function resetForm() {
            productIdInput.value     = '';
            productNameInput.value   = '';
            productDescInput.value   = '';
            productPriceInput.value  = '';
            productStockInput.value  = '';
            productCatInput.value    = '';
            productStatusInput.value = 'activo';
            productImageInput.value  = '';
            imagePreview.src         = '';
            imagePreview.classList.add('d-none');
            document.getElementById('productModalLabel').textContent = 'Agregar nuevo producto';
            saveBtnLabel.innerHTML = '<i class="bi bi-check-lg me-1"></i>Guardar producto';
        }

        // Función para comprimir la imagen y convertirla a Base64 (Texto)
        const compressImage = (file) => {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (e) => {
                    const img = new Image();
                    img.src = e.target.result;
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        const scale = Math.min(1, 800 / img.width); // Máximo 800px de ancho
                        canvas.width = img.width * scale;
                        canvas.height = img.height * scale;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                        resolve(canvas.toDataURL('image/jpeg', 0.7)); // Comprimir al 70% de calidad
                    };
                };
            });
        };

        // Vista previa de la imagen al seleccionar archivo
        productImageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) {
                imagePreview.src = '';
                imagePreview.classList.add('d-none');
                return;
            }

            if (file.size > MAX_IMAGE_SIZE) {
                showBanner(`La imagen excede el límite de ${MAX_IMAGE_SIZE_MB} MB. Elige un archivo más pequeño.`, 'warning');
                this.value = '';
                imagePreview.src = '';
                imagePreview.classList.add('d-none');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.classList.remove('d-none');
            }
            reader.readAsDataURL(file);
        });

        async function fetchProducts() {
            productTableBody.innerHTML = `
                <tr><td colspan="7" class="text-center text-primary py-5 fw-bold">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </td></tr>`;
            try {
                const res      = await fetch(`/api/products?vendor_id=${encodeURIComponent(vendorId)}`, { credentials: 'same-origin' });
                const products = await res.json();
                renderTable(products);
            } catch {
                productTableBody.innerHTML = `
                    <tr><td colspan="7" class="text-center text-danger py-4">
                        Error cargando productos.
                    </td></tr>`;
            }
        }

        function renderTable(products) {
            if (!products.length) {
                productTableBody.innerHTML = `
                    <tr><td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                        No tienes productos publicados todavía.<br>
                        <button class="btn btn-primary btn-sm mt-3" id="emptyStateNewBtn">
                            <i class="bi bi-plus-lg me-1"></i>Publicar mi primer producto
                        </button>
                    </td></tr>`;
                document.getElementById('emptyStateNewBtn')
                    ?.addEventListener('click', () => { resetForm(); productModal.show(); });
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
                                data-img="${p.img}"
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

        document.getElementById('newProductButton').addEventListener('click', () => {
            resetForm();
            productModal.show();
        });

        productTableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-action]');
            if (!btn) return;

            if (btn.dataset.action === 'edit') {
                productIdInput.value     = btn.dataset.id;
                productNameInput.value   = btn.dataset.name;
                productDescInput.value   = btn.dataset.desc;
                productPriceInput.value  = btn.dataset.price;
                productStockInput.value  = btn.dataset.stock;
                productCatInput.value    = btn.dataset.category;
                productStatusInput.value = btn.dataset.status;
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

        saveProductButton.addEventListener('click', async () => {
            if (!productNameInput.value.trim() || !productPriceInput.value || !productStockInput.value) {
                showBanner('Por favor completa los campos obligatorios.', 'warning');
                return;
            }

            saveBtnLabel.classList.add('d-none');
            saveBtnSpinner.classList.remove('d-none');
            saveProductButton.disabled = true;

            const existingId = productIdInput.value;
            const url = existingId ? `/api/products/${existingId}` : '/api/products';

            const formData = new FormData();
            formData.append('vendor_id', vendorId);
            formData.append('name', productNameInput.value.trim());
            formData.append('description', productDescInput.value.trim());
            formData.append('price', parseFloat(productPriceInput.value));
            formData.append('stock', parseInt(productStockInput.value, 10));
            formData.append('category', productCatInput.value.trim() || 'general');
            formData.append('status', productStatusInput.value);

            if (productImageInput.files[0]) {
                if (productImageInput.files[0].size > MAX_IMAGE_SIZE) {
                    showBanner(`La imagen excede el límite de ${MAX_IMAGE_SIZE_MB} MB.`, 'warning');
                    saveBtnLabel.classList.remove('d-none');
                    saveBtnSpinner.classList.add('d-none');
                    saveProductButton.disabled = false;
                    return;
                }
                const compressedBase64 = await compressImage(productImageInput.files[0]);
                formData.append('img', compressedBase64);
            }

            if (existingId) {
                formData.append('_method', 'PUT');
            }

            try {
                const res  = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                    body: formData,
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    productModal.hide();
                    showBanner(existingId ? 'Producto actualizado correctamente.' : 'Producto creado correctamente.');
                    await fetchProducts();
                } else {
                    showBanner('Error: ' + (data.message ?? JSON.stringify(data.errors ?? '')), 'danger');
                }
            } catch {
                showBanner('Error de conexión con el servidor.', 'danger');
            }

            saveBtnLabel.classList.remove('d-none');
            saveBtnSpinner.classList.add('d-none');
            saveProductButton.disabled = false;
        });

        confirmDeleteBtn.addEventListener('click', async () => {
            if (!pendingDeleteId) return;

            deleteBtnLabel.classList.add('d-none');
            deleteBtnSpinner.classList.remove('d-none');
            confirmDeleteBtn.disabled = true;

            try {
                const res  = await fetch(
                    `/api/products/${pendingDeleteId}?vendor_id=${encodeURIComponent(vendorId)}`,
                    { method: 'DELETE', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': CSRF } }
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

        const toggleButton = document.getElementById('darkModeToggle');
        const html = document.documentElement;
        toggleButton.innerHTML = html.getAttribute('data-bs-theme') === 'dark' ? '🌙' : '☀️';
        toggleButton.addEventListener('click', () => {
            const newTheme = html.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            toggleButton.innerHTML = newTheme === 'dark' ? '🌙' : '☀️';
        });

        fetchProducts();
    });
    </script>
</body>
</html>

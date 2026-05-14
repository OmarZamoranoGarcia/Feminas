<div class="offcanvas offcanvas-end shadow border-0" tabindex="-1" id="cartDrawer" style="border-radius: 1.5rem 0 0 1.5rem;">
    <div class="offcanvas-header border-bottom py-4">
        <h5 class="offcanvas-title fw-bold">
            <i class="bi bi-cart3 me-2 text-primary"></i>Tu Carrito
            <span class="badge bg-primary ms-2" id="cartCount">0</span>
        </h5>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"></button>
    </div>
    
    <div class="offcanvas-body d-flex flex-column">
        <!-- Estado vacío -->
        <div id="emptyState" class="text-center py-5">
            <i class="bi bi-cart-x" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-secondary mt-3">Tu carrito está vacío</p>
        </div>

        <!-- Contenido del carrito -->
        <div id="cartContent" style="display: none;">
            <ul id="cartList" class="list-group list-group-flush mb-4"></ul>
            
            <!-- Resumen -->
            <div class="bg-body-tertiary p-3 rounded-4 mb-4" id="cartSummary">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Subtotal</span>
                    <span class="fw-medium" id="cartSubtotal">$0.00</span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2 mt-2">
                    <span class="fw-bold">Total</span>
                    <span class="fw-bold text-primary" id="cartTotal">$0.00</span>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="mt-auto d-grid gap-2">
                <button class="btn btn-primary rounded-pill py-2 shadow-sm fw-bold" id="checkoutBtn">
                    Finalizar Compra <i class="bi bi-arrow-right ms-1"></i>
                </button>
                <button class="btn btn-link btn-sm text-secondary text-decoration-none" id="clearCartBtn">
                    Vaciar carrito
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartDrawer = {
        cartData: {},
        
        init() {
            this.attachEventListeners();
            this.render();
        },

        updateQuantity(productId, quantity) {
            if (quantity <= 0) {
                this.removeItem(productId);
            } else {
                this.cartData[productId].quantity = quantity;
                this.render();
                // Sync to backend if exists
                this.syncToBackend(productId, quantity);
            }
        },

        removeItem(productId) {
            delete this.cartData[productId];
            this.render();
            // Sync removal to backend if exists
            this.syncRemovalToBackend(productId);
        },

        clearCart() {
            if (confirm('¿Seguro que deseas vaciar el carrito?')) {
                Object.keys(this.cartData).forEach(id => {
                    this.syncRemovalToBackend(id);
                });
                this.cartData = {};
                this.render();
            }
        },

        getTotal() {
            return Object.values(this.cartData).reduce((sum, item) => {
                return sum + (item.price * item.quantity);
            }, 0);
        },

        attachEventListeners() {
            document.getElementById('checkoutBtn')?.addEventListener('click', () => this.checkout());
            document.getElementById('clearCartBtn')?.addEventListener('click', () => this.clearCart());
        },

        checkout() {
            if (Object.keys(this.cartData).length === 0) {
                alert('El carrito está vacío');
                return;
            }
            console.log('Procesando compra:', this.cartData);
            // Redirect to checkout or payment page
            window.location.href = '/checkout';
        },

        syncToBackend(productId, quantity) {
            // Will be called by welcome.blade.php if backend exists
            if (window.updateBackendCart) {
                window.updateBackendCart(productId, quantity);
            }
        },

        syncRemovalToBackend(productId) {
            // Will be called by welcome.blade.php if backend exists
            if (window.removeBackendCartItem) {
                window.removeBackendCartItem(productId);
            }
        },

        render() {
            const isEmpty = Object.keys(this.cartData).length === 0;
            const emptyState = document.getElementById('emptyState');
            const cartContent = document.getElementById('cartContent');
            
            emptyState.style.display = isEmpty ? 'block' : 'none';
            cartContent.style.display = isEmpty ? 'none' : 'flex';

            // Actualizar contador
            document.getElementById('cartCount').textContent = Object.values(this.cartData)
                .reduce((sum, item) => sum + item.quantity, 0);

            // Renderizar items
            const cartList = document.getElementById('cartList');
            cartList.innerHTML = Object.entries(this.cartData).map(([id, item]) => `
                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold">${item.name}</h6>
                        <small class="text-secondary">$${item.price.toFixed(2)}</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="cartDrawer.updateQuantity(${id}, ${item.quantity - 1})">
                            <i class="bi bi-dash"></i>
                        </button>
                        <span class="fw-medium" style="min-width: 30px; text-align: center;">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="cartDrawer.updateQuantity(${id}, ${item.quantity + 1})">
                            <i class="bi bi-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger ms-2" onclick="cartDrawer.removeItem(${id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </li>
            `).join('');

            // Actualizar totales
            const total = this.getTotal();
            document.getElementById('cartSubtotal').textContent = `$${total.toFixed(2)}`;
            document.getElementById('cartTotal').textContent = `$${total.toFixed(2)}`;
        }
    };

    window.cartDrawer = cartDrawer;
    cartDrawer.init();
});
</script>
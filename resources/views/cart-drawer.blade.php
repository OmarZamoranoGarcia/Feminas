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
        <div id="cartContent" class="flex-column flex-grow-1" style="display: none; overflow-y: hidden;">
            <ul id="cartList" class="list-group list-group-flush mb-4 overflow-y-auto" style="max-height: 60vh;"></ul>

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
            <div class="mt-auto d-grid gap-2 pt-3">
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
document.addEventListener('DOMContentLoaded', function () {
    const cartDrawer = {
        // { [productId]: { cart_id, name, price, quantity, img } }
        cartData: {},

        init() {
            document.getElementById('checkoutBtn')?.addEventListener('click', () => this.checkout());
            document.getElementById('clearCartBtn')?.addEventListener('click', () => this.clearCart());

            // Delegated remove button clicks
            document.getElementById('cartList').addEventListener('click', async (e) => {
                const btn = e.target.closest('.btn-remove-drawer');
                if (!btn) return;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                await this.removeFromBackend(btn.dataset.cartId);
            });
        },

        /**
         * Called by welcome.blade.php after every /api/cart response.
         * items: [{ cart_id, qty, product: { id, name, price, img } }]
         */
        syncFromApi(items) {
            this.cartData = {};
            items.forEach(item => {
                if (!item.product) return;
                this.cartData[item.product.id] = {
                    cart_id:  item.cart_id,
                    name:     item.product.name,
                    price:    parseFloat(item.product.price),
                    quantity: item.qty,
                    img:      item.product.img,
                };
            });
            this.render();
        },

        async removeFromBackend(cartId) {
            const CSRF   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const params = window.cartParams ? window.cartParams() : '';
            try {
                const res = await fetch(`/api/cart/${cartId}?${params}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                });
                if (!res.ok) console.error('Error al eliminar del carrito.');
            } catch {
                console.error('Error de conexión al eliminar del carrito.');
            }
            // Always reload from server to keep badge + list in sync
            if (window.reloadCart) await window.reloadCart();
        },

        async clearCart() {
            if (!confirm('¿Seguro que deseas vaciar el carrito?')) return;
            const ids = Object.values(this.cartData).map(i => i.cart_id);
            for (const cartId of ids) {
                await this.removeFromBackend(cartId);
            }
        },

        getTotal() {
            return Object.values(this.cartData)
                .reduce((sum, item) => sum + item.price * item.quantity, 0);
        },

        checkout() {
            if (!Object.keys(this.cartData).length) {
                alert('El carrito está vacío');
                return;
            }
            window.location.href = '/checkout';
        },

        render() {
            const items   = Object.values(this.cartData);
            const isEmpty = items.length === 0;

            document.getElementById('emptyState').style.display  = isEmpty ? 'block' : 'none';
            document.getElementById('cartContent').style.display = isEmpty ? 'none'  : 'flex';

            const count = items.reduce((s, i) => s + i.quantity, 0);
            document.getElementById('cartCount').textContent = count;

            const total = this.getTotal();
            document.getElementById('cartSubtotal').textContent = `$${total.toFixed(2)}`;
            document.getElementById('cartTotal').textContent    = `$${total.toFixed(2)}`;

            document.getElementById('cartList').innerHTML = items.map(item => `
                <li class="list-group-item px-0 py-2 border-0">
                    <div class="bg-body-tertiary p-3 rounded-4">
                        <div class="d-flex align-items-center justify-content-between gap-2">

                            <!-- Nombre -->
                            <span class="fw-semibold text-truncate" style="font-size:.95rem;">
                                ${item.name}
                            </span>

                            <!-- Qty controls + precio + eliminar -->
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <button class="btn btn-sm btn-outline-secondary rounded-circle"
                                        style="width:30px;height:30px;padding:0;line-height:1;" disabled>
                                    <i class="bi bi-dash" style="pointer-events:none;"></i>
                                </button>
                                <span class="fw-bold text-center" style="min-width:20px;font-size:.95rem;">
                                    ${item.quantity}
                                </span>
                                <button class="btn btn-sm btn-outline-secondary rounded-circle"
                                        style="width:30px;height:30px;padding:0;line-height:1;" disabled>
                                    <i class="bi bi-plus" style="pointer-events:none;"></i>
                                </button>

                                <span class="fw-bold text-primary border-start ps-2" style="font-size:.95rem;">
                                    $${(item.price * item.quantity).toFixed(2)}
                                </span>

                                <button class="btn btn-sm btn-outline-danger btn-remove-drawer"
                                        data-cart-id="${item.cart_id}" title="Eliminar"
                                        style="width:30px;height:30px;padding:0;line-height:1;">
                                    <i class="bi bi-trash" style="pointer-events:none;"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                </li>
            `).join('');
        },
    };

    window.cartDrawer = cartDrawer;
    cartDrawer.init();
});
</script>
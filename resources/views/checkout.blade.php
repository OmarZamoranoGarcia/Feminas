<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - DevMart</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <style>
        body { min-height: 100vh; }
        .checkout-card {
            background: var(--surface);
            border: 1px solid var(--surface-border);
            border-radius: 1.4rem;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(15,23,42,.1);
        }
        .shipping-option {
            border: 2px solid var(--surface-border);
            border-radius: 1rem;
            padding: 1.1rem 1.4rem;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }
        .shipping-option:has(input:checked) {
            border-color: var(--accent);
            background: rgba(90,108,255,.07);
        }
        .shipping-option input { accent-color: var(--accent); }
        .step-badge {
            width: 28px; height: 28px;
            border-radius: 50%;
            background: var(--accent);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .78rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .order-item-row { border-bottom: 1px solid var(--surface-border); }
        .order-item-row:last-child { border-bottom: none; }
        #successOverlay {
            position: fixed; inset: 0;
            background: rgba(5,8,15,.82);
            display: flex; align-items: center; justify-content: center;
            z-index: 9999;
            opacity: 0; pointer-events: none;
            transition: opacity .35s;
        }
        #successOverlay.visible { opacity: 1; pointer-events: all; }
        .success-card {
            background: var(--surface);
            border-radius: 2rem;
            padding: 3rem 2.5rem;
            text-align: center;
            max-width: 480px;
            width: 90%;
            box-shadow: 0 30px 80px rgba(0,0,0,.3);
            animation: popIn .4s cubic-bezier(.34,1.56,.64,1) both;
        }
        @keyframes popIn {
            from { transform: scale(.7); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }
        .success-icon {
            width: 72px; height: 72px;
            background: linear-gradient(135deg,#5a6cff,#2ac3ff);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.4rem;
            font-size: 2rem;
            color: #fff;
        }
        .split-row {
            border-left: 3px solid var(--accent);
            padding-left: .9rem;
            margin-bottom: .5rem;
        }
        .total-line { font-size: 1.2rem; }
    </style>
    <script>(function(){var t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top py-3">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">DevMart</a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <a class="btn btn-ghost btn-sm" href="{{ route('home') }}"><i class="bi bi-arrow-left me-1"></i>Seguir comprando</a>
                <button class="btn btn-ghost" id="darkModeToggle" style="font-size:1.2rem;border:none;background:none;padding:.5rem;"></button>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div id="notLoggedAlert" class="alert alert-warning rounded-4 d-none">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Debes <a href="{{ route('login') }}" class="alert-link">iniciar sesión</a> para completar tu compra.
        </div>

        <div id="emptyCartAlert" class="alert alert-info rounded-4 d-none">
            <i class="bi bi-cart-x me-2"></i>
            Tu carrito está vacío. <a href="{{ route('home') }}" class="alert-link">Ver productos</a>.
        </div>

        <div id="checkoutBody" class="d-none">
            <h1 class="fw-bold mb-4"><i class="bi bi-bag-check me-2 text-primary"></i>Finalizar Compra</h1>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="checkout-card mb-4">
                        <h5 class="fw-bold mb-3"><span class="step-badge me-2">1</span>Dirección de envío</h5>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nombre completo</label>
                            <input type="text" class="form-control rounded-3" id="shippingName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Dirección</label>
                            <input type="text" class="form-control rounded-3" id="shippingAddress" placeholder="Calle, número, colonia" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Ciudad</label>
                                <input type="text" class="form-control rounded-3" id="shippingCity" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Código Postal</label>
                                <input type="text" class="form-control rounded-3" id="shippingZip" required>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-card mb-4">
                        <h5 class="fw-bold mb-3"><span class="step-badge me-2">2</span>Tipo de envío</h5>
                        <div class="d-flex flex-column gap-2">
                            <label class="shipping-option d-flex align-items-center gap-3">
                                <input type="radio" name="shipping" value="standard" data-cost="0" checked>
                                <div>
                                    <div class="fw-semibold">Envío Estándar</div>
                                    <div class="text-muted small">5–8 días hábiles</div>
                                </div>
                                <span class="ms-auto fw-bold text-success">Gratis</span>
                            </label>
                            <label class="shipping-option d-flex align-items-center gap-3">
                                <input type="radio" name="shipping" value="express" data-cost="149">
                                <div>
                                    <div class="fw-semibold">Envío Express</div>
                                    <div class="text-muted small">1–2 días hábiles</div>
                                </div>
                                <span class="ms-auto fw-bold text-primary">+ $149.00</span>
                            </label>
                        </div>
                    </div>

                    <div class="checkout-card">
                        <h5 class="fw-bold mb-3"><span class="step-badge me-2">3</span>Datos de pago</h5>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Número de tarjeta</label>
                            <input type="text" class="form-control rounded-3" id="cardNumber"
                                   placeholder="4242 4242 4242 4242" maxlength="19">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Vencimiento</label>
                                <input type="text" class="form-control rounded-3" id="cardExpiry" placeholder="MM/AA" maxlength="5">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">CVV</label>
                                <input type="text" class="form-control rounded-3" id="cardCvv" placeholder="123" maxlength="4">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="checkout-card position-sticky" style="top:5rem;">
                        <h5 class="fw-bold mb-3"><i class="bi bi-receipt me-2 text-primary"></i>Resumen del pedido</h5>

                        <div id="orderItemsList" class="mb-3"></div>

                        <div class="mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span id="summarySubtotal" class="fw-medium">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Envío</span>
                                <span id="summaryShipping" class="fw-medium">Gratis</span>
                            </div>
                            <div class="d-flex justify-content-between border-top pt-2 mt-2 total-line">
                                <span class="fw-bold">Total</span>
                                <span id="summaryTotal" class="fw-bold text-primary">$0.00</span>
                            </div>
                        </div>

                        <div id="splitBreakdown" class="mt-4 d-none">
                            <h6 class="fw-semibold mb-2 text-muted small text-uppercase">Distribución de pagos</h6>
                            <div id="splitList"></div>
                        </div>

                        <div id="feedbackBanner" class="alert d-none mt-3 rounded-3 small" role="alert"></div>

                        <button class="btn btn-primary w-100 rounded-pill py-2 mt-3 shadow-sm fw-bold fs-5" id="placeOrderBtn">
                            <span id="placeOrderLabel"><i class="bi bi-lock-fill me-2"></i>Confirmar pedido</span>
                            <span id="placeOrderSpinner" class="d-none">
                                <span class="spinner-border spinner-border-sm me-2"></span>Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="successOverlay">
        <div class="success-card">
            <div class="success-icon"><i class="bi bi-check-lg"></i></div>
            <h3 class="fw-bold mb-2">¡Pedido confirmado!</h3>
            <p class="text-muted mb-1">Orden <strong id="successOrderId"></strong></p>
            <p class="text-muted small mb-4">Recibirás tu pedido según el método de envío elegido.</p>

            <div id="successSplitSection" class="text-start mb-4 d-none">
                <h6 class="fw-semibold mb-3 text-center small text-muted text-uppercase">Pagos a vendedores</h6>
                <div id="successSplitList"></div>
            </div>

            <div class="d-grid gap-2">
                <a href="{{ route('home') }}" class="btn btn-primary rounded-pill">Seguir comprando</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Dark mode toggle
    (function(){
        var t=localStorage.getItem('theme')||'light';
        var toggle=document.getElementById('darkModeToggle');
        if(toggle){toggle.textContent=t==='dark'?'🌙':'☀️';}
        if(toggle){toggle.addEventListener('click',function(){
            var n=document.documentElement.getAttribute('data-bs-theme')==='light'?'dark':'light';
            document.documentElement.setAttribute('data-bs-theme',n);
            localStorage.setItem('theme',n);
            toggle.textContent=n==='dark'?'🌙':'☀️';
        });}
    })();

    document.addEventListener('DOMContentLoaded', async () => {
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        // Obtener usuario actual
        let currentUser = null;
        try {
            const r = await fetch('/api/me', { credentials: 'same-origin' });
            const d = await r.json();
            if (d.success) currentUser = d.user;
            console.log('Usuario actual:', currentUser);
        } catch (e) {
            console.error('Error obteniendo usuario:', e);
        }

        if (!currentUser) {
            document.getElementById('notLoggedAlert').classList.remove('d-none');
            return;
        }

        const userId = currentUser.id_usuario || currentUser.id;
        const sessionToken = localStorage.getItem('sessionToken') ?? '';

        function cartParams() {
            return userId
                ? `user_id=${encodeURIComponent(userId)}`
                : `session_token=${encodeURIComponent(sessionToken)}`;
        }

        // Obtener items del carrito
        let cartItems = [];
        try {
            const r = await fetch(`/api/cart?${cartParams()}`, { credentials: 'same-origin' });
            const text = await r.text();
            console.log('Respuesta del carrito:', text);
            try {
                cartItems = JSON.parse(text);
            } catch (e) {
                console.error('Error parseando respuesta del carrito:', e);
                cartItems = [];
            }
        } catch (e) {
            console.error('Error obteniendo carrito:', e);
        }

        console.log('Items del carrito:', cartItems);

        if (!cartItems.length) {
            document.getElementById('emptyCartAlert').classList.remove('d-none');
            return;
        }

        document.getElementById('checkoutBody').classList.remove('d-none');

        // Rellenar nombre si está disponible
        if (currentUser.nombre) {
            document.getElementById('shippingName').value = currentUser.nombre;
        }

        const COMMISSION_RATE = 0.10;

        function subtotal() {
            return cartItems.reduce((s, i) => s + parseFloat(i.product?.price || i.price) * (i.qty || i.quantity), 0);
        }

        function shippingCost() {
            const checked = document.querySelector('input[name="shipping"]:checked');
            return checked ? parseFloat(checked.dataset.cost) : 0;
        }

        function renderItems() {
            const list = document.getElementById('orderItemsList');
            list.innerHTML = cartItems.map(i => {
                const product = i.product || i;
                const qty = i.qty || i.quantity || 1;
                const price = parseFloat(product.price || 0);
                return `
                    <div class="order-item-row py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-medium" style="font-size:.95rem;">${product.name || product.nombre || 'Producto'}</div>
                            <div class="text-muted small">x${qty} · $${price.toFixed(2)} c/u</div>
                        </div>
                        <span class="fw-semibold">$${(price * qty).toFixed(2)}</span>
                    </div>
                `;
            }).join('');
        }

        function buildSplits() {
            const vendorMap = {};
            cartItems.forEach(i => {
                const product = i.product || i;
                const qty = i.qty || i.quantity || 1;
                const vid = product.vendor_id || product.id_vendedor || 'unknown';
                const seller = product.seller || product.vendedor || 'Vendedor';
                if (!vendorMap[vid]) vendorMap[vid] = { seller, subtotal: 0 };
                vendorMap[vid].subtotal += parseFloat(product.price || 0) * qty;
            });
            return Object.entries(vendorMap).map(([vid, v]) => ({
                vendor_id: vid,
                seller: v.seller,
                amount: v.subtotal,
                commission: parseFloat((v.subtotal * COMMISSION_RATE).toFixed(2)),
                net: parseFloat((v.subtotal * (1 - COMMISSION_RATE)).toFixed(2)),
            }));
        }

        function renderSummary() {
            const sub = subtotal();
            const ship = shippingCost();
            const total = sub + ship;

            document.getElementById('summarySubtotal').textContent = `$${sub.toFixed(2)}`;
            document.getElementById('summaryShipping').textContent = ship > 0 ? `$${ship.toFixed(2)}` : 'Gratis';
            document.getElementById('summaryTotal').textContent = `$${total.toFixed(2)}`;

            const splits = buildSplits();
            const splitSection = document.getElementById('splitBreakdown');
            const splitList = document.getElementById('splitList');

            if (splits.length > 0) {
                splitSection.classList.remove('d-none');
                splitList.innerHTML = splits.map(s => `
                    <div class="split-row mb-3">
                        <div class="fw-semibold small">${s.seller}</div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Venta</span><span>$${s.amount.toFixed(2)}</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Comisión (10%)</span><span class="text-danger">-$${s.commission.toFixed(2)}</span>
                        </div>
                        <div class="d-flex justify-content-between small fw-semibold">
                            <span>Pago neto al vendedor</span><span class="text-success">$${s.net.toFixed(2)}</span>
                        </div>
                    </div>
                `).join('');
            }
        }

        renderItems();
        renderSummary();

        document.querySelectorAll('input[name="shipping"]').forEach(r => {
            r.addEventListener('change', renderSummary);
        });

        // Formateo de tarjeta
        function formatCard(e) {
            let v = e.target.value.replace(/\D/g,'').substring(0,16);
            e.target.value = v.replace(/(.{4})/g,'$1 ').trim();
        }
        function formatExpiry(e) {
            let v = e.target.value.replace(/\D/g,'').substring(0,4);
            if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2);
            e.target.value = v;
        }
        document.getElementById('cardNumber').addEventListener('input', formatCard);
        document.getElementById('cardExpiry').addEventListener('input', formatExpiry);

        function showBanner(msg, type) {
            const b = document.getElementById('feedbackBanner');
            b.className = `alert alert-${type} rounded-3 small`;
            b.textContent = msg;
            b.classList.remove('d-none');
        }

        // Procesar pedido
        document.getElementById('placeOrderBtn').addEventListener('click', async () => {
            const name    = document.getElementById('shippingName').value.trim();
            const address = document.getElementById('shippingAddress').value.trim();
            const city    = document.getElementById('shippingCity').value.trim();
            const zip     = document.getElementById('shippingZip').value.trim();
            const card    = document.getElementById('cardNumber').value.trim();
            const expiry  = document.getElementById('cardExpiry').value.trim();
            const cvv     = document.getElementById('cardCvv').value.trim();

            if (!name || !address || !city || !zip) {
                showBanner('Completa todos los campos de envío.', 'warning'); return;
            }
            if (!card || !expiry || !cvv) {
                showBanner('Completa los datos de pago.', 'warning'); return;
            }

            const label   = document.getElementById('placeOrderLabel');
            const spinner = document.getElementById('placeOrderSpinner');
            const btn     = document.getElementById('placeOrderBtn');
            label.classList.add('d-none');
            spinner.classList.remove('d-none');
            btn.disabled = true;

            const shipping = document.querySelector('input[name="shipping"]:checked');

            // Construir payload con mapeo flexible de campos
            const payload = {
                user_id:          userId,
                session_token:    sessionToken,
                shipping_address: `${name}, ${address}, ${city} ${zip}`,
                shipping_type:    shipping.value,
                shipping_cost:    parseFloat(shipping.dataset.cost),
                items:            cartItems.map(i => {
                    const product = i.product || i;
                    const qty = i.qty || i.quantity || 1;
                    return {
                        cart_id:    i.cart_id || i.id_carrito || null,
                        product_id: product.id_producto || product.id,
                        qty:        qty,
                        price:      parseFloat(product.price || product.precio || 0),
                        vendor_id: product.vendor_id || product.id_vendedor || product.vendedor?.id_usuario || userId,                    };
                }),
                card_last4: card.replace(/\s/g,'').slice(-4),
            };

            console.log('Enviando payload:', payload);

            try {
                const res = await fetch('/api/orders', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();
                console.log('Respuesta del servidor:', data);

                if (res.ok && data.success) {
                    document.getElementById('successOrderId').textContent = `#${data.order_id.substring(0,8).toUpperCase()}`;

                    const sSection = document.getElementById('successSplitSection');
                    const sList    = document.getElementById('successSplitList');
                    if (data.splits && data.splits.length) {
                        sSection.classList.remove('d-none');
                        sList.innerHTML = data.splits.map(s => `
                            <div class="split-row mb-3">
                                <div class="fw-semibold small">${s.seller}</div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Venta</span><span>$${parseFloat(s.amount).toFixed(2)}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Comisión (10%)</span>
                                    <span class="text-danger">-$${parseFloat(s.commission).toFixed(2)}</span>
                                </div>
                                <div class="d-flex justify-content-between small fw-semibold">
                                    <span>Pago neto</span>
                                    <span class="text-success">$${parseFloat(s.net).toFixed(2)}</span>
                                </div>
                            </div>
                        `).join('');
                    }

                    document.getElementById('successOverlay').classList.add('visible');
                } else {
                    showBanner(data.message ?? 'Error al procesar el pedido.', 'danger');
                    label.classList.remove('d-none');
                    spinner.classList.add('d-none');
                    btn.disabled = false;
                }
            } catch (e) {
                console.error('Error:', e);
                showBanner('Error de conexión con el servidor.', 'danger');
                label.classList.remove('d-none');
                spinner.classList.add('d-none');
                btn.disabled = false;
            }
        });
    });
    </script>
</body>
</html>

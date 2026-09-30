(function () {
    const cart = new Map();

    const productGrid = document.getElementById('productGrid');
    const cartEl = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');
    const discountEl = document.getElementById('discount');
    const errorEl = document.getElementById('checkoutError');
    const checkoutBtn = document.getElementById('checkout');

    const money = (v) => Number(v || 0).toFixed(2);

    productGrid.addEventListener('click', (e) => {
        const card = e.target.closest('.pos-product');
        if (!card || card.classList.contains('out')) return;

        const id = card.dataset.id;
        const item = cart.get(id);

        if (item) {
            if (item.qty >= Number(card.dataset.stock)) return;
            item.qty++;
        } else {
            cart.set(id, {
                id,
                name: card.dataset.name,
                price: Number(card.dataset.price),
                stock: Number(card.dataset.stock),
                qty: 1,
            });
        }

        render();
    });

    cartEl.addEventListener('click', (e) => {
        const btn = e.target.closest('button');
        if (!btn || !btn.dataset.action || !btn.dataset.id) return;

        const item = cart.get(btn.dataset.id);
        if (!item) return;

        if (btn.dataset.action === 'inc' && item.qty < item.stock) item.qty++;
        if (btn.dataset.action === 'dec') {
            item.qty--;
            if (item.qty <= 0) cart.delete(item.id);
        }
        if (btn.dataset.action === 'remove') cart.delete(item.id);

        render();
    });

    discountEl.addEventListener('input', render);

    function render() {
        if (cart.size === 0) {
            cartEl.innerHTML = '<p class="text-muted mb-0">Cart is empty.</p>';
            subtotalEl.textContent = money(0);
            totalEl.textContent = money(0);
            return;
        }

        let subtotal = 0;
        let html = '';

        cart.forEach((item) => {
            const lineTotal = item.price * item.qty;
            subtotal += lineTotal;

            html += `
                <div class="cart-item">
                    <div>
                        <div class="font-weight-bold">${item.name}</div>
                        <small>${money(item.price)} x ${item.qty}</small>
                    </div>
                    <div>
                        <span class="font-weight-bold mr-2">${money(lineTotal)}</span>
                        <span class="qty-controls">
                            <button data-action="dec" data-id="${item.id}" type="button">-</button>
                            <span>${item.qty}</span>
                            <button data-action="inc" data-id="${item.id}" type="button">+</button>
                            <button data-action="remove" data-id="${item.id}" type="button" title="Remove">&#10005;</button>
                        </span>
                    </div>
                </div>`;
        });

        cartEl.innerHTML = html;

        const discount = Math.min(Math.max(Number(discountEl.value || 0), 0), subtotal);
        subtotalEl.textContent = money(subtotal);
        totalEl.textContent = money(subtotal - discount);
    }

    checkoutBtn.addEventListener('click', () => {
        errorEl.textContent = '';

        if (cart.size === 0) {
            errorEl.textContent = 'Cart is empty.';
            return;
        }

        checkoutBtn.disabled = true;
        checkoutBtn.textContent = 'Processing...';

        const items = [];
        cart.forEach((item) => items.push({ product_id: Number(item.id), qty: item.qty }));

        fetch('/pos/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                items,
                customer_name: document.getElementById('customerName').value,
                discount: Number(discountEl.value || 0),
                method: document.getElementById('method').value,
                reference: document.getElementById('reference').value,
            }),
        })
            .then((res) => {
                if (res.redirected) {
                    window.location.href = res.url;
                    return null;
                }
                return res.json().then((data) => Promise.reject(data));
            })
            .catch((err) => {
                checkoutBtn.disabled = false;
                checkoutBtn.textContent = 'Complete Sale';
                if (err && err.message) {
                    errorEl.textContent = err.message;
                } else if (err && err.errors) {
                    errorEl.textContent = Object.values(err.errors).flat().join(' ');
                }
            });
    });
})();
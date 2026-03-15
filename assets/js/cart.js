/**
 * Street2Screen ZA - Cart JavaScript
 * Handles real-time cart interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    initCartQuantityUpdates();
    initCartBadge();
});

/**
 * Real-time quantity updates with debounce
 */
function initCartQuantityUpdates() {
    document.querySelectorAll('.cart-quantity-input').forEach(function(input) {
        let debounceTimer;
        input.addEventListener('change', function() {
            clearTimeout(debounceTimer);
            const cartId = this.dataset.cartId;
            const quantity = parseInt(this.value);
            const max = parseInt(this.max);
            const row = this.closest('.cart-item-row');

            // Validate quantity
            if (quantity < 1) {
                this.value = 1;
                return;
            }
            if (quantity > max) {
                this.value = max;
                showCartAlert('Maximum available stock is ' + max, 'warning');
                return;
            }

            debounceTimer = setTimeout(() => {
                updateCartItem(cartId, quantity, row);
            }, 500);
        });
    });
}

/**
 * Update cart item via AJAX
 */
function updateCartItem(cartId, quantity, row) {
    const priceEl = row ? row.querySelector('.item-unit-price') : null;
    const totalEl = row ? row.querySelector('.item-total-price') : null;
    const unitPrice = priceEl ? parseFloat(priceEl.dataset.price) : 0;

    fetch(APP_URL + '/orders/cart-update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'cart_id=' + cartId + '&quantity=' + quantity
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Update item total in UI
            if (totalEl && unitPrice) {
                totalEl.textContent = 'R ' + (unitPrice * quantity).toFixed(2);
            }
            updateCartTotals();
            showCartAlert('Cart updated!', 'success');
        }
    })
    .catch(() => showCartAlert('Failed to update cart. Please refresh.', 'danger'));
}

/**
 * Recalculate cart totals from DOM
 */
function updateCartTotals() {
    let subtotal = 0;
    document.querySelectorAll('.item-total-price').forEach(function(el) {
        const val = parseFloat(el.textContent.replace('R ', '').replace(' ', ''));
        if (!isNaN(val)) subtotal += val;
    });

    const subtotalEl = document.getElementById('cart-subtotal');
    const totalEl = document.getElementById('cart-total');

    if (subtotalEl) subtotalEl.textContent = 'R ' + subtotal.toFixed(2);
    if (totalEl) totalEl.textContent = 'R ' + subtotal.toFixed(2);
}

/**
 * Update cart badge in navbar
 */
function initCartBadge() {
    fetch(APP_URL + '/orders/cart-count.php')
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('cart-badge');
            if (badge) {
                badge.textContent = data.count || 0;
                badge.style.display = data.count > 0 ? 'inline' : 'none';
            }
        })
        .catch(() => {});
}

/**
 * Show temporary cart alert
 */
function showCartAlert(message, type = 'info') {
    const existing = document.getElementById('cart-alert');
    if (existing) existing.remove();

    const alert = document.createElement('div');
    alert.id = 'cart-alert';
    alert.className = `alert alert-${type} alert-dismissible position-fixed`;
    alert.style.cssText = 'top:80px;right:20px;z-index:9999;min-width:250px';
    alert.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(alert);

    setTimeout(() => { if (alert.parentNode) alert.remove(); }, 3000);
}

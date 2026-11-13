document.addEventListener('DOMContentLoaded', function () {
    var BASE = window.SITE_URL || '';

    document.querySelectorAll('.add-to-cart-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = form.querySelector('button[type="submit"]');
            var originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Adding…';

            fetch(BASE + '/actions/add_to_cart.php', { method: 'POST', body: new FormData(form) })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    showToast(data.message || (data.success ? 'Added to cart.' : 'Something went wrong.'), data.success ? 'success' : 'error');
                    if (data.success) {
                        updateCartBadge(data.cart_count);
                    }
                })
                .catch(function () {
                    showToast('Something went wrong. Please try again.', 'error');
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
        });
    });

    document.querySelectorAll('.qty-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = btn.closest('.cart-row');
            var input = row.querySelector('.qty-input');
            var qty = parseInt(input.value, 10) || 1;
            qty = btn.dataset.action === 'increase' ? qty + 1 : qty - 1;
            if (qty < 1) qty = 1;
            input.value = qty;
            updateCartItem(row.dataset.cartItemId, qty);
        });
    });

    document.querySelectorAll('.qty-input').forEach(function (input) {
        input.addEventListener('change', function () {
            var row = input.closest('.cart-row');
            var qty = parseInt(input.value, 10);
            if (isNaN(qty) || qty < 1) qty = 1;
            input.value = qty;
            updateCartItem(row.dataset.cartItemId, qty);
        });
    });

    document.querySelectorAll('.remove-item-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!window.confirm('Remove this item from your cart?')) return;
            var row = btn.closest('.cart-row');
            var formData = new FormData();
            formData.append('cart_item_id', row.dataset.cartItemId);
            formData.append('csrf_token', window.CSRF_TOKEN);
            fetch(BASE + '/actions/remove_from_cart.php', { method: 'POST', body: formData })
                .then(function () { window.location.reload(); });
        });
    });

    function updateCartItem(cartItemId, quantity) {
        var formData = new FormData();
        formData.append('cart_item_id', cartItemId);
        formData.append('quantity', quantity);
        formData.append('csrf_token', window.CSRF_TOKEN);
        fetch(BASE + '/actions/update_cart.php', { method: 'POST', body: formData })
            .then(function () { window.location.reload(); });
    }

    var navToggle = document.querySelector('.nav-toggle');
    var mainNav = document.querySelector('.main-nav');
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function () {
            var isOpen = mainNav.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    document.querySelectorAll('.nav-dropdown-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            if (window.matchMedia('(max-width: 760px)').matches) {
                trigger.closest('.nav-dropdown').classList.toggle('is-open');
            }
        });
    });

    document.querySelectorAll('.flash-message').forEach(function (msg) {
        setTimeout(function () {
            msg.classList.add('fade-out');
            setTimeout(function () { msg.remove(); }, 400);
        }, 4500);
    });

    function updateCartBadge(count) {
        var badge = document.querySelector('.cart-badge');
        if (!badge) return;
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline-flex' : 'none';
    }

    function showToast(message, type) {
        var container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        var toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(function () {
            toast.classList.add('fade-out');
            setTimeout(function () { toast.remove(); }, 400);
        }, 3200);
    }
});

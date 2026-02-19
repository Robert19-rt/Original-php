const Cart = {
    get: () => JSON.parse(localStorage.getItem('cart') || '{"items":[]}'),
    set: cart => localStorage.setItem('cart', JSON.stringify(cart)),
    addItem(productId, size, qty = 1) {
        const cart = this.get();
        const item = cart.items.find(i => i.product_id === productId && i.size === size);
        if (item) {
            item.quantity = Math.max(1, item.quantity + qty);
        } else {
            cart.items.push({ product_id: productId, size: parseFloat(size), quantity: qty });
        }
        this.set(cart);
        this.updateBadge();
    },
    removeItem(productId, size) {
        const cart = this.get();
        cart.items = cart.items.filter(i => !(i.product_id === productId && i.size === size));
        this.set(cart);
        this.updateBadge();
    },
    updateBadge() {
        const count = this.get().items.reduce((sum, i) => sum + i.quantity, 0);
        document.querySelectorAll('.cart-count').forEach(el => {
            el.textContent = count || '';
            el.style.display = count ? 'inline-block' : 'none';
        });
    },
    mergeOnLogin() {
        const cart = this.get();
        if (cart.items.length > 0) {
            fetch('/api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(cart)
            })
            .then(() => localStorage.removeItem('cart'))
            .then(() => location.reload())
            .catch(err => console.error('Ошибка слияния:', err));
        }
    }
};

document.addEventListener('click', e => {
    if (e.target.classList.contains('btn-add-to-cart')) {
        const productId = parseInt(e.target.dataset.productId);
        const sizeEl = document.querySelector('input[name="size"]:checked');
        if (!sizeEl) {
            alert('Пожалуйста, выберите размер');
            return;
        }
        const size = parseFloat(sizeEl.value);
        Cart.addItem(productId, size);
        alert('✅ Товар добавлен в корзину!');
    }
});

document.addEventListener('click', e => {
    if (e.target.classList.contains('btn-remove-from-cart')) {
        const productId = parseInt(e.target.dataset.productId);
        const size = parseFloat(e.target.dataset.size);
        if (confirm('Удалить товар из корзины?')) {
            Cart.removeItem(productId, size);
            location.reload();
        }
    }
});

document.addEventListener('DOMContentLoaded', () => Cart.updateBadge());

document.addEventListener('click', e => {
    if (e.target.classList.contains('gallery-img')) {
        const mainImg = document.getElementById('main-img');
        mainImg.src = e.target.src;
        document.querySelectorAll('.gallery-img').forEach(img => img.classList.remove('active'));
        e.target.classList.add('active');
    }
});
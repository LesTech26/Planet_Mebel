let cart = [];
let deliveryPrice = 0;

function loadCart() {
    cart = getCart();
    renderOrderSummary();
}

function renderOrderSummary() {
    const summary = document.getElementById('orderSummary');
    if (!summary) return;
    
    if (cart.length === 0) {
        summary.innerHTML = '<div class="empty-cart" style="text-align:center;padding:40px"><p>Корзина пуста</p><a href="catalog.html" class="btn-primary">Перейти в каталог</a></div>';
        document.getElementById('submitOrder').disabled = true;
        return;
    }
    
    let subtotal = 0;
    const itemsHtml = cart.map(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        return `
            <div class="order-item">
                <div class="order-item-img" style="background-image: url('${item.image || 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=50&q=80'}')"></div>
                <div class="order-item-info">
                    <div class="order-item-name">${item.name}</div>
                    <div class="order-item-price">${formatPrice(item.price)} × ${item.quantity}</div>
                </div>
                <div>${formatPrice(itemTotal)}</div>
            </div>
        `;
    }).join('');
    
    const total = subtotal + deliveryPrice;
    
    summary.innerHTML = `
        <h3 class="summary-title">Ваш заказ</h3>
        <div class="order-items-list">${itemsHtml}</div>
        <div class="summary-row"><span>Товары</span><span>${formatPrice(subtotal)}</span></div>
        <div class="summary-row"><span>Доставка</span><span>${deliveryPrice === 0 ? 'Бесплатно' : formatPrice(deliveryPrice)}</span></div>
        <div class="summary-row total"><span>Итого</span><span>${formatPrice(total)}</span></div>
    `;
}

function updateDelivery() {
    const selected = document.querySelector('input[name="delivery"]:checked')?.value;
    const subtotal = cart.reduce((s, i) => s + i.price * i.quantity, 0);
    
    if (selected === 'courier') {
        deliveryPrice = subtotal >= 10000 ? 0 : 500;
        document.getElementById('addressGroup').style.display = 'block';
    } else {
        deliveryPrice = 0;
        document.getElementById('addressGroup').style.display = 'none';
    }
    
    renderOrderSummary();
}

function validateForm() {
    let isValid = true;
    
    const fullname = document.getElementById('fullname')?.value.trim();
    const phone = document.getElementById('phone')?.value.trim();
    const email = document.getElementById('email')?.value.trim();
    
    if (!fullname) {
        document.getElementById('fullname')?.classList.add('error-field');
        isValid = false;
    } else {
        document.getElementById('fullname')?.classList.remove('error-field');
    }
    
    if (!phone) {
        document.getElementById('phone')?.classList.add('error-field');
        isValid = false;
    } else {
        document.getElementById('phone')?.classList.remove('error-field');
    }
    
    if (!email || !email.includes('@')) {
        document.getElementById('email')?.classList.add('error-field');
        isValid = false;
    } else {
        document.getElementById('email')?.classList.remove('error-field');
    }
    
    const delivery = document.querySelector('input[name="delivery"]:checked')?.value;
    if (delivery === 'courier') {
        const address = document.getElementById('address')?.value.trim();
        if (!address) {
            document.getElementById('address')?.classList.add('error-field');
            isValid = false;
        } else {
            document.getElementById('address')?.classList.remove('error-field');
        }
    }
    
    return isValid;
}

function submitOrder() {
    if (!validateForm()) {
        showToast('Заполните все обязательные поля', false);
        return;
    }
    
    if (cart.length === 0) {
        showToast('Корзина пуста', false);
        return;
    }
    
    const orderData = {
        items: cart,
        customer: {
            fullname: document.getElementById('fullname').value,
            phone: document.getElementById('phone').value,
            email: document.getElementById('email').value
        },
        delivery: document.querySelector('input[name="delivery"]:checked')?.value,
        address: document.getElementById('address')?.value || '',
        payment: document.querySelector('input[name="payment"]:checked')?.value,
        comment: document.getElementById('comment')?.value || '',
        total: cart.reduce((s, i) => s + i.price * i.quantity, 0) + deliveryPrice
    };
    
    const orderId = 'PM-' + Date.now();
    localStorage.removeItem('pm_cart');
    updateCartCount();
    
    window.location.href = `order-success.html?order_id=${orderId}`;
}

document.addEventListener('DOMContentLoaded', () => {
    loadCart();
    
    document.querySelectorAll('input[name="delivery"]').forEach(radio => {
        radio.addEventListener('change', updateDelivery);
    });
    
    document.getElementById('submitOrder')?.addEventListener('click', submitOrder);
    
    // Load user data if logged in
    const user = getUser();
    if (user) {
        if (document.getElementById('fullname')) document.getElementById('fullname').value = user.name || '';
        if (document.getElementById('email')) document.getElementById('email').value = user.email || '';
        if (document.getElementById('phone')) document.getElementById('phone').value = user.phone || '';
        if (document.getElementById('address')) document.getElementById('address').value = user.address || '';
    }
});
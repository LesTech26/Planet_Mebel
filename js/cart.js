function renderCart() {
    const cart = getCart();
    const emptyCart = document.getElementById('emptyCart');
    const cartContent = document.getElementById('cartContent');
    
    if (cart.length === 0) {
        if (emptyCart) emptyCart.style.display = 'block';
        if (cartContent) cartContent.style.display = 'none';
        return;
    }
    
    if (emptyCart) emptyCart.style.display = 'none';
    if (cartContent) cartContent.style.display = 'grid';
    
    let subtotal = 0;
    const rowsHtml = cart.map((item, index) => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        return `
            <tr>
                <td>
                    <div class="cart-product">
                        <div class="cart-product-img" style="background-image: url('${item.image || 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=70&q=80'}')"></div>
                        <div class="cart-product-info">
                            <h4>${item.name}</h4>
                            ${item.options?.color || item.options?.material ? `<div class="cart-product-options">${[item.options.color, item.options.material].filter(Boolean).join(' · ')}</div>` : ''}
                        </div>
                    </div>
                </td>
                <td>${formatPrice(item.price)}</td>
                <td>
                    <div class="cart-quantity">
                        <button class="cart-qty-btn" onclick="changeQuantity(${index}, -1)">−</button>
                        <input type="text" class="cart-qty-input" value="${item.quantity}" readonly>
                        <button class="cart-qty-btn" onclick="changeQuantity(${index}, 1)">+</button>
                    </div>
                </td>
                <td>${formatPrice(itemTotal)}</td>
                <td><button class="cart-remove" onclick="removeCartItem(${index})">✕</button></td>
            </tr>
        `;
    }).join('');
    
    document.getElementById('cartTableBody').innerHTML = rowsHtml;
    
    const deliveryCost = subtotal >= 10000 ? 0 : 500;
    const total = subtotal + deliveryCost;
    
    document.getElementById('cartSummary').innerHTML = `
        <h3 class="summary-title">Итого</h3>
        <div class="summary-row"><span>Товары (${cart.reduce((s, i) => s + i.quantity, 0)} шт.)</span><span>${formatPrice(subtotal)}</span></div>
        <div class="summary-row"><span>Доставка</span><span>${deliveryCost === 0 ? 'Бесплатно' : formatPrice(deliveryCost)}</span></div>
        <div class="summary-row total"><span>Итого</span><span>${formatPrice(total)}</span></div>
        <button class="checkout-btn" onclick="location.href='checkout.html'">Оформить заказ</button>
    `;
}

function changeQuantity(index, delta) {
    const cart = getCart();
    const newQty = cart[index].quantity + delta;
    if (newQty < 1) {
        cart.splice(index, 1);
    } else {
        cart[index].quantity = newQty;
    }
    saveCart(cart);
    updateCartCount();
    renderCart();
}

function removeCartItem(index) {
    const cart = getCart();
    cart.splice(index, 1);
    saveCart(cart);
    updateCartCount();
    renderCart();
    showToast('Товар удалён из корзины');
}

document.addEventListener('DOMContentLoaded', renderCart);

window.changeQuantity = changeQuantity;
window.removeCartItem = removeCartItem;
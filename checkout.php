<?php
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

// Функция форматирования цены (если нет в functions.php)
if (!function_exists('formatPrice')) {
    function formatPrice($price) {
        return number_format($price, 0, '', ' ') . ' ₽';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Оформление заказа — Планета Мебели</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --cream: #F5F0E8; --warm: #FDFAF5; --walnut: #7C5C3E; --walnut-l: #A07850; --charcoal: #2A2420; --mid: #8C7B6E; --line: rgba(124,92,62,0.18); --gold: #C9A96E; --green: #4A7C59; --red: #B04040; }
html, body { min-height: 100vh; display: flex; flex-direction: column; }
body { font-family: 'Jost', sans-serif; background: var(--warm); color: var(--charcoal); font-weight: 300; font-size: 15px; line-height: 1.5; overflow-x: hidden; width: 100%; flex: 1; display: flex; flex-direction: column; }
main { flex: 1; }
footer { margin-top: auto; }

/* HEADER */
.header { position: fixed; top: 0; left: 0; right: 0; z-index: 200; background: rgba(253,250,245,0.96); backdrop-filter: blur(14px); border-bottom: 1px solid var(--line); height: 65px; display: flex; align-items: center; width: 100%; }
.hinner { width: 100%; max-width: 1400px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; gap: 15px; }
.logo { font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 600; letter-spacing: 0.04em; color: var(--charcoal); text-decoration: none; white-space: nowrap; flex-shrink: 0; }
.logo span { color: var(--walnut); font-style: italic; }
.nav { display: flex; gap: 25px; list-style: none; margin: 0; padding: 0; }
.nav a { text-decoration: none; color: var(--charcoal); font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 400; transition: color 0.2s; white-space: nowrap; }
.nav a:hover { color: var(--walnut); }
.hactions { display: flex; align-items: center; gap: 15px; flex-shrink: 0; }
.search-wrap { position: relative; }
.search-wrap input { background: transparent; border: none; border-bottom: 1px solid var(--line); padding: 5px 25px 5px 0; width: 140px; font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 300; color: var(--charcoal); outline: none; }
.search-icon { position: absolute; right: 4px; top: 50%; transform: translateY(-50%); color: var(--mid); font-size: 14px; cursor: pointer; }
.cart-btn { background: none; border: none; cursor: pointer; color: var(--charcoal); display: flex; align-items: center; gap: 5px; font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; }
.cart-count { background: var(--walnut); color: #fff; width: 18px; height: 18px; border-radius: 50%; font-size: 10px; display: flex; align-items: center; justify-content: center; }
.profile-link { color: var(--charcoal); text-decoration: none; font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; white-space: nowrap; }

/* CHECKOUT PAGE */
.checkout-page { padding-top: 85px; max-width: 1400px; margin: 0 auto; padding-left: 20px; padding-right: 20px; width: 100%; min-height: 60vh; }
.checkout-title { font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 300; margin-bottom: 30px; }
.checkout-layout { display: grid; grid-template-columns: 1fr 380px; gap: 40px; }
.form-section { background: var(--cream); padding: 28px; margin-bottom: 24px; border-radius: 16px; }
.form-section h2 { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 400; margin-bottom: 20px; }
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.form-group label { font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--mid); }
.form-group input, .form-group textarea { padding: 12px; border: 1px solid var(--line); background: var(--warm); font-family: 'Jost', sans-serif; font-size: 14px; border-radius: 8px; }
.form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--walnut); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.delivery-options, .payment-options { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }
.delivery-options label, .payment-options label { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 1px solid var(--line); cursor: pointer; background: var(--warm); border-radius: 8px; transition: all 0.2s; }
.delivery-options label:hover, .payment-options label:hover { border-color: var(--walnut); }
.order-summary { background: var(--cream); padding: 28px; height: fit-content; position: sticky; top: 85px; border-radius: 16px; }
.order-items-list { max-height: 300px; overflow-y: auto; margin-bottom: 20px; }
.order-item { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--line); }
.order-item-img { width: 50px; height: 50px; background-size: cover; background-position: center; flex-shrink: 0; border-radius: 8px; }
.order-item-name { font-family: 'Cormorant Garamond', serif; font-size: 14px; font-weight: 400; }
.order-item-price { font-size: 13px; color: var(--walnut); }
.summary-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--line); }
.summary-row.total { font-size: 18px; font-weight: 500; border-bottom: none; padding-top: 16px; margin-top: 8px; }
.submit-order { width: 100%; margin-top: 24px; background: var(--walnut); color: #fff; padding: 16px; border: none; font-size: 13px; letter-spacing: 0.15em; text-transform: uppercase; cursor: pointer; border-radius: 30px; transition: background 0.2s; }
.submit-order:hover { background: var(--walnut-l); }

/* FOOTER */
footer { background: var(--charcoal); color: rgba(255,255,255,0.72); font-size: 12px; width: 100%; }
.footer-inner { max-width: 1400px; margin: 0 auto; padding: 35px 20px 20px; }
.footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 25px; margin-bottom: 25px; }
.footer-logo { font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 600; color: #fff; margin-bottom: 10px; }
.footer-logo span { color: var(--gold); font-style: italic; }
.fcol h4 { color: #fff; font-size: 10px; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 12px; font-weight: 500; }
.fcol ul { list-style: none; }
.fcol li { margin-bottom: 6px; }
.fcol a { color: rgba(255,255,255,0.58); text-decoration: none; font-size: 11px; }
.fcol a:hover { color: var(--gold); }
.sub-form { display: flex; margin-top: 6px; }
.sub-form input { flex: 1; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.15); border-right: none; padding: 8px 10px; color: #fff; font-size: 11px; }
.sub-form button { background: var(--walnut); border: none; color: #fff; padding: 8px 14px; cursor: pointer; font-size: 11px; }
.footer-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px; font-size: 10px; }
.socials { display: flex; gap: 6px; margin-top: 10px; }
.social-link { width: 28px; height: 28px; border: 1px solid rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.55); text-decoration: none; font-size: 10px; border-radius: 50%; }

/* TOAST */
.toast { position: fixed; bottom: 20px; right: 20px; z-index: 999; background: var(--charcoal); color: #fff; padding: 8px 16px; font-size: 12px; transform: translateY(100px); opacity: 0; transition: all 0.35s ease; display: flex; align-items: center; gap: 6px; border-radius: 4px; }
.toast.show { transform: translateY(0); opacity: 1; }
.toast-icon { color: var(--gold); }

@media (max-width: 900px) { .checkout-layout { grid-template-columns: 1fr; } .order-summary { position: static; } .form-row { grid-template-columns: 1fr; } .checkout-page { padding-left: 15px; padding-right: 15px; } }
@media (max-width: 768px) { .nav { display: none; } .checkout-page { padding-top: 75px; } .checkout-title { font-size: 28px; } }
@media (max-width: 600px) { .logo { font-size: 17px; } .profile-link { display: none; } .cart-btn span:not(.cart-count) { display: none; } .search-wrap { display: none; } .form-section { padding: 20px; } }
@media (max-width: 480px) { .hinner { padding: 0 12px; } .cart-count { width: 20px; height: 20px; font-size: 11px; } .checkout-page { padding-left: 12px; padding-right: 12px; } }
</style>
</head>
<body>

<header class="header">
  <div class="hinner">
    <a href="index.php" class="logo">Планета <span>Мебели</span></a>
    <ul class="nav">
      <li><a href="catalog.php">Каталог</a></li>
      <li><a href="collections.php">Коллекции</a></li>
      <li><a href="designers.php">Дизайнерам</a></li>
      <li><a href="about.php">О нас</a></li>
    </ul>
    <div class="hactions">
      <div class="search-wrap">
        <input type="text" placeholder="Поиск...">
        <span class="search-icon">⌕</span>
      </div>
      <a href="profile.php" class="profile-link" id="profileLink"><?= $userName ? htmlspecialchars($userName) : 'Войти' ?></a>
      <button class="cart-btn" onclick="location.href='cart.php'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <span class="cart-count" id="cartCount">0</span>
      </button>
    </div>
  </div>
</header>

<main class="checkout-page">
    <h1 class="checkout-title">Оформление заказа</h1>
    <div id="checkoutContainer">
        <div class="spinner" style="text-align: center; padding: 50px;">Загрузка...</div>
    </div>
</main>

<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div><div class="footer-logo">Планета <span>Мебели</span></div><p style="font-size: 11px;">Мебель для вашего дома</p><div class="socials"><a href="#" class="social-link">vk</a><a href="#" class="social-link">tg</a><a href="#" class="social-link">in</a><a href="#" class="social-link">yt</a></div></div>
      <div class="fcol"><h4>Каталог</h4><ul><li><a href="catalog.php">Диваны</a></li><li><a href="catalog.php">Кровати</a></li><li><a href="catalog.php">Столы</a></li><li><a href="catalog.php">Шкафы</a></li></ul></div>
      <div class="fcol"><h4>Компания</h4><ul><li><a href="about.php">О нас</a></li><li><a href="delivery.php">Доставка</a></li><li><a href="contacts.php">Контакты</a></li></ul></div>
      <div class="fcol"><h4>Подписка</h4><form class="sub-form" id="subFormFooter"><input type="email" id="subEmailFooter" placeholder="ваш@email.ru"><button type="submit">→</button></form><p id="subMsgFooter" style="font-size:10px;margin-top:6px;color:var(--gold);display:none">✓ Вы подписались!</p><p id="subErrFooter" style="font-size:10px;margin-top:6px;color:#e07070;display:none">Введите email</p></div>
    </div>
    <div class="footer-bottom"><span>© 2026 Планета Мебели</span><span>Политика конфиденциальности</span></div>
  </div>
</footer>

<div class="toast" id="toast"><span class="toast-icon">✓</span><span id="toastMsg"></span></div>

<script>
const getCart = () => JSON.parse(localStorage.getItem('pm_cart') || '[]');

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
}

function showToast(msg, isSuccess = true) {
    const toast = document.getElementById('toast');
    const msgSpan = document.getElementById('toastMsg');
    if (msgSpan) msgSpan.textContent = msg;
    if (toast) {
        toast.classList.add('show');
        clearTimeout(toast._timeout);
        toast._timeout = setTimeout(() => toast.classList.remove('show'), 3000);
    }
}

function updateCartCount() {
    const cart = getCart();
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    const el = document.getElementById('cartCount');
    if (el) el.textContent = count;
}

function getImageUrl(image) {
    if (!image || image === '') return '/uploads/no-image.webp';
    if (image.indexOf('http://') === 0 || image.indexOf('https://') === 0) return image;
    if (image.indexOf('/uploads/') === 0) return image;
    return '/uploads/products/' + image;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function renderCheckout() {
    const cart = getCart();
    const container = document.getElementById('checkoutContainer');
    
    if (!container) return;
    
    if (cart.length === 0) {
        container.innerHTML = `
            <div class="empty-cart" style="text-align: center; padding: 60px;">
                <div class="empty-icon" style="font-size: 72px; margin-bottom: 20px;">🛒</div>
                <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 300; margin-bottom: 12px;">Корзина пуста</h2>
                <p style="color: var(--mid); margin-bottom: 20px;">Добавьте товары из каталога</p>
                <a href="catalog.php" class="btn-primary" style="display: inline-block; padding: 12px 28px; background: var(--walnut); color: #fff; text-decoration: none; border-radius: 30px;">Перейти в каталог</a>
            </div>
        `;
        return;
    }
    
    let subtotal = 0;
    let itemsHtml = '';
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        const imageUrl = getImageUrl(item.image);
        
        itemsHtml += `
            <div class="order-item">
                <div class="order-item-img" style="background-image: url('${imageUrl}')"></div>
                <div class="order-item-info">
                    <div class="order-item-name">${escapeHtml(item.name)}</div>
                    <div class="order-item-price">${formatPrice(item.price)} × ${item.quantity}</div>
                </div>
                <div>${formatPrice(itemTotal)}</div>
            </div>
        `;
    });
    
    const deliveryPrice = subtotal >= 10000 ? 0 : 500;
    const total = subtotal + deliveryPrice;
    
    const userData = JSON.parse(localStorage.getItem('pm_user') || '{}');
    
    container.innerHTML = `
        <div class="checkout-layout">
            <div class="checkout-form">
                <form id="checkoutForm">
                    <div class="form-section">
                        <h2>Контактные данные</h2>
                        <div class="form-group"><label>ФИО *</label><input type="text" name="fullname" id="fullname" value="${escapeHtml(userData.name || '')}" required></div>
                        <div class="form-row">
                            <div class="form-group"><label>Телефон *</label><input type="tel" name="phone" id="phone" value="${escapeHtml(userData.phone || '')}" required></div>
                            <div class="form-group"><label>Email *</label><input type="email" name="email" id="email" value="${escapeHtml(userData.email || '')}" required></div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h2>Доставка</h2>
                        <div class="delivery-options">
                            <label><input type="radio" name="delivery" value="pickup" checked onchange="updateDeliveryPrice()"> Самовывоз (бесплатно)</label>
                            <label><input type="radio" name="delivery" value="courier" onchange="updateDeliveryPrice()"> Доставка курьером (500 ₽)</label>
                        </div>
                        <div class="form-group" id="addressGroup" style="display: none;">
                            <label>Адрес доставки</label>
                            <textarea name="address" rows="2" placeholder="Город, улица, дом, квартира">${escapeHtml(userData.address || '')}</textarea>
                        </div>
                    </div>
                    <div class="form-section">
                        <h2>Оплата</h2>
                        <div class="payment-options">
                            <label><input type="radio" name="payment" value="card" checked> Карта онлайн</label>
                            <label><input type="radio" name="payment" value="cash"> Наличные курьеру</label>
                        </div>
                    </div>
                    <div class="form-section">
                        <h2>Комментарий</h2>
                        <textarea name="comment" rows="2" placeholder="Пожелания к заказу"></textarea>
                    </div>
                    <button type="submit" class="submit-order">Оформить заказ</button>
                </form>
            </div>
            <div class="order-summary">
                <h3 class="summary-title">Ваш заказ</h3>
                <div class="order-items-list">
                    ${itemsHtml}
                </div>
                <div class="summary-row"><span>Товары</span><span id="subtotalSpan">${formatPrice(subtotal)}</span></div>
                <div class="summary-row"><span>Доставка</span><span id="deliverySpan">${deliveryPrice === 0 ? 'Бесплатно' : formatPrice(deliveryPrice)}</span></div>
                <div class="summary-row total"><span>Итого</span><span id="totalSpan">${formatPrice(total)}</span></div>
            </div>
        </div>
    `;
    
    // Добавляем обработчик изменения способа доставки
    document.querySelectorAll('input[name="delivery"]').forEach(radio => {
        radio.addEventListener('change', () => updateDeliveryPrice());
    });
    
    // Инициализируем отображение адреса
    toggleAddress();
}

function updateDeliveryPrice() {
    const cart = getCart();
    const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const selectedDelivery = document.querySelector('input[name="delivery"]:checked')?.value;
    const deliveryPrice = (selectedDelivery === 'courier' && subtotal < 10000) ? 500 : 0;
    const total = subtotal + deliveryPrice;
    
    const deliverySpan = document.getElementById('deliverySpan');
    const totalSpan = document.getElementById('totalSpan');
    const subtotalSpan = document.getElementById('subtotalSpan');
    
    if (subtotalSpan) subtotalSpan.textContent = formatPrice(subtotal);
    if (deliverySpan) deliverySpan.textContent = deliveryPrice === 0 ? 'Бесплатно' : formatPrice(deliveryPrice);
    if (totalSpan) totalSpan.textContent = formatPrice(total);
    
    toggleAddress();
}

function toggleAddress() {
    const addressGroup = document.getElementById('addressGroup');
    const selectedDelivery = document.querySelector('input[name="delivery"]:checked')?.value;
    if (addressGroup) {
        addressGroup.style.display = selectedDelivery === 'courier' ? 'block' : 'none';
    }
}

// Обработка отправки формы
document.addEventListener('submit', function(e) {
    if (e.target.id === 'checkoutForm') {
        e.preventDefault();
        
        const fullname = document.getElementById('fullname')?.value.trim();
        const phone = document.getElementById('phone')?.value.trim();
        const email = document.getElementById('email')?.value.trim();
        
        if (!fullname || !phone || !email) {
            showToast('Заполните все обязательные поля', false);
            return;
        }
        
        if (!email.includes('@') || !email.includes('.')) {
            showToast('Введите корректный email', false);
            return;
        }
        
        const cart = getCart();
        if (cart.length === 0) {
            showToast('Корзина пуста', false);
            return;
        }
        
        // Сохраняем данные пользователя в localStorage
        const userData = {
            name: fullname,
            phone: phone,
            email: email,
            address: document.getElementById('addressGroup')?.querySelector('textarea')?.value || ''
        };
        localStorage.setItem('pm_user', JSON.stringify(userData));
        
        // Получаем данные для отправки на сервер
        const deliveryMethod = document.querySelector('input[name="delivery"]:checked')?.value;
        const paymentMethod = document.querySelector('input[name="payment"]:checked')?.value;
        const comment = document.querySelector('textarea[name="comment"]')?.value || '';
        const address = document.getElementById('addressGroup')?.querySelector('textarea')?.value || '';
        
        const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        const deliveryPrice = (deliveryMethod === 'courier' && subtotal < 10000) ? 500 : 0;
        const total = subtotal + deliveryPrice;
        
        // Отправляем заказ на сервер
        const orderData = {
            fullname: fullname,
            phone: phone,
            email: email,
            address: address,
            delivery_method: deliveryMethod,
            payment_method: paymentMethod,
            comment: comment,
            subtotal: subtotal,
            delivery_price: deliveryPrice,
            total: total,
            items: cart.map(item => ({
                product_id: item.productId,
                product_name: item.name,
                price: item.price,
                quantity: item.quantity,
                options: item.options || {}
            }))
        };
        
        showToast('Оформление заказа...');
        
        fetch('/api/create-order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Очищаем корзину
                localStorage.removeItem('pm_cart');
                updateCartCount();
                
                showToast('Заказ оформлен! Спасибо за покупку!');
                
                // Перенаправляем на страницу успеха
                setTimeout(() => {
                    window.location.href = 'order-success.php?order_id=' + data.order_number;
                }, 1500);
            } else {
                showToast(data.error || 'Ошибка при оформлении заказа', false);
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Ошибка сервера. Попробуйте позже.', false);
        });
    }
});

// Подписка
document.getElementById('subFormFooter')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('subEmailFooter').value.trim();
    const msg = document.getElementById('subMsgFooter');
    const err = document.getElementById('subErrFooter');
    if (msg) msg.style.display = 'none';
    if (err) err.style.display = 'none';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        if (err) err.style.display = 'block';
        return;
    }
    if (msg) msg.style.display = 'block';
    document.getElementById('subEmailFooter').value = '';
});

// Инициализация
document.addEventListener('DOMContentLoaded', () => {
    renderCheckout();
    updateCartCount();
    
    const user = JSON.parse(localStorage.getItem('pm_user') || 'null');
    const profileLink = document.getElementById('profileLink');
    if (profileLink && user) profileLink.textContent = user.name?.split(' ')[0] || 'Войти';
});

window.updateDeliveryPrice = updateDeliveryPrice;
window.toggleAddress = toggleAddress;
</script>
</body>
</html>
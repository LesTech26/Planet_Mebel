<?php
session_start();

$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

// Функция для получения корректного URL картинки
function getImageUrl($image) {
    if (empty($image)) {
        return '/uploads/no-image.webp';
    }
    if (strpos($image, 'http://') === 0 || strpos($image, 'https://') === 0) {
        return $image;
    }
    if (strpos($image, '/uploads/') === 0) {
        return $image;
    }
    return '/uploads/products/' . $image;
}

// Функция форматирования цены
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
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
<title>Корзина — Планета Мебели</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --cream: #F5F0E8;
    --warm: #FDFAF5;
    --walnut: #7C5C3E;
    --walnut-l: #A07850;
    --charcoal: #2A2420;
    --mid: #8C7B6E;
    --line: rgba(124, 92, 62, 0.18);
    --gold: #C9A96E;
    --green: #4A7C59;
    --red: #B04040;
}

html, body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

body {
    font-family: 'Jost', sans-serif;
    background: var(--warm);
    color: var(--charcoal);
    font-weight: 400;
    font-size: 17px;
    line-height: 1.55;
    overflow-x: hidden;
    width: 100%;
    flex: 1;
    display: flex;
    flex-direction: column;
}

main {
    flex: 1;
}

footer {
    margin-top: auto;
}

/* HEADER */
.header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 200;
    background: rgba(253, 250, 245, 0.96);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--line);
    height: 76px;
    display: flex;
    align-items: center;
    width: 100%;
}

.hinner {
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
}

.logo {
    font-family: 'Cormorant Garamond', serif;
    font-size: 26px;
    font-weight: 600;
    letter-spacing: 0.04em;
    color: var(--charcoal);
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
}

.logo span {
    color: var(--walnut);
    font-style: italic;
}

/* ДЕСКТОПНОЕ МЕНЮ */
.nav {
    display: flex;
    gap: 32px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav a {
    text-decoration: none;
    color: var(--charcoal);
    font-size: 15px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-weight: 500;
    transition: color 0.2s;
    white-space: nowrap;
}

.nav a:hover {
    color: var(--walnut);
}

/* БУРГЕР-МЕНЮ */
.burger-menu {
    display: none;
    flex-direction: column;
    justify-content: space-between;
    width: 30px;
    height: 21px;
    cursor: pointer;
    z-index: 210;
}

.burger-menu span {
    display: block;
    width: 100%;
    height: 2px;
    background: var(--charcoal);
    transition: all 0.3s ease;
    border-radius: 2px;
}

.burger-menu.active span:nth-child(1) {
    transform: translateY(9px) rotate(45deg);
}

.burger-menu.active span:nth-child(2) {
    opacity: 0;
}

.burger-menu.active span:nth-child(3) {
    transform: translateY(-9px) rotate(-45deg);
}

/* МОБИЛЬНОЕ МЕНЮ (overlay) */
.mobile-nav-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(42, 36, 32, 0.95);
    z-index: 205;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.mobile-nav-overlay.active {
    opacity: 1;
    visibility: visible;
}

.mobile-nav {
    position: fixed;
    top: 0;
    right: -100%;
    width: 280px;
    height: 100%;
    background: var(--warm);
    z-index: 215;
    padding: 100px 30px 40px;
    transition: right 0.4s ease;
    box-shadow: -5px 0 20px rgba(0, 0, 0, 0.1);
}

.mobile-nav-overlay.active .mobile-nav {
    right: 0;
}

.mobile-nav ul {
    list-style: none;
}

.mobile-nav li {
    margin-bottom: 25px;
}

.mobile-nav a {
    text-decoration: none;
    color: var(--charcoal);
    font-size: 18px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-weight: 500;
    transition: color 0.2s;
    display: block;
}

.mobile-nav a:hover {
    color: var(--walnut);
}

.mobile-nav-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 30px;
    height: 30px;
    cursor: pointer;
    background: none;
    border: none;
}

.mobile-nav-close::before,
.mobile-nav-close::after {
    content: '';
    position: absolute;
    top: 14px;
    right: 4px;
    width: 22px;
    height: 2px;
    background: var(--charcoal);
}

.mobile-nav-close::before {
    transform: rotate(45deg);
}

.mobile-nav-close::after {
    transform: rotate(-45deg);
}

.hactions {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-shrink: 0;
}

.search-wrap {
    position: relative;
}

.search-wrap input {
    background: transparent;
    border: none;
    border-bottom: 1px solid var(--line);
    padding: 6px 28px 6px 0;
    width: 160px;
    font-family: 'Jost', sans-serif;
    font-size: 14px;
    font-weight: 400;
    color: var(--charcoal);
    outline: none;
}

.search-icon {
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--mid);
    font-size: 16px;
    cursor: pointer;
}

.cart-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--charcoal);
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-weight: 500;
}

.cart-count {
    background: var(--walnut);
    color: #fff;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
}

.profile-link {
    color: var(--charcoal);
    text-decoration: none;
    font-size: 14px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    white-space: nowrap;
    font-weight: 500;
}

/* CART PAGE */
.cart-page {
    padding-top: 100px;
    max-width: 1440px;
    margin: 0 auto;
    padding-left: 24px;
    padding-right: 24px;
    width: 100%;
    min-height: 60vh;
}

.cart-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 42px;
    font-weight: 400;
    margin-bottom: 40px;
}

.empty-cart {
    text-align: center;
    padding: 80px 24px;
}

.empty-icon {
    font-size: 80px;
    margin-bottom: 24px;
    color: var(--mid);
}

.empty-cart h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 32px;
    font-weight: 400;
    margin-bottom: 16px;
}

.empty-cart p {
    color: var(--mid);
    margin-bottom: 24px;
    font-size: 17px;
}

.btn-primary {
    display: inline-block;
    padding: 14px 32px;
    background: var(--walnut);
    color: #fff;
    text-decoration: none;
    font-size: 13px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    border-radius: 40px;
    transition: background 0.25s;
    font-weight: 500;
}

.btn-primary:hover {
    background: var(--walnut-l);
}

.cart-content {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 50px;
}

.cart-table {
    width: 100%;
    border-collapse: collapse;
}

.cart-table th {
    text-align: left;
    padding: 18px 12px;
    font-size: 13px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    font-weight: 500;
    color: var(--mid);
    border-bottom: 1px solid var(--line);
}

.cart-table td {
    padding: 24px 12px;
    border-bottom: 1px solid var(--line);
    vertical-align: middle;
}

.cart-product {
    display: flex;
    align-items: center;
    gap: 20px;
}

.cart-product-img {
    width: 85px;
    height: 85px;
    background-size: cover;
    background-position: center;
    flex-shrink: 0;
    border-radius: 12px;
    background-color: var(--cream);
}

.cart-product-info h4 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 18px;
    font-weight: 500;
    margin-bottom: 6px;
}

.cart-product-options {
    font-size: 13px;
    color: var(--mid);
}

.cart-quantity {
    display: flex;
    align-items: center;
    gap: 10px;
}

.cart-qty-btn {
    width: 32px;
    height: 32px;
    background: none;
    border: 1px solid var(--line);
    cursor: pointer;
    font-size: 18px;
    border-radius: 8px;
    transition: all 0.2s;
}

.cart-qty-btn:hover {
    background: var(--walnut);
    color: #fff;
    border-color: var(--walnut);
}

.cart-qty-input {
    width: 50px;
    text-align: center;
    border: none;
    background: transparent;
    font-size: 16px;
    font-weight: 500;
}

.cart-remove {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--mid);
    font-size: 20px;
    transition: color 0.2s;
}

.cart-remove:hover {
    color: var(--red);
}

.cart-summary {
    background: var(--cream);
    padding: 35px;
    height: fit-content;
    position: sticky;
    top: 100px;
    border-radius: 20px;
}

.summary-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 26px;
    font-weight: 400;
    margin-bottom: 24px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 14px 0;
    font-size: 16px;
    border-bottom: 1px solid var(--line);
}

.summary-row.total {
    font-size: 20px;
    font-weight: 600;
    border-bottom: none;
    padding-top: 20px;
    margin-top: 12px;
    color: var(--walnut);
}

.checkout-btn {
    width: 100%;
    margin-top: 28px;
    background: var(--walnut);
    color: #fff;
    padding: 16px;
    border: none;
    font-size: 14px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    cursor: pointer;
    border-radius: 40px;
    transition: background 0.2s;
    font-weight: 500;
}

.checkout-btn:hover {
    background: var(--walnut-l);
}

.spinner {
    text-align: center;
    padding: 60px;
    font-size: 17px;
    color: var(--mid);
}

/* FOOTER */
footer {
    background: var(--charcoal);
    color: rgba(255, 255, 255, 0.72);
    font-size: 14px;
    width: 100%;
}

.footer-inner {
    max-width: 1440px;
    margin: 0 auto;
    padding: 48px 24px 28px;
}

.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 32px;
    margin-bottom: 32px;
}

.footer-logo {
    font-family: 'Cormorant Garamond', serif;
    font-size: 24px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 12px;
}

.footer-logo span {
    color: var(--gold);
    font-style: italic;
}

.fcol h4 {
    color: #fff;
    font-size: 13px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    margin-bottom: 16px;
    font-weight: 600;
}

.fcol ul {
    list-style: none;
}

.fcol li {
    margin-bottom: 10px;
}

.fcol a {
    color: rgba(255, 255, 255, 0.6);
    text-decoration: none;
    font-size: 13px;
    transition: color 0.2s;
}

.fcol a:hover {
    color: var(--gold);
}

.sub-form {
    display: flex;
    margin-top: 8px;
}

.sub-form input {
    flex: 1;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-right: none;
    padding: 12px 14px;
    color: #fff;
    font-size: 13px;
    border-radius: 30px 0 0 30px;
}

.sub-form button {
    background: var(--walnut);
    border: none;
    color: #fff;
    padding: 12px 20px;
    cursor: pointer;
    font-size: 13px;
    border-radius: 0 30px 30px 0;
    font-weight: 500;
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 20px;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 12px;
}

.footer-socials {
    display: flex;
    gap: 10px;
    margin-top: 12px;
}

.footer-social-link {
    width: 34px;
    height: 34px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.6);
    text-decoration: none;
    font-size: 13px;
    border-radius: 50%;
    transition: all 0.2s;
}

.footer-social-link:hover {
    background: var(--gold);
    border-color: var(--gold);
    color: #fff;
}

/* TOAST */
.toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 999;
    background: var(--charcoal);
    color: #fff;
    padding: 12px 24px;
    font-size: 14px;
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.35s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    border-radius: 50px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    font-weight: 500;
}

.toast.show {
    transform: translateY(0);
    opacity: 1;
}

.toast-icon {
    color: var(--gold);
    font-size: 18px;
}

/* RESPONSIVE */
@media (max-width: 1000px) {
    .cart-content {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    .cart-summary {
        position: static;
    }
}

@media (max-width: 768px) {
    .nav {
        display: none;
    }
    .burger-menu {
        display: flex;
    }
    .search-wrap {
        display: none;
    }
    .cart-page {
        padding-top: 90px;
        padding-left: 20px;
        padding-right: 20px;
    }
    .cart-title {
        font-size: 36px;
        margin-bottom: 30px;
    }
    .cart-table th {
        font-size: 12px;
        padding: 14px 8px;
    }
    .cart-table td {
        padding: 18px 8px;
    }
    .cart-product {
        gap: 14px;
    }
    .cart-product-img {
        width: 70px;
        height: 70px;
    }
    .cart-product-info h4 {
        font-size: 16px;
    }
}

@media (max-width: 600px) {
    .logo {
        font-size: 20px;
    }
    .profile-link {
        display: none;
    }
    .cart-btn span:not(.cart-count) {
        display: none;
    }
    .cart-page {
        padding-left: 16px;
        padding-right: 16px;
    }
    .cart-title {
        font-size: 32px;
    }
    .cart-table th:nth-child(2),
    .cart-table td:nth-child(2) {
        display: none;
    }
    .cart-product {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .cart-product-img {
        width: 100px;
        height: 100px;
    }
    .cart-summary {
        padding: 25px;
    }
    .summary-title {
        font-size: 24px;
    }
    .empty-cart {
        padding: 50px 20px;
    }
    .empty-icon {
        font-size: 64px;
    }
    .empty-cart h2 {
        font-size: 28px;
    }
    .footer-inner {
        padding: 40px 20px 24px;
    }
}

@media (max-width: 480px) {
    .hinner {
        padding: 0 16px;
    }
    .cart-count {
        width: 22px;
        height: 22px;
        font-size: 12px;
    }
    .cart-page {
        padding-left: 14px;
        padding-right: 14px;
    }
    .cart-title {
        font-size: 28px;
    }
    .cart-product-img {
        width: 80px;
        height: 80px;
    }
    .cart-qty-btn {
        width: 28px;
        height: 28px;
        font-size: 16px;
    }
    .cart-qty-input {
        width: 40px;
        font-size: 14px;
    }
    .checkout-btn {
        padding: 14px;
        font-size: 13px;
    }
}
</style>
</head>
<body>

<header class="header">
  <div class="hinner">
    <a href="index.php" class="logo">Планета <span>Мебели</span></a>
    
    <!-- Десктопное меню -->
    <ul class="nav">
      <li><a href="catalog.php">Каталог</a></li>
      <li><a href="collections.php">Коллекции</a></li>
      <li><a href="designers.php">Дизайнерам</a></li>
      <li><a href="about.php">О нас</a></li>
    </ul>
    
    <!-- Бургер-меню для мобильных -->
    <div class="burger-menu" id="burgerMenu">
      <span></span>
      <span></span>
      <span></span>
    </div>
    
    <div class="hactions">
      <div class="search-wrap">
        <input type="text" placeholder="Поиск...">
        <span class="search-icon">⌕</span>
      </div>
      <a href="profile.php" class="profile-link" id="profileLink"><?= $userName ? htmlspecialchars($userName) : 'Войти' ?></a>
      <button class="cart-btn" onclick="location.href='cart.php'">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <path d="M16 10a4 4 0 01-8 0"/>
        </svg>
        <span class="cart-count" id="cartCount">0</span>
      </button>
    </div>
  </div>
</header>

<!-- Мобильное меню (overlay) -->
<div class="mobile-nav-overlay" id="mobileNavOverlay">
  <div class="mobile-nav">
    <button class="mobile-nav-close" id="mobileNavClose"></button>
    <ul>
      <li><a href="catalog.php">Каталог</a></li>
      <li><a href="collections.php">Коллекции</a></li>
      <li><a href="designers.php">Дизайнерам</a></li>
      <li><a href="about.php">О нас</a></li>
      <li><a href="profile.php">Личный кабинет</a></li>
      <li><a href="cart.php">Корзина</a></li>
    </ul>
  </div>
</div>

<main class="cart-page">
    <h1 class="cart-title">Корзина</h1>
    <div id="cartContainer">
        <div class="spinner">Загрузка...</div>
    </div>
</main>

<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div>
        <div class="footer-logo">Планета <span>Мебели</span></div>
        <p style="font-size: 13px; margin-bottom: 12px;">Мебель для вашего дома</p>
        <div class="footer-socials">
          <a href="#" class="footer-social-link"><i class="fab fa-vk"></i></a>
          <a href="#" class="footer-social-link"><i class="fab fa-telegram"></i></a>
        </div>
      </div>
      <div class="fcol">
        <h4>Каталог</h4>
        <ul><li><a href="catalog.php">Диваны</a></li><li><a href="catalog.php">Кровати</a></li><li><a href="catalog.php">Столы</a></li><li><a href="catalog.php">Шкафы</a></li></ul>
      </div>
      <div class="fcol">
        <h4>Компания</h4>
        <ul><li><a href="about.php">О нас</a></li><li><a href="delivery.php">Доставка</a></li><li><a href="contacts.php">Контакты</a></li></ul>
      </div>
      <div class="fcol">
        <h4>Подписка</h4>
        <form class="sub-form" id="subFormFooter">
          <input type="email" id="subEmailFooter" placeholder="ваш@email.ru">
          <button type="submit">→</button>
        </form>
        <p id="subMsgFooter" style="font-size:12px;margin-top:8px;color:var(--gold);display:none">✓ Вы подписались!</p>
        <p id="subErrFooter" style="font-size:12px;margin-top:8px;color:#e07070;display:none">Введите email</p>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 Планета Мебели</span>
      <span>Политика конфиденциальности</span>
    </div>
  </div>
</footer>

<div class="toast" id="toast"><span class="toast-icon">✓</span><span id="toastMsg"></span></div>

<script>
// Бургер-меню
const burgerMenu = document.getElementById('burgerMenu');
const mobileNavOverlay = document.getElementById('mobileNavOverlay');
const mobileNavClose = document.getElementById('mobileNavClose');

function openMobileMenu() {
    burgerMenu.classList.add('active');
    mobileNavOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeMobileMenu() {
    burgerMenu.classList.remove('active');
    mobileNavOverlay.classList.remove('active');
    document.body.style.overflow = '';
}

burgerMenu?.addEventListener('click', openMobileMenu);
mobileNavClose?.addEventListener('click', closeMobileMenu);
mobileNavOverlay?.addEventListener('click', function(e) {
    if (e.target === mobileNavOverlay) closeMobileMenu();
});

// Функции для работы с корзиной в localStorage
const getCart = () => JSON.parse(localStorage.getItem('pm_cart') || '[]');
const saveCart = (cart) => localStorage.setItem('pm_cart', JSON.stringify(cart));

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
}

function showToast(msg) {
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
    const cartCountEl = document.getElementById('cartCount');
    if (cartCountEl) cartCountEl.textContent = count;
}

function updateCartQuantity(key, delta) {
    const cart = getCart();
    const item = cart.find(i => i._key === key);
    if (item) {
        const newQty = item.quantity + delta;
        if (newQty < 1) {
            removeFromCart(key);
        } else {
            item.quantity = newQty;
            saveCart(cart);
            renderCart();
            updateCartCount();
        }
    }
}

function removeFromCart(key) {
    let cart = getCart();
    cart = cart.filter(i => i._key !== key);
    saveCart(cart);
    renderCart();
    updateCartCount();
    showToast('Товар удалён из корзины');
}

function getImageUrl(image) {
    if (!image || image === '') {
        return '/uploads/no-image.webp';
    }
    if (image.indexOf('http://') === 0 || image.indexOf('https://') === 0) {
        return image;
    }
    if (image.indexOf('/uploads/') === 0) {
        return image;
    }
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

function proceedToCheckout() {
    const cart = getCart();
    if (cart.length === 0) {
        showToast('Корзина пуста');
        return;
    }
    window.location.href = 'checkout.php';
}

function renderCart() {
    const cart = getCart();
    const container = document.getElementById('cartContainer');
    
    if (!container) return;
    
    if (cart.length === 0) {
        container.innerHTML = `
            <div class="empty-cart">
                <div class="empty-icon"><i class="fas fa-shopping-cart"></i></div>
                <h2>Корзина пуста</h2>
                <p>Добавьте товары из каталога</p>
                <a href="catalog.php" class="btn-primary">Перейти в каталог</a>
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
        const optionsText = item.options ? Object.values(item.options).filter(Boolean).join(' · ') : '';
        
        itemsHtml += `
            <tr>
                <td>
                    <div class="cart-product">
                        <div class="cart-product-img" style="background-image: url('${imageUrl}')"></div>
                        <div class="cart-product-info">
                            <h4>${escapeHtml(item.name)}</h4>
                            ${optionsText ? `<div class="cart-product-options">${escapeHtml(optionsText)}</div>` : ''}
                        </div>
                    </div>
                </td>
                <td>${formatPrice(item.price)}</td>
                <td>
                    <div class="cart-quantity">
                        <button class="cart-qty-btn" onclick="updateCartQuantity('${item._key}', -1)">−</button>
                        <input type="text" class="cart-qty-input" value="${item.quantity}" readonly>
                        <button class="cart-qty-btn" onclick="updateCartQuantity('${item._key}', 1)">+</button>
                    </div>
                </td>
                <td>${formatPrice(itemTotal)}</td>
                <td><button class="cart-remove" onclick="removeFromCart('${item._key}')">✕</button></td>
            </tr>
        `;
    });
    
    const delivery = subtotal >= 10000 ? 0 : 500;
    const total = subtotal + delivery;
    
    container.innerHTML = `
        <div class="cart-content">
            <div class="cart-items">
                <table class="cart-table">
                    <thead>
                        <tr><th>Товар</th><th>Цена</th><th>Кол-во</th><th>Сумма</th><th></th></tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>
            </div>
            <div class="cart-summary">
                <h3 class="summary-title">Итого</h3>
                <div class="summary-row"><span>Товары (${cart.reduce((s,i)=>s+i.quantity,0)} шт.)</span><span>${formatPrice(subtotal)}</span></div>
                <div class="summary-row"><span>Доставка</span><span>${delivery === 0 ? 'Бесплатно' : formatPrice(delivery)}</span></div>
                <div class="summary-row total"><span>Итого</span><span>${formatPrice(total)}</span></div>
                <button class="checkout-btn" onclick="proceedToCheckout()">Оформить заказ</button>
            </div>
        </div>
    `;
}

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
    showToast('Вы успешно подписались на новости!');
});

// Инициализация
document.addEventListener('DOMContentLoaded', () => {
    renderCart();
    updateCartCount();
    
    const user = JSON.parse(localStorage.getItem('pm_user') || 'null');
    const profileLink = document.getElementById('profileLink');
    if (profileLink && user) profileLink.textContent = user.name?.split(' ')[0] || user.name;
});

// Глобальные функции для onclick
window.updateCartQuantity = updateCartQuantity;
window.removeFromCart = removeFromCart;
window.proceedToCheckout = proceedToCheckout;
</script>
</body>
</html>
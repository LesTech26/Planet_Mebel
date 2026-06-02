<?php
session_start();

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cartCount = array_sum(array_column($cart, 'quantity'));
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

// Получаем товары ИЗ БАЗЫ ДАННЫХ
$products = getProducts();
$categories = getCategories();

// Функция для получения корректного URL картинки
function getProductImageUrl($image) {
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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
<title>Каталог — Планета Мебели</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { 
    --cream: #F5F0E8; 
    --warm: #FDFAF5; 
    --walnut: #7C5C3E; 
    --walnut-l: #A07850; 
    --charcoal: #2A2420; 
    --mid: #8C7B6E; 
    --line: rgba(124,92,62,0.18); 
    --gold: #C9A96E; 
    --green: #4A7C59;
}
html, body { min-height: 100vh; display: flex; flex-direction: column; }
body { font-family: 'Jost', sans-serif; background: var(--warm); color: var(--charcoal); font-weight: 400; font-size: 17px; line-height: 1.55; overflow-x: hidden; width: 100%; flex: 1; display: flex; flex-direction: column; }
main { flex: 1; }
footer { margin-top: auto; }

/* HEADER */
.header { position: fixed; top: 0; left: 0; right: 0; z-index: 200; background: rgba(253,250,245,0.96); backdrop-filter: blur(14px); border-bottom: 1px solid var(--line); height: 76px; display: flex; align-items: center; width: 100%; }
.hinner { width: 100%; max-width: 1440px; margin: 0 auto; padding: 0 24px; display: flex; justify-content: space-between; align-items: center; gap: 24px; }
.logo { font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 600; letter-spacing: 0.04em; color: var(--charcoal); text-decoration: none; white-space: nowrap; flex-shrink: 0; }
.logo span { color: var(--walnut); font-style: italic; }

/* ДЕСКТОПНОЕ МЕНЮ */
.nav { display: flex; gap: 32px; list-style: none; margin: 0; padding: 0; }
.nav a { text-decoration: none; color: var(--charcoal); font-size: 15px; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 500; transition: color 0.2s; white-space: nowrap;margin-right: 16px; }
.nav a:hover, .nav a.active { color: var(--walnut); }

/* БУРГЕР-МЕНЮ */
.burger-menu { display: none; flex-direction: column; justify-content: space-between; width: 32px; height: 22px; cursor: pointer; z-index: 210; }
.burger-menu span { display: block; width: 100%; height: 2px; background: var(--charcoal); transition: all 0.3s ease; border-radius: 2px; }
.burger-menu.active span:nth-child(1) { transform: translateY(10px) rotate(45deg); }
.burger-menu.active span:nth-child(2) { opacity: 0; }
.burger-menu.active span:nth-child(3) { transform: translateY(-10px) rotate(-45deg); }

/* МОБИЛЬНОЕ МЕНЮ */
.mobile-nav-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(42,36,32,0.95); z-index: 205; opacity: 0; visibility: hidden; transition: all 0.3s ease; backdrop-filter: blur(10px); }
.mobile-nav-overlay.active { opacity: 1; visibility: visible; }
.mobile-nav { position: fixed; top: 0; right: -100%; width: 300px; height: 100%; background: var(--warm); z-index: 215; padding: 100px 30px 40px; transition: right 0.4s ease; box-shadow: -5px 0 20px rgba(0,0,0,0.1); }
.mobile-nav-overlay.active .mobile-nav { right: 0; }
.mobile-nav ul { list-style: none; }
.mobile-nav li { margin-bottom: 28px; }
.mobile-nav a { text-decoration: none; color: var(--charcoal); font-size: 18px; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 500; transition: color 0.2s; display: block; }
.mobile-nav a:hover { color: var(--walnut); }
.mobile-nav-close { position: absolute; top: 24px; right: 24px; width: 30px; height: 30px; cursor: pointer; background: none; border: none; }
.mobile-nav-close::before, .mobile-nav-close::after { content: ''; position: absolute; top: 14px; right: 4px; width: 22px; height: 2px; background: var(--charcoal); }
.mobile-nav-close::before { transform: rotate(45deg); }
.mobile-nav-close::after { transform: rotate(-45deg); }

.hactions { display: flex; align-items: center; gap: 20px; flex-shrink: 0; }
.search-wrap { position: relative; }
.search-wrap input { background: transparent; border: none; border-bottom: 1px solid var(--line); padding: 6px 28px 6px 0; width: 160px; font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 400; color: var(--charcoal); outline: none; }
.search-icon { position: absolute; right: 4px; top: 50%; transform: translateY(-50%); color: var(--mid); font-size: 16px; cursor: pointer; }
.cart-btn { background: none; border: none; cursor: pointer; color: var(--charcoal); display: flex; align-items: center; gap: 6px; font-size: 14px; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 500; }
.cart-count { background: var(--walnut); color: #fff; width: 22px; height: 22px; border-radius: 50%; font-size: 12px; display: flex; align-items: center; justify-content: center; font-weight: 500; }
.profile-link { color: var(--charcoal); text-decoration: none; font-size: 14px; letter-spacing: 0.08em; text-transform: uppercase; white-space: nowrap; font-weight: 500; }

/* CATALOG MAIN */
.catalog-main { padding-top: 90px; max-width: 1440px; margin: 0 auto; padding-left: 24px; padding-right: 24px; width: 100%;margin-bottom: 20px }
.catalog-layout { display: grid; grid-template-columns: 300px 1fr; gap: 48px; }
.filters-sidebar { position: sticky; top: 90px; height: fit-content; background: var(--cream); padding: 24px; border-radius: 16px; }
.filter-group { margin-bottom: 28px; border-bottom: 1px solid var(--line); padding-bottom: 20px; }
.filter-title { font-size: 14px; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; display: flex; justify-content: space-between; font-weight: 600; }
.filter-content { margin-top: 14px; }
.price-inputs { display: flex; gap: 12px; }
.price-inputs input { width: 100%; padding: 12px; border: 1px solid var(--line); background: var(--warm); border-radius: 8px; font-family: 'Jost', sans-serif; font-size: 14px; }
.category-item, .checkbox-item { display: flex; align-items: center; gap: 10px; padding: 10px 0; cursor: pointer; font-size: 15px; }
.category-count { margin-left: auto; color: var(--mid); font-size: 12px; }
.btn-reset { width: 100%; padding: 14px; background: none; border: 1px solid var(--walnut); color: var(--walnut); cursor: pointer; margin-top: 20px; font-size: 13px; text-transform: uppercase; border-radius: 8px; transition: all 0.2s; font-weight: 500; }
.btn-reset:hover { background: var(--walnut); color: #fff; }
.sort-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 36px; flex-wrap: wrap; gap: 15px; }
.products-count { font-size: 14px; color: var(--mid); }
.sort-select { padding: 10px 18px; border: 1px solid var(--line); background: var(--warm); border-radius: 30px; font-family: 'Jost', sans-serif; font-size: 14px; cursor: pointer; }
.products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 32px; }

/* CARD */
.product-card { cursor: pointer; transition: transform 0.3s; background: var(--warm); border-radius: 16px; overflow: hidden; }
.product-card:hover { transform: translateY(-5px); }
.card-img { aspect-ratio: 3/4; overflow: hidden; background: var(--cream); position: relative; display: flex; align-items: center; justify-content: center; }
.card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
.product-card:hover .card-img img { transform: scale(1.05); }
.card-badge { position: absolute; top: 12px; left: 12px; background: var(--walnut); color: #fff; font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; padding: 4px 10px; border-radius: 6px; z-index: 2; font-weight: 500; }
.card-fav { position: absolute; top: 12px; right: 12px; background: rgba(253,250,245,0.95); border: none; width: 34px; height: 34px; cursor: pointer; font-size: 18px; color: var(--mid); opacity: 0; transition: opacity 0.2s; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 2; }
.product-card:hover .card-fav { opacity: 1; }
.card-fav.fav-active { opacity: 1; color: var(--walnut); }
.card-info { padding: 16px; }
.card-cat { font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--mid); margin-bottom: 6px; font-weight: 500; }
.card-name { font-family: 'Cormorant Garamond', serif; font-size: 18px; font-weight: 500; margin-bottom: 8px; color: var(--charcoal); }
.card-price { font-size: 16px; color: var(--walnut); display: flex; gap: 10px; align-items: baseline; flex-wrap: wrap; font-weight: 500; }
.card-price-old { font-size: 13px; color: var(--mid); text-decoration: line-through; font-weight: 400; }
.card-add { margin-top: 12px; width: 100%; background: none; border: 1px solid var(--line); padding: 10px; font-size: 12px; text-transform: uppercase; cursor: pointer; opacity: 0; transform: translateY(4px); transition: opacity 0.2s, transform 0.2s, background 0.2s; border-radius: 30px; font-weight: 500; }
.product-card:hover .card-add { opacity: 1; transform: translateY(0); }
.card-add:hover { background: var(--walnut); color: #fff; border-color: var(--walnut); }

.pagination { display: flex; justify-content: center; gap: 10px; margin-top: 56px; }
.page-btn { padding: 10px 16px; border: 1px solid var(--line); background: transparent; cursor: pointer; border-radius: 8px; transition: all 0.2s; font-size: 14px; }
.page-btn:hover { border-color: var(--walnut); }
.page-btn.active { background: var(--walnut); color: #fff; border-color: var(--walnut); }

.mobile-filter-btn { display: none; position: fixed; bottom: 24px; right: 24px; background: var(--walnut); color: #fff; padding: 14px 24px; border: none; z-index: 100; cursor: pointer; border-radius: 40px; font-size: 14px; font-weight: 500; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
.mobile-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 250; }

/* FOOTER */
footer { background: var(--charcoal); color: rgba(255,255,255,0.72); font-size: 14px; width: 100%; }
.footer-inner { max-width: 1440px; margin: 0 auto; padding: 48px 24px 28px; }
.footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 32px; margin-bottom: 32px; }
.footer-logo { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 600; color: #fff; margin-bottom: 12px; }
.footer-logo span { color: var(--gold); font-style: italic; }
.fcol h4 { color: #fff; font-size: 13px; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 16px; font-weight: 600; }
.fcol ul { list-style: none; }
.fcol li { margin-bottom: 10px; }
.fcol a { color: rgba(255,255,255,0.6); text-decoration: none; font-size: 13px; transition: color 0.2s; }
.fcol a:hover { color: var(--gold); }
.sub-form { display: flex; margin-top: 8px; }
.sub-form input { flex: 1; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-right: none; padding: 12px 14px; color: #fff; font-size: 13px; border-radius: 30px 0 0 30px; }
.sub-form button { background: var(--walnut); border: none; color: #fff; padding: 12px 20px; cursor: pointer; font-size: 13px; border-radius: 0 30px 30px 0; font-weight: 500; }
.footer-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px; font-size: 12px; }
.socials { display: flex; gap: 10px; margin-top: 12px; }
.social-link { width: 34px; height: 34px; border: 1px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.6); text-decoration: none; font-size: 13px; border-radius: 50%; transition: all 0.2s; }
.social-link:hover { background: var(--gold); border-color: var(--gold); color: #fff; }

/* TOAST */
.toast { position: fixed; bottom: 24px; right: 24px; z-index: 999; background: var(--charcoal); color: #fff; padding: 12px 24px; font-size: 14px; transform: translateY(100px); opacity: 0; transition: all 0.35s ease; display: flex; align-items: center; gap: 10px; border-radius: 50px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); font-weight: 500; }
.toast.show { transform: translateY(0); opacity: 1; }
.toast-icon { color: var(--gold); font-size: 18px; }

/* RESPONSIVE */
@media (min-width: 769px) { .search-wrap { display: block; } }
@media (max-width: 900px) { 
    .catalog-layout { grid-template-columns: 1fr; } 
    .filters-sidebar { position: fixed; top: 0; left: -340px; width: 320px; height: 100%; background: var(--warm); z-index: 300; padding: 90px 24px 24px; transition: left 0.3s; overflow-y: auto; } 
    .filters-sidebar.open { left: 0; } 
    .mobile-filter-btn { display: block; } 
    .catalog-main { padding-left: 20px; padding-right: 20px; } 
}
@media (max-width: 768px) { 
    .nav { display: none; } 
    .burger-menu { display: flex; }
    .products-grid { gap: 24px; } 
    .catalog-main { padding-top: 80px; } 
}
@media (max-width: 600px) { 
    .logo { font-size: 20px; } 
    .profile-link { display: none; } 
    .cart-btn span:not(.cart-count) { display: none; } 
    .search-wrap { display: none; } 
    .sort-bar { flex-direction: column; align-items: flex-start; } 
    .products-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; } 
    .card-name { font-size: 15px; } 
    .card-price { font-size: 14px; } 
    .card-info { padding: 12px; } 
}
@media (max-width: 480px) { 
    .hinner { padding: 0 14px; } 
    .cart-count { width: 22px; height: 22px; font-size: 12px; } 
    .catalog-main { padding-left: 14px; padding-right: 14px; } 
}
</style>
</head>
<body>

<header class="header">
  <div class="hinner">
    <a href="index.php" class="logo">Планета <span>Мебели</span></a>
    
    <!-- Десктопное меню -->
    <ul class="nav">
      <li><a href="catalog.php" class="active">Каталог</a></li>
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
        <input type="text" id="searchInput" placeholder="Поиск...">
        <span class="search-icon">⌕</span>
      </div>
      <a href="profile.php" class="profile-link" id="profileLink"><?= $userName ? htmlspecialchars($userName) : 'Войти' ?></a>
      <button class="cart-btn" onclick="location.href='cart.php'">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <span class="cart-count" id="cartCount"><?= $cartCount ?></span>
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

<main class="catalog-main">
    <div class="catalog-layout">
        <aside class="filters-sidebar" id="filtersSidebar">
            <div class="filter-group">
                <div class="filter-title" onclick="this.closest('.filter-group').classList.toggle('collapsed')">Категория <span>▼</span></div>
                <div class="filter-content" id="categoryFilter"></div>
            </div>
            <div class="filter-group">
                <div class="filter-title" onclick="this.closest('.filter-group').classList.toggle('collapsed')">Цена <span>▼</span></div>
                <div class="filter-content">
                    <div class="price-inputs">
                        <input type="number" id="priceMin" placeholder="от 0">
                        <input type="number" id="priceMax" placeholder="до 300 000">
                    </div>
                </div>
            </div>
            <div class="filter-group">
                <div class="filter-title" onclick="this.closest('.filter-group').classList.toggle('collapsed')">Материал <span>▼</span></div>
                <div class="filter-content" id="materialFilter"></div>
            </div>
            <button class="btn-reset" id="resetFilters">Сбросить фильтры</button>
        </aside>

        <div class="products-area">
            <div class="sort-bar">
                <span class="products-count" id="productsCount">Найдено товаров: <?= count($products) ?></span>
                <select class="sort-select" id="sortSelect">
                    <option value="default">По умолчанию</option>
                    <option value="price_asc">Цена: по возрастанию</option>
                    <option value="price_desc">Цена: по убыванию</option>
                    <option value="name_asc">Название: А-Я</option>
                </select>
            </div>
            <div class="products-grid" id="productsGrid"></div>
            <div class="pagination" id="pagination"></div>
        </div>
    </div>
</main>

<button class="mobile-filter-btn" id="mobileFilterBtn">🔍 Фильтры</button>
<div class="mobile-overlay" id="mobileOverlay"></div>

<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div>
        <div class="footer-logo">Планета <span>Мебели</span></div>
        <p style="font-size: 13px;">Мебель для вашего дома</p>
        <div class="socials">
          <a href="#" class="social-link">vk</a>
          <a href="#" class="social-link">tg</a>
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
        <p id="subMsgFooter" style="font-size:11px;margin-top:8px;color:var(--gold);display:none">✓ Вы подписались!</p>
        <p id="subErrFooter" style="font-size:11px;margin-top:8px;color:#e07070;display:none">Введите email</p>
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

burgerMenu.addEventListener('click', openMobileMenu);
mobileNavClose.addEventListener('click', closeMobileMenu);
mobileNavOverlay.addEventListener('click', function(e) {
    if (e.target === mobileNavOverlay) closeMobileMenu();
});

// Данные из БД
const productsData = <?= json_encode($products) ?>;
const categoriesData = <?= json_encode($categories) ?>;

let currentProductsList = [...productsData];
let currentPageNum = 1;
const itemsPerPage = 9;

// Функции корзины
const getCartItems = () => JSON.parse(localStorage.getItem('pm_cart')||'[]');
const saveCartItems = c => localStorage.setItem('pm_cart',JSON.stringify(c));
const getFavItems = () => JSON.parse(localStorage.getItem('pm_favs')||'[]');
const saveFavItems = f => localStorage.setItem('pm_favs',JSON.stringify(f));
const isItemFav = id => getFavItems().some(f=>f.id===id);

function updateCartCountDisplay(){
  const count = getCartItems().reduce((s,i)=>s+i.quantity,0);
  const el = document.getElementById('cartCount');
  if(el) el.textContent = count;
}

function addProductToCart(id, qty){
  const product = productsData.find(p => p.id === id);
  if(!product) return;
  const cart = getCartItems();
  const key = id+'__';
  const existing = cart.find(i=>i._key===key);
  if(existing) existing.quantity += qty;
  else cart.push({productId:id, name:product.name, price:product.price, quantity:qty, _key:key, image:product.image});
  saveCartItems(cart);
  updateCartCountDisplay();
  showToastMessage('«'+product.name+'» добавлен в корзину');
}

function toggleFavorite(id, btn){
  const product = productsData.find(p => p.id === id);
  if(!product) return;
  const favs = getFavItems();
  const idx = favs.findIndex(f=>f.id===id);
  if(idx>-1) favs.splice(idx,1);
  else favs.push({id:id, name:product.name, price:product.price, image:product.image});
  saveFavItems(favs);
  btn.classList.toggle('fav-active');
  showToastMessage(idx>-1 ? 'Удалено из избранного' : 'Добавлено в избранное');
}

function showToastMessage(msg){
  const toast = document.getElementById('toast'); 
  const msgSpan = document.getElementById('toastMsg');
  if(msgSpan) msgSpan.textContent = msg;
  if(toast){ toast.classList.add('show'); clearTimeout(toast._tm); toast._tm=setTimeout(()=>toast.classList.remove('show'),3000); }
}

function getImageUrl(image) {
    if (!image || image === '') return '/uploads/no-image.webp';
    if (image.indexOf('http://') === 0 || image.indexOf('https://') === 0) return image;
    if (image.indexOf('/uploads/') === 0) return image;
    return '/uploads/products/' + image;
}

function renderProductCard(product){
  const isFavorite = isItemFav(product.id);
  const imageUrl = getImageUrl(product.image);
  
  return `<div class="product-card" onclick="location.href='product.php?id=${product.id}'">
    <div class="card-img">
      <img src="${imageUrl}" alt="${product.name}" onerror="this.src='/uploads/no-image.webp'">
      ${product.is_new ? '<span class="card-badge">Новинка</span>' : ''}
      <button class="card-fav ${isFavorite ? 'fav-active' : ''}" onclick="event.stopPropagation();toggleFavorite(${product.id}, this)">${isFavorite ? '♥' : '♡'}</button>
    </div>
    <div class="card-info">
      <p class="card-cat">${product.category || 'Мебель'}</p>
      <div class="card-name">${product.name}</div>
      <div class="card-price">
        <span>${Number(product.price).toLocaleString('ru-RU')} ₽</span>
        ${product.old_price ? `<span class="card-price-old">${Number(product.old_price).toLocaleString('ru-RU')} ₽</span>` : ''}
      </div>
      <button class="card-add" onclick="event.stopPropagation();addProductToCart(${product.id}, 1)">В корзину</button>
    </div>
  </div>`;
}

function renderProductsList(){
  const start = (currentPageNum-1)*itemsPerPage;
  const pageItems = currentProductsList.slice(start, start+itemsPerPage);
  const grid = document.getElementById('productsGrid');
  if(grid) grid.innerHTML = pageItems.map(renderProductCard).join('');
  document.getElementById('productsCount').innerHTML = `Найдено товаров: ${currentProductsList.length}`;
  renderPaginationControls();
}

function renderPaginationControls(){
  const totalPages = Math.ceil(currentProductsList.length/itemsPerPage);
  const container = document.getElementById('pagination');
  if(!container) return;
  if(totalPages<=1){ container.innerHTML=''; return; }
  let html='';
  for(let i=1;i<=totalPages;i++){
    html+=`<button class="page-btn ${i===currentPageNum?'active':''}" onclick="goToPage(${i})">${i}</button>`;
  }
  container.innerHTML=html;
}

function goToPage(page){
  currentPageNum=page;
  renderProductsList();
  window.scrollTo({top:document.querySelector('.catalog-main').offsetTop-100, behavior:'smooth'});
}

function applyProductFilters(){
  let filtered = [...productsData];
  const selectedCat = document.querySelector('input[name="category"]:checked')?.value;
  if(selectedCat) filtered = filtered.filter(p => p.category_id == selectedCat);
  const minPrice = parseInt(document.getElementById('priceMin')?.value) || 0;
  const maxPrice = parseInt(document.getElementById('priceMax')?.value) || 9999999;
  filtered = filtered.filter(p => p.price >= minPrice && p.price <= maxPrice);
  const selectedMaterials = Array.from(document.querySelectorAll('input[name="material"]:checked')).map(cb=>cb.value);
  if(selectedMaterials.length) filtered = filtered.filter(p => selectedMaterials.includes(p.material));
  const searchTerm = document.getElementById('searchInput')?.value.trim().toLowerCase();
  if(searchTerm) filtered = filtered.filter(p => p.name.toLowerCase().includes(searchTerm) || (p.category && p.category.toLowerCase().includes(searchTerm)));
  const sortType = document.getElementById('sortSelect')?.value;
  if(sortType === 'price_asc') filtered.sort((a,b)=>a.price-b.price);
  else if(sortType === 'price_desc') filtered.sort((a,b)=>b.price-a.price);
  else if(sortType === 'name_asc') filtered.sort((a,b)=>a.name.localeCompare(b.name));
  currentProductsList = filtered;
  currentPageNum = 1;
  renderProductsList();
}

function buildFilterWidgets(){
  const catHtml = categoriesData.map(c=>`<label class="category-item"><input type="radio" name="category" value="${c.id}" onchange="applyProductFilters()"> ${c.name} <span class="category-count">${productsData.filter(p=>p.category_id===c.id).length}</span></label>`).join('');
  document.getElementById('categoryFilter').innerHTML = catHtml + '<label class="category-item"><input type="radio" name="category" value="" checked onchange="applyProductFilters()"> Все категории</label>';
  const materialsList = ['Дерево', 'Ткань', 'Металл', 'Комбинированный'];
  const matHtml = materialsList.map(m=>`<label class="checkbox-item"><input type="checkbox" name="material" value="${m}" onchange="applyProductFilters()"> ${m}</label>`).join('');
  document.getElementById('materialFilter').innerHTML = matHtml;
}

// Инициализация
document.addEventListener('DOMContentLoaded', () => {
  buildFilterWidgets();
  renderProductsList();
  updateCartCountDisplay();
  
  document.getElementById('priceMin')?.addEventListener('input', applyProductFilters);
  document.getElementById('priceMax')?.addEventListener('input', applyProductFilters);
  document.getElementById('sortSelect')?.addEventListener('change', applyProductFilters);
  document.getElementById('searchInput')?.addEventListener('input', applyProductFilters);
  document.getElementById('resetFilters')?.addEventListener('click', () => {
    document.querySelectorAll('input[name="category"]').forEach(r=>r.checked=false);
    document.querySelectorAll('input[name="material"]').forEach(c=>c.checked=false);
    document.getElementById('priceMin').value='';
    document.getElementById('priceMax').value='';
    document.getElementById('searchInput').value='';
    document.getElementById('sortSelect').value='default';
    applyProductFilters();
  });
  
  const mobileBtn = document.getElementById('mobileFilterBtn');
  const sidebar = document.getElementById('filtersSidebar');
  const overlay = document.getElementById('mobileOverlay');
  if(mobileBtn && sidebar){
    mobileBtn.addEventListener('click',()=>sidebar.classList.add('open'));
    if(overlay) overlay.addEventListener('click',()=>sidebar.classList.remove('open'));
  }
  
  document.getElementById('subFormFooter')?.addEventListener('submit', async e=>{
    e.preventDefault();
    const email=document.getElementById('subEmailFooter').value.trim();
    const msg=document.getElementById('subMsgFooter'); 
    const err=document.getElementById('subErrFooter');
    if(msg) msg.style.display='none'; 
    if(err) err.style.display='none';
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){ if(err) err.style.display='block'; return; }
    if(msg) msg.style.display='block'; 
    document.getElementById('subEmailFooter').value='';
  });
  
  const storedUser = JSON.parse(localStorage.getItem('pm_user')||'null');
  const profileLinkEl = document.getElementById('profileLink');
  if(profileLinkEl && storedUser) profileLinkEl.textContent = storedUser.name.split(' ')[0];
});

window.goToPage = goToPage;
window.toggleFavorite = toggleFavorite;
window.addProductToCart = addProductToCart;
window.applyProductFilters = applyProductFilters;
</script>
</body>
</html>
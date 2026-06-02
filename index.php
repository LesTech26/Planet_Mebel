<?php
session_start();

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cartCount = array_sum(array_column($cart, 'quantity'));
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

// Обработка выхода из аккаунта
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Получаем товары ИЗ БАЗЫ ДАННЫХ (хиты продаж и новинки)
$hitsProducts = getProducts(['is_hit' => 1]);
$newProducts = getProducts(['is_new' => 1]);

// Слайдеры с вашими локальными картинками
$sliders = [
    [
        'title' => 'Пространство, где живёт <em>уют</em>', 
        'label' => 'Новая коллекция 2025', 
        'desc' => 'Мебель ручной работы из массива дерева', 
        'cta' => 'Смотреть коллекцию', 
        'link' => 'catalog.php', 
        'bg' => '/uploads/slides/slide1.jpg'
    ],
    [
        'title' => 'Минимализм и <em>тепло</em> дерева', 
        'label' => 'Скандинавский стиль', 
        'desc' => 'Простые формы, натуральные материалы', 
        'cta' => 'Каталог диванов', 
        'link' => 'catalog.php', 
        'bg' => '/uploads/slides/slide2.jpg'
    ],
    [
        'title' => 'Отдых как <em>искусство</em>', 
        'label' => 'Спальня мечты', 
        'desc' => 'Кровати и матрасы премиум-класса', 
        'cta' => 'Спальные гарнитуры', 
        'link' => 'catalog.php', 
        'bg' => '/uploads/slides/slide3.jpg'
    ],
];

// Подготавливаем данные для JavaScript
$hitsDataForJs = [];
foreach ($hitsProducts as $p) {
    $hitsDataForJs[] = [
        'id' => $p['id'],
        'name' => $p['name'],
        'category' => $p['category'],
        'price' => (float)$p['price'],
        'old_price' => $p['old_price'] ? (float)$p['old_price'] : null,
        'is_new' => (bool)$p['is_new'],
        'image' => $p['image']
    ];
}

$newDataForJs = [];
foreach ($newProducts as $p) {
    $newDataForJs[] = [
        'id' => $p['id'],
        'name' => $p['name'],
        'category' => $p['category'],
        'price' => (float)$p['price'],
        'old_price' => $p['old_price'] ? (float)$p['old_price'] : null,
        'is_new' => (bool)$p['is_new'],
        'image' => $p['image']
    ];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
<title>Планета Мебели — Мебель для вашего дома</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
.nav a:hover { color: var(--walnut); }

/* БУРГЕР-МЕНЮ */
.burger-menu { display: none; flex-direction: column; justify-content: space-between; width: 30px; height: 21px; cursor: pointer; z-index: 210; }
.burger-menu span { display: block; width: 100%; height: 2px; background: var(--charcoal); transition: all 0.3s ease; border-radius: 2px; }
.burger-menu.active span:nth-child(1) { transform: translateY(9px) rotate(45deg); }
.burger-menu.active span:nth-child(2) { opacity: 0; }
.burger-menu.active span:nth-child(3) { transform: translateY(-9px) rotate(-45deg); }

/* МОБИЛЬНОЕ МЕНЮ (overlay) */
.mobile-nav-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(42,36,32,0.95); z-index: 205; opacity: 0; visibility: hidden; transition: all 0.3s ease; backdrop-filter: blur(10px); }
.mobile-nav-overlay.active { opacity: 1; visibility: visible; }
.mobile-nav { position: fixed; top: 0; right: -100%; width: 280px; height: 100%; background: var(--warm); z-index: 215; padding: 100px 30px 40px; transition: right 0.4s ease; box-shadow: -5px 0 20px rgba(0,0,0,0.1); }
.mobile-nav-overlay.active .mobile-nav { right: 0; }
.mobile-nav ul { list-style: none; }
.mobile-nav li { margin-bottom: 25px; }
.mobile-nav a { text-decoration: none; color: var(--charcoal); font-size: 18px; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 500; transition: color 0.2s; display: block; }
.mobile-nav a:hover { color: var(--walnut); }
.mobile-nav-close { position: absolute; top: 20px; right: 20px; width: 30px; height: 30px; cursor: pointer; background: none; border: none; }
.mobile-nav-close::before, .mobile-nav-close::after { content: ''; position: absolute; top: 14px; right: 4px; width: 22px; height: 2px; background: var(--charcoal); }
.mobile-nav-close::before { transform: rotate(45deg); }
.mobile-nav-close::after { transform: rotate(-45deg); }

.hactions { display: flex; align-items: center; gap: 20px; flex-shrink: 0; position: relative; }
.search-wrap { position: relative; }
.search-wrap input { background: transparent; border: none; border-bottom: 1px solid var(--line); padding: 6px 28px 6px 0; width: 160px; font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 400; color: var(--charcoal); outline: none; }
.search-icon { position: absolute; right: 4px; top: 50%; transform: translateY(-50%); color: var(--mid); font-size: 16px; cursor: pointer; }
.cart-btn { background: none; border: none; cursor: pointer; color: var(--charcoal); display: flex; align-items: center; gap: 6px; font-size: 14px; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 500; }
.cart-count { background: var(--walnut); color: #fff; width: 22px; height: 22px; border-radius: 50%; font-size: 12px; display: flex; align-items: center; justify-content: center; font-weight: 500; }

/* ПРОФИЛЬ С ВЫПАДАЮЩИМ МЕНЮ */
.profile-dropdown { position: relative; }
.profile-link { color: var(--charcoal); text-decoration: none; font-size: 14px; letter-spacing: 0.08em; text-transform: uppercase; white-space: nowrap; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.profile-link i { font-size: 12px; transition: transform 0.2s; }
.profile-dropdown:hover .profile-link i { transform: rotate(180deg); }
.dropdown-menu { position: absolute; top: 100%; right: 0; background: var(--warm); border: 1px solid var(--line); border-radius: 12px; min-width: 180px; padding: 8px 0; opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.2s; z-index: 250; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
.profile-dropdown:hover .dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
.dropdown-menu a { display: flex; align-items: center; gap: 10px; padding: 10px 20px; text-decoration: none; color: var(--charcoal); font-size: 13px; transition: background 0.2s; }
.dropdown-menu a:hover { background: var(--cream); }
.dropdown-menu a i { width: 18px; font-size: 14px; color: var(--walnut); }
.dropdown-menu .logout-item { border-top: 1px solid var(--line); margin-top: 6px; padding-top: 6px; color: var(--red); }
.dropdown-menu .logout-item i { color: var(--red); }
.dropdown-menu .logout-item:hover { background: rgba(176,64,64,0.05); }

/* SLIDER */
.slider-section { padding-top: 76px; width: 100%; max-width: 1440px; margin: 0 auto; padding-left: 24px; padding-right: 24px; }
.main-swiper { height: 560px; width: 100%; border-radius: 16px; overflow: hidden; position: relative; }
.main-swiper .swiper-slide { position: relative; overflow: hidden; display: flex; align-items: flex-end; width: 100%; }
.main-swiper .swiper-slide img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0; }
.slide-overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(42,36,32,0.7) 0%, transparent 60%); z-index: 1; }
.slide-content { position: relative; z-index: 2; padding: 48px 56px; max-width: 650px; }
.slide-label { color: var(--gold); font-size: 13px; letter-spacing: 0.22em; text-transform: uppercase; margin-bottom: 12px; font-weight: 500; }
.slide-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(42px, 6vw, 72px); line-height: 1.1; color: #fff; font-weight: 400; margin-bottom: 18px; }
.slide-title em { font-style: italic; color: var(--gold); }
.slide-desc { color: rgba(255,255,255,0.85); font-size: 16px; margin-bottom: 28px; max-width: 420px; line-height: 1.5; }
.btn-primary { display: inline-block; padding: 14px 32px; background: var(--walnut); color: #fff; text-decoration: none; font-size: 13px; letter-spacing: 0.15em; text-transform: uppercase; border: none; cursor: pointer; transition: background 0.25s; font-weight: 500; border-radius: 40px; }
.btn-primary:hover { background: var(--walnut-l); }
.swiper-pagination-bullet { width: 10px; height: 10px; background: rgba(255,255,255,0.5); opacity: 1; }
.swiper-pagination-bullet-active { background: var(--gold); }

/* SECTION */
.section { max-width: 1440px; margin: 0 auto; padding: 56px 24px; width: 100%; overflow: hidden; }
.sec-head { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 36px; flex-wrap: wrap; gap: 12px; }
.sec-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(32px, 4.5vw, 48px); font-weight: 400; line-height: 1.2; }
.sec-title em { font-style: italic; color: var(--walnut); }
.sec-link { color: var(--walnut); font-size: 14px; letter-spacing: 0.12em; text-transform: uppercase; text-decoration: none; border-bottom: 1px solid var(--walnut); padding-bottom: 3px; white-space: nowrap; font-weight: 500; }

/* CARD */
.product-card { cursor: pointer; position: relative; transition: transform 0.3s; background: var(--warm); border-radius: 16px; overflow: hidden; }
.product-card:hover { transform: translateY(-5px); }
.card-img { width: 100%; aspect-ratio: 3/4; overflow: hidden; background: var(--cream); position: relative; display: flex; align-items: center; justify-content: center; }
.card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
.product-card:hover .card-img img { transform: scale(1.05); }
.card-badge { position: absolute; top: 12px; left: 12px; background: var(--walnut); color: #fff; font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; padding: 4px 10px; border-radius: 6px; z-index: 2; font-weight: 500; }
.card-fav { position: absolute; top: 12px; right: 12px; background: rgba(253,250,245,0.95); border: none; cursor: pointer; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; opacity: 0; font-size: 18px; color: var(--mid); border-radius: 50%; z-index: 2; transition: opacity 0.2s; }
.product-card:hover .card-fav, .card-fav.fav-active { opacity: 1; }
.card-fav.fav-active { color: var(--walnut); }
.card-info { padding: 14px 16px 16px; }
.card-cat { font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--mid); margin-bottom: 6px; font-weight: 500; }
.card-name { font-family: 'Cormorant Garamond', serif; font-size: 18px; font-weight: 500; margin-bottom: 6px; color: var(--charcoal); display: block; }
.card-price { font-size: 16px; color: var(--walnut); display: flex; gap: 10px; align-items: baseline; flex-wrap: wrap; font-weight: 500; }
.card-price-old { font-size: 13px; color: var(--mid); text-decoration: line-through; font-weight: 400; }
.card-add { margin-top: 12px; width: 100%; background: none; border: 1px solid var(--line); padding: 10px; font-size: 12px; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; opacity: 0; transform: translateY(4px); transition: opacity 0.2s, transform 0.2s, background 0.2s; border-radius: 30px; font-weight: 500; }
.product-card:hover .card-add { opacity: 1; transform: translateY(0); }
.card-add:hover { background: var(--walnut); color: #fff; border-color: var(--walnut); }

/* CAROUSEL */
.carousel-swiper { overflow: visible !important; width: 100%; padding: 0 0 12px 0; }
.carousel-swiper .swiper-wrapper { display: flex; }
.carousel-swiper .swiper-slide { width: 280px; flex-shrink: 0; }
.car-nav { display: flex; gap: 12px; margin-top: 28px; justify-content: center; }
.car-btn { width: 44px; height: 44px; border: 1px solid var(--line); background: transparent; cursor: pointer; font-size: 18px; border-radius: 50%; transition: all 0.2s; }
.car-btn:hover { background: var(--walnut); color: #fff; }

/* CATEGORIES */
.cats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; width: 100%; }
.cat-item { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 28px 16px; background: var(--cream); cursor: pointer; text-decoration: none; color: var(--charcoal); gap: 12px; transition: background 0.25s, transform 0.2s; border-radius: 16px; }
.cat-item:hover { background: var(--walnut); color: #fff; transform: translateY(-4px); }
.cat-icon { font-size: 40px; color: var(--walnut); transition: color 0.25s; }
.cat-item:hover .cat-icon { color: #fff; }
.cat-name { font-size: 14px; letter-spacing: 0.1em; text-transform: uppercase; text-align: center; font-weight: 500; }

/* DIVIDER */
.divider { border: none; border-top: 1px solid var(--line); margin: 0 24px; }

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
@media (max-width: 768px) { 
    .nav { display: none; } 
    .burger-menu { display: flex; }
    .slide-content { padding: 32px 32px; } 
    .main-swiper { height: 460px; } 
    .cats-grid { grid-template-columns: repeat(3, 1fr); gap: 14px; } 
    .cat-item { padding: 18px 10px; } 
    .footer-grid { grid-template-columns: 1fr; gap: 28px; } 
    .footer-bottom { flex-direction: column; text-align: center; }
    .search-wrap { display: none; }
    .dropdown-menu { left: auto; right: 0; min-width: 160px; }
}
@media (max-width: 600px) { 
    .logo { font-size: 20px; } 
    .profile-link span { display: none; }
    .profile-link i { display: inline-block; }
    .cart-btn span:not(.cart-count) { display: none; } 
    .slide-title { font-size: clamp(28px, 6vw, 42px); } 
    .slide-content { padding: 24px 24px; } 
    .btn-primary { padding: 10px 22px; font-size: 11px; } 
    .sec-head { flex-direction: column; align-items: flex-start; } 
    .carousel-swiper .swiper-slide { width: 240px; } 
    .section { padding: 32px 16px; } 
    .slider-section { padding-left: 16px; padding-right: 16px; } 
    .divider { margin: 0 16px; } 
}
@media (max-width: 550px) { 
    .cats-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; } 
    .cat-item { padding: 22px 14px; } 
    .cat-icon { font-size: 34px; } 
}
@media (max-width: 480px) { 
    .hinner { padding: 0 14px; } 
    .hactions { gap: 10px; } 
    .cart-count { width: 22px; height: 22px; font-size: 12px; } 
    .main-swiper { height: 400px; } 
    .slider-section { padding-left: 14px; padding-right: 14px; } 
    .slide-label { font-size: 10px; } 
    .slide-desc { font-size: 12px; margin-bottom: 18px; } 
    .card-name { font-size: 15px; } 
    .card-price { font-size: 14px; } 
    .carousel-swiper .swiper-slide { width: 220px; } 
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
      <!-- Профиль с выпадающим меню -->
      <div class="profile-dropdown">
        <a href="#" class="profile-link" id="profileLink">
          <?= $userName ? htmlspecialchars($userName) : 'Войти' ?>
          <i class="fas fa-chevron-down"></i>
        </a>
        <div class="dropdown-menu">
          <?php if ($userName): ?>
            <a href="profile.php"><i class="fas fa-user"></i> Личный кабинет</a>
            <a href="profile.php?tab=orders"><i class="fas fa-shopping-bag"></i> Мои заказы</a>
            <a href="profile.php?tab=favorites"><i class="fas fa-heart"></i> Избранное</a>
            <a href="?logout=1" class="logout-item"><i class="fas fa-sign-out-alt"></i> Выйти</a>
          <?php else: ?>
            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Войти</a>
            <a href="register.php"><i class="fas fa-user-plus"></i> Регистрация</a>
          <?php endif; ?>
        </div>
      </div>
      
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
      <?php if ($userName): ?>
        <li><a href="profile.php">Личный кабинет</a></li>
        <li><a href="?logout=1" style="color: var(--red);">Выйти</a></li>
      <?php else: ?>
        <li><a href="login.php">Войти</a></li>
        <li><a href="register.php">Регистрация</a></li>
      <?php endif; ?>
      <li><a href="cart.php">Корзина</a></li>
    </ul>
  </div>
</div>

<main>
<section class="slider-section">
  <div class="swiper main-swiper" id="mainSwiper">
    <div class="swiper-wrapper" id="sliderWrapper"></div>
    <div class="swiper-pagination"></div>
  </div>
</section>

<section class="section">
  <div class="sec-head">
    <h2 class="sec-title">Хиты <em>продаж</em></h2>
    <a href="catalog.php" class="sec-link">Весь каталог →</a>
  </div>
  <div class="swiper carousel-swiper" id="hitsSwiper">
    <div class="swiper-wrapper" id="hitsWrapper"></div>
  </div>
  <div class="car-nav">
    <button class="car-btn" id="hitsPrev">←</button>
    <button class="car-btn" id="hitsNext">→</button>
  </div>
</section>

<hr class="divider">

<section class="section">
  <div class="sec-head"><h2 class="sec-title">Все <em>категории</em></h2></div>
  <div class="cats-grid">
    <a href="catalog.php?category=1" class="cat-item"><i class="fas fa-couch fa-2x"></i><span class="cat-name">Диваны</span></a>
    <a href="catalog.php?category=2" class="cat-item"><i class="fas fa-chair fa-2x"></i><span class="cat-name">Кресла</span></a>
    <a href="catalog.php?category=3" class="cat-item"><i class="fas fa-bed fa-2x"></i><span class="cat-name">Кровати</span></a>
    <a href="catalog.php?category=4" class="cat-item"><i class="fas fa-archive fa-2x"></i><span class="cat-name">Шкафы</span></a>
    <a href="catalog.php?category=5" class="cat-item"><i class="fas fa-utensils fa-2x"></i><span class="cat-name">Столы</span></a>
    <a href="catalog.php?category=6" class="cat-item"><i class="fas fa-book fa-2x"></i><span class="cat-name">Стеллажи</span></a>
    <a href="catalog.php?category=7" class="cat-item"><i class="fas fa-leaf fa-2x"></i><span class="cat-name">Декор</span></a>
    <a href="catalog.php?category=8" class="cat-item"><i class="fas fa-lightbulb fa-2x"></i><span class="cat-name">Освещение</span></a>
  </div>
</section>

<hr class="divider">

<section class="section">
  <div class="sec-head">
    <h2 class="sec-title">Новинки <em>сезона</em></h2>
    <a href="catalog.php" class="sec-link">Все новинки →</a>
  </div>
  <div class="swiper carousel-swiper" id="newSwiper">
    <div class="swiper-wrapper" id="newWrapper"></div>
  </div>
  <div class="car-nav">
    <button class="car-btn" id="newPrev">←</button>
    <button class="car-btn" id="newNext">→</button>
  </div>
</section>
</main>

<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div>
        <div class="footer-logo">Планета <span>Мебели</span></div>
        <p style="font-size: 13px; margin-bottom: 12px;">Мебель для вашего дома</p>
        <div class="socials">
          <a href="#" class="social-link"><i class="fab fa-vk"></i></a>
          <a href="#" class="social-link"><i class="fab fa-telegram"></i></a>
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

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
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
const slidersData = <?= json_encode($sliders) ?>;
const hitsData = <?= json_encode($hitsDataForJs) ?>;
const newData = <?= json_encode($newDataForJs) ?>;

// Функции корзины и избранного
const getCart = () => JSON.parse(localStorage.getItem('pm_cart') || '[]');
const saveCart = c => localStorage.setItem('pm_cart', JSON.stringify(c));
const getFavs = () => JSON.parse(localStorage.getItem('pm_favs') || '[]');
const saveFavs = f => localStorage.setItem('pm_favs', JSON.stringify(f));
const isFav = id => getFavs().some(f => f.id === id);

function updateCartCount() {
    const n = getCart().reduce((s, i) => s + i.quantity, 0);
    const el = document.getElementById('cartCount');
    if (el) el.textContent = n;
}

function addToCart(product) {
    const cart = getCart();
    const key = product.productId + '__' + JSON.stringify(product.options || {});
    const existing = cart.find(i => i._key === key);
    if (existing) {
        existing.quantity += product.quantity || 1;
    } else {
        cart.push({ ...product, _key: key, quantity: product.quantity || 1 });
    }
    saveCart(cart);
    updateCartCount();
    showToast('«' + product.name + '» добавлен в корзину');
}

function toggleFav(id, name, price, image) {
    const favs = getFavs();
    const idx = favs.findIndex(f => f.id === id);
    if (idx > -1) {
        favs.splice(idx, 1);
    } else {
        favs.push({ id, name, price, image });
    }
    saveFavs(favs);
    return idx === -1;
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

function renderCard(product) {
    const fav = isFav(product.id);
    const name = String(product.name).replace(/'/g, "\\'");
    const imageUrl = product.image && product.image !== '' ? product.image : '/uploads/no-image.webp';
    
    return `<div class="swiper-slide">
        <div class="product-card" onclick="location.href='product.php?id=${product.id}'">
            <div class="card-img">
                <img src="${imageUrl}" alt="${product.name}" onerror="this.src='/uploads/no-image.webp'">
                ${product.is_new ? '<span class="card-badge">Новинка</span>' : ''}
                <button class="card-fav${fav ? ' fav-active' : ''}" onclick="event.stopPropagation(); handleFav(this, ${product.id}, '${name}', ${product.price}, '${imageUrl}')">${fav ? '♥' : '♡'}</button>
            </div>
            <div class="card-info">
                <p class="card-cat">${product.category || 'Мебель'}</p>
                <a class="card-name" href="product.php?id=${product.id}">${product.name}</a>
                <div class="card-price">
                    <span>${Number(product.price).toLocaleString('ru-RU')} ₽</span>
                    ${product.old_price ? '<span class="card-price-old">' + Number(product.old_price).toLocaleString('ru-RU') + ' ₽</span>' : ''}
                </div>
                <button class="card-add" onclick="event.stopPropagation(); addToCart({productId: ${product.id}, name: '${name}', price: ${product.price}, quantity: 1, options: {}})">В корзину</button>
            </div>
        </div>
    </div>`;
}

function handleFav(btn, id, name, price, image) {
    const added = toggleFav(id, name, price, image);
    btn.textContent = added ? '♥' : '♡';
    btn.classList.toggle('fav-active', added);
    showToast(added ? '«' + name + '» добавлен в избранное' : '«' + name + '» удалён из избранного');
}

function initSlider() {
    const wrapper = document.getElementById('sliderWrapper');
    if (!wrapper) return;
    
    wrapper.innerHTML = slidersData.map(s => `
        <div class="swiper-slide">
            <img src="${s.bg}" alt="Slide" onerror="this.style.display='none'">
            <div class="slide-overlay"></div>
            <div class="slide-content">
                <p class="slide-label">${s.label}</p>
                <h2 class="slide-title">${s.title}</h2>
                <p class="slide-desc">${s.desc}</p>
                <a href="${s.link}" class="btn-primary">${s.cta}</a>
            </div>
        </div>
    `).join('');
    
    new Swiper('#mainSwiper', {
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
        effect: 'fade',
        fadeEffect: { crossFade: true },
        pagination: { el: '.swiper-pagination', clickable: true },
        speed: 900
    });
}

function initCarousel(wrapperId, items, prevId, nextId, swiperId) {
    const wrapper = document.getElementById(wrapperId);
    if (!wrapper) return;
    if (!items || items.length === 0) {
        wrapper.innerHTML = '<div class="swiper-slide" style="width:100%; text-align:center;">Нет товаров</div>';
        return;
    }
    wrapper.innerHTML = items.map(renderCard).join('');
    new Swiper('#' + swiperId, {
        slidesPerView: 'auto',
        spaceBetween: 20,
        navigation: { prevEl: '#' + prevId, nextEl: '#' + nextId },
        breakpoints: {
            320: { spaceBetween: 14 },
            600: { spaceBetween: 18 },
            1024: { spaceBetween: 24 }
        }
    });
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
(function init() {
    updateCartCount();
})();

initSlider();
initCarousel('hitsWrapper', hitsData, 'hitsPrev', 'hitsNext', 'hitsSwiper');
initCarousel('newWrapper', newData, 'newPrev', 'newNext', 'newSwiper');
</script>
</body>
</html>
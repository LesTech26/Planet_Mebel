<?php
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user = getUser($_SESSION['user_id']);
$userName = $user['name'];

// Получаем заказы пользователя из БД
$orders = getUserOrders($_SESSION['user_id']);

// Получаем избранное пользователя из БД
$favorites = getUserFavorites($_SESSION['user_id']);

// Обновление профиля
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] . ' ' . ($_POST['surname'] ?? ''));
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    db()->update('users', [
        'name' => $name,
        'phone' => $phone,
        'address' => $address
    ], 'id = :id', ['id' => $_SESSION['user_id']]);
    
    $_SESSION['user_name'] = $name;
    $success = true;
    $user = getUser($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
<title>Личный кабинет — Планета Мебели</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
    --red: #B04040;
}
html, body { min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden; width: 100%; }
body { font-family: 'Jost', sans-serif; background: var(--warm); color: var(--charcoal); font-weight: 400; font-size: 16px; line-height: 1.5; flex: 1; display: flex; flex-direction: column; }
main { flex: 1; }
footer { margin-top: auto; }

/* HEADER */
.header { position: fixed; top: 0; left: 0; right: 0; z-index: 200; background: rgba(253,250,245,0.96); backdrop-filter: blur(14px); border-bottom: 1px solid var(--line); height: 70px; display: flex; align-items: center; width: 100%; }
.hinner { width: 100%; max-width: 1400px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; gap: 20px; }
.logo { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 600; letter-spacing: 0.04em; color: var(--charcoal); text-decoration: none; white-space: nowrap; flex-shrink: 0; }
.logo span { color: var(--walnut); font-style: italic; }
.nav { display: flex; gap: 30px; list-style: none; margin: 0; padding: 0; }
.nav a { text-decoration: none; color: var(--charcoal); font-size: 14px; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 500; transition: color 0.2s; white-space: nowrap; }
.nav a:hover { color: var(--walnut); }
.burger-menu { display: none; flex-direction: column; justify-content: space-between; width: 30px; height: 21px; cursor: pointer; z-index: 210; }
.burger-menu span { display: block; width: 100%; height: 2px; background: var(--charcoal); transition: all 0.3s ease; border-radius: 2px; }
.burger-menu.active span:nth-child(1) { transform: translateY(9px) rotate(45deg); }
.burger-menu.active span:nth-child(2) { opacity: 0; }
.burger-menu.active span:nth-child(3) { transform: translateY(-9px) rotate(-45deg); }
.mobile-nav-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(42,36,32,0.95); z-index: 205; opacity: 0; visibility: hidden; transition: all 0.3s ease; backdrop-filter: blur(10px); }
.mobile-nav-overlay.active { opacity: 1; visibility: visible; }
.mobile-nav { position: fixed; top: 0; right: -100%; width: 280px; height: 100%; background: var(--warm); z-index: 215; padding: 90px 25px 30px; transition: right 0.4s ease; box-shadow: -5px 0 20px rgba(0,0,0,0.1); }
.mobile-nav-overlay.active .mobile-nav { right: 0; }
.mobile-nav ul { list-style: none; }
.mobile-nav li { margin-bottom: 25px; }
.mobile-nav a { text-decoration: none; color: var(--charcoal); font-size: 18px; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 500; transition: color 0.2s; display: block; }
.mobile-nav a:hover { color: var(--walnut); }
.mobile-nav-close { position: absolute; top: 20px; right: 20px; width: 30px; height: 30px; cursor: pointer; background: none; border: none; }
.mobile-nav-close::before, .mobile-nav-close::after { content: ''; position: absolute; top: 14px; right: 4px; width: 22px; height: 2px; background: var(--charcoal); }
.mobile-nav-close::before { transform: rotate(45deg); }
.mobile-nav-close::after { transform: rotate(-45deg); }
.hactions { display: flex; align-items: center; gap: 18px; flex-shrink: 0; }
.search-wrap { position: relative; }
.search-wrap input { background: transparent; border: none; border-bottom: 1px solid var(--line); padding: 5px 25px 5px 0; width: 150px; font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 400; color: var(--charcoal); outline: none; }
.search-icon { position: absolute; right: 4px; top: 50%; transform: translateY(-50%); color: var(--mid); font-size: 14px; cursor: pointer; }
.cart-btn { background: none; border: none; cursor: pointer; color: var(--charcoal); display: flex; align-items: center; gap: 5px; font-size: 13px; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 500; }
.cart-count { background: var(--walnut); color: #fff; width: 20px; height: 20px; border-radius: 50%; font-size: 11px; display: flex; align-items: center; justify-content: center; font-weight: 500; }
.logout-btn { background: none; border: none; cursor: pointer; color: var(--charcoal); font-size: 13px; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 500; transition: color 0.2s; }
.logout-btn:hover { color: var(--red); }

/* PROFILE PAGE - НОВАЯ ВЕРСИЯ С ВЫДВИГАЮЩИМСЯ САЙДБАРОМ */
.profile-page { padding-top: 85px; max-width: 1400px; margin: 0 auto; padding-left: 20px; padding-right: 20px; width: 100%; }

/* КНОПКА ДЛЯ ВЫЗОВА ПРОФИЛЯ (ТОЛЬКО НА МОБИЛЬНЫХ) */
.profile-toggle-btn {
    display: none;
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 150;
    background: var(--walnut);
    color: #fff;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    font-size: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    transition: all 0.3s;
}
.profile-toggle-btn:hover { background: var(--walnut-l); transform: scale(1.05); }

/* ВЫДВИГАЮЩИЙСЯ САЙДБАР (ТОЛЬКО НА МОБИЛЬНЫХ) */
.profile-sidebar-mobile {
    position: fixed;
    top: 0;
    left: -280px;
    width: 280px;
    height: 100%;
    background: var(--warm);
    z-index: 300;
    padding: 85px 20px 20px;
    transition: left 0.3s ease;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    overflow-y: auto;
}
.profile-sidebar-mobile.open { left: 0; }
.profile-sidebar-mobile-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 295;
    display: none;
}
.profile-sidebar-mobile-overlay.open { display: block; }

/* ДЕСКТОПНЫЙ САЙДБАР */
.profile-sidebar-desktop {
    position: sticky;
    top: 85px;
    height: fit-content;
}

/* УМЕНЬШЕННАЯ КАРТОЧКА ПОЛЬЗОВАТЕЛЯ */
.user-card { 
    background: var(--cream); 
    padding: 16px; 
    text-align: center; 
    margin-bottom: 16px; 
    border-radius: 12px; 
}
.user-avatar { 
    width: 60px; 
    height: 60px; 
    border-radius: 50%; 
    background: var(--walnut); 
    color: #fff; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-family: 'Cormorant Garamond', serif; 
    font-size: 28px; 
    margin: 0 auto 10px; 
}
.user-name { 
    font-family: 'Cormorant Garamond', serif; 
    font-size: 16px; 
    font-weight: 500; 
    margin-bottom: 4px; 
    word-break: break-word; 
}
.user-email { 
    font-size: 11px; 
    color: var(--mid); 
    word-break: break-word; 
}

/* НАВИГАЦИЯ В СТОЛБИК */
.profile-nav { 
    display: flex; 
    flex-direction: column; 
    gap: 4px; 
}
.tab-btn { 
    background: none; 
    border: none; 
    text-align: left; 
    padding: 10px 14px; 
    font-family: 'Jost', sans-serif; 
    font-size: 13px; 
    letter-spacing: 0.08em; 
    text-transform: uppercase; 
    color: var(--mid); 
    cursor: pointer; 
    border-left: 3px solid transparent; 
    transition: all 0.2s; 
    font-weight: 500; 
    width: 100%; 
    border-radius: 8px;
}
.tab-btn:hover { color: var(--charcoal); background: rgba(124,92,62,0.05); }
.tab-btn.active { 
    color: var(--walnut); 
    border-left-color: var(--walnut); 
    background: rgba(124,92,62,0.08); 
}

/* ОСНОВНОЙ КОНТЕНТ */
.profile-layout { display: grid; grid-template-columns: 260px 1fr; gap: 40px; }
.tab-panel { display: none; }
.tab-panel.active { display: block; }
.tab-panel h2 { font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 400; margin-bottom: 20px; }

/* ORDERS */
.order-card { border: 1px solid var(--line); margin-bottom: 16px; border-radius: 12px; overflow: hidden; cursor: pointer; }
.order-header { padding: 14px 18px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; background: var(--warm); }
.order-number { font-family: 'Cormorant Garamond', serif; font-size: 16px; font-weight: 500; }
.order-date { font-size: 11px; color: var(--mid); }
.order-status { font-size: 10px; padding: 3px 10px; border-radius: 20px; text-transform: uppercase; font-weight: 500; display: inline-block; }
.status-new { background: rgba(201,169,110,0.15); color: #9A7030; }
.status-processing { background: rgba(201,169,110,0.15); color: #9A7030; }
.status-delivered { background: rgba(74,124,89,0.12); color: var(--green); }
.status-cancelled { background: rgba(176,64,64,0.1); color: var(--red); }
.order-total { margin-left: auto; font-weight: 500; font-size: 15px; }
.order-chevron { margin-left: 8px; transition: transform 0.3s; font-size: 11px; }
.order-card.open .order-chevron { transform: rotate(180deg); }
.order-items { display: none; padding: 14px 18px; background: var(--cream); border-top: 1px solid var(--line); }
.order-card.open .order-items { display: block; }
.order-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--line); flex-wrap: wrap; }
.order-item:last-child { border-bottom: none; }
.order-item-img { width: 45px; height: 45px; background-size: cover; background-position: center; border-radius: 8px; flex-shrink: 0; }
.order-item-info { flex: 1; min-width: 100px; }
.order-item-name { font-family: 'Cormorant Garamond', serif; font-size: 13px; font-weight: 500; margin-bottom: 2px; }
.order-item-price { font-size: 11px; color: var(--walnut); }

/* FAVORITES */
.favorites-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
.fav-card { cursor: pointer; transition: transform 0.3s; }
.fav-card:hover { transform: translateY(-3px); }
.fav-card-img { aspect-ratio: 3/4; overflow: hidden; background: var(--cream); margin-bottom: 10px; border-radius: 10px; position: relative; }
.fav-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
.fav-card:hover .fav-card-img img { transform: scale(1.03); }
.fav-remove { position: absolute; top: 8px; right: 8px; background: rgba(253,250,245,0.95); border: none; width: 24px; height: 24px; border-radius: 50%; cursor: pointer; font-size: 12px; color: var(--mid); transition: all 0.2s; z-index: 2; }
.fav-remove:hover { background: var(--red); color: #fff; }
.fav-card-name { font-family: 'Cormorant Garamond', serif; font-size: 14px; font-weight: 500; margin-bottom: 4px; word-break: break-word; }
.fav-card-price { font-size: 13px; color: var(--walnut); font-weight: 500; }

/* PROFILE FORM */
.profile-form { max-width: 500px; width: 100%; }
.profile-form .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.profile-form .form-group { margin-bottom: 14px; }
.profile-form label { display: block; font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--mid); margin-bottom: 5px; font-weight: 500; }
.profile-form input, .profile-form textarea { width: 100%; padding: 10px; border: 1px solid var(--line); background: var(--warm); font-family: 'Jost', sans-serif; border-radius: 8px; font-size: 13px; }
.profile-form input:focus, .profile-form textarea:focus { outline: none; border-color: var(--walnut); }
.btn-save { background: var(--walnut); color: #fff; border: none; padding: 10px 24px; cursor: pointer; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; margin-top: 8px; border-radius: 30px; transition: background 0.2s; font-weight: 500; }
.btn-save:hover { background: var(--walnut-l); }
.empty-state { text-align: center; padding: 40px 20px; color: var(--mid); background: var(--cream); border-radius: 12px; font-size: 13px; }
.success-message { color: var(--green); margin-bottom: 16px; padding: 10px; background: rgba(74,124,89,0.1); border-radius: 8px; text-align: center; font-size: 12px; }

/* FOOTER */
footer { background: var(--charcoal); color: rgba(255,255,255,0.72); font-size: 12px; width: 100%; margin-top: 40px; }
.footer-inner { max-width: 1400px; margin: 0 auto; padding: 35px 20px 20px; }
.footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 25px; margin-bottom: 25px; }
.footer-logo { font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 600; color: #fff; margin-bottom: 8px; }
.footer-logo span { color: var(--gold); font-style: italic; }
.fcol h4 { color: #fff; font-size: 11px; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 12px; font-weight: 600; }
.fcol ul { list-style: none; }
.fcol li { margin-bottom: 6px; }
.fcol a { color: rgba(255,255,255,0.6); text-decoration: none; font-size: 11px; transition: color 0.2s; word-break: break-word; }
.fcol a:hover { color: var(--gold); }
.sub-form { display: flex; margin-top: 6px; flex-wrap: wrap; }
.sub-form input { flex: 1; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-right: none; padding: 8px 10px; color: #fff; font-size: 11px; border-radius: 30px 0 0 30px; min-width: 80px; }
.sub-form button { background: var(--walnut); border: none; color: #fff; padding: 8px 15px; cursor: pointer; font-size: 11px; border-radius: 0 30px 30px 0; font-weight: 500; }
.footer-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px; font-size: 10px; }
.socials { display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap; }
.social-link { width: 28px; height: 28px; border: 1px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.6); text-decoration: none; font-size: 11px; border-radius: 50%; transition: all 0.2s; }
.social-link:hover { background: var(--gold); border-color: var(--gold); color: #fff; }

/* TOAST */
.toast { position: fixed; bottom: 20px; right: 20px; z-index: 999; background: var(--charcoal); color: #fff; padding: 8px 16px; font-size: 12px; transform: translateY(100px); opacity: 0; transition: all 0.35s ease; display: flex; align-items: center; gap: 6px; border-radius: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); font-weight: 500; }
.toast.show { transform: translateY(0); opacity: 1; }
.toast-icon { color: var(--gold); font-size: 14px; }

/* АДАПТАЦИЯ */
@media (max-width: 992px) {
    .profile-layout { grid-template-columns: 1fr; gap: 25px; }
    .profile-sidebar-desktop { display: none; }
    .profile-toggle-btn { display: flex; align-items: center; justify-content: center; }
    .favorites-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; }
}
@media (max-width: 768px) {
    .nav { display: none; }
    .burger-menu { display: flex; }
    .profile-page { padding-top: 75px; padding-left: 12px; padding-right: 12px; }
    .hinner { padding: 0 12px; }
    .logo { font-size: 18px; }
    .search-wrap { display: none; }
    .cart-btn span:not(.cart-count) { display: none; }
    .hactions { gap: 8px; }
    .logout-btn { font-size: 11px; }
    .tab-panel h2 { font-size: 20px; margin-bottom: 15px; }
    .order-header { padding: 10px 12px; gap: 6px; }
    .order-number { font-size: 13px; }
    .order-date { font-size: 10px; }
    .order-status { font-size: 9px; padding: 2px 8px; }
    .order-total { font-size: 12px; }
    .order-items { padding: 10px 12px; }
    .favorites-grid { gap: 12px; }
    .fav-card-name { font-size: 12px; }
    .fav-card-price { font-size: 11px; }
    .profile-form .form-row { grid-template-columns: 1fr; gap: 0; }
    .profile-form .form-group { margin-bottom: 12px; }
    .btn-save { width: 100%; text-align: center; }
    .footer-grid { gap: 20px; }
    .footer-inner { padding: 25px 12px 15px; }
    .footer-bottom { flex-direction: column; text-align: center; }
    .sub-form { flex-direction: column; }
    .sub-form input { border-radius: 30px; border-right: 1px solid rgba(255,255,255,0.2); margin-bottom: 6px; }
    .sub-form button { border-radius: 30px; }
}
@media (max-width: 576px) {
    .profile-page { padding-left: 10px; padding-right: 10px; }
    .hinner { padding: 0 10px; }
    .logo { font-size: 16px; }
    .cart-count { width: 18px; height: 18px; font-size: 9px; }
    .favorites-grid { grid-template-columns: 1fr; gap: 15px; }
    .order-header { flex-direction: column; align-items: flex-start; }
    .order-total { margin-left: 0; }
    .order-chevron { margin-left: auto; }
    .order-item { flex-direction: column; align-items: flex-start; gap: 6px; }
    .order-item-img { width: 50px; height: 50px; }
    .profile-toggle-btn { width: 45px; height: 45px; font-size: 20px; bottom: 15px; left: 15px; }
}
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
      <button class="logout-btn" onclick="logout()">Выйти</button>
      <button class="cart-btn" onclick="location.href='cart.php'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <span class="cart-count" id="cartCount">0</span>
      </button>
    </div>
  </div>
</header>

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

<!-- Кнопка для вызова профиля (на мобильных) -->
<button class="profile-toggle-btn" id="profileToggleBtn">👤</button>

<!-- Выдвигающийся сайдбар (на мобильных) -->
<div class="profile-sidebar-mobile-overlay" id="profileSidebarOverlay"></div>
<div class="profile-sidebar-mobile" id="profileSidebarMobile">
    <div class="user-card">
        <div class="user-avatar"><?= mb_substr($user['name'], 0, 1) ?></div>
        <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
        <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
    </div>
    <nav class="profile-nav">
        <button class="tab-btn active" data-tab="orders">📦 История заказов</button>
        <button class="tab-btn" data-tab="favorites">♡ Избранное</button>
        <button class="tab-btn" data-tab="profile">✎ Мой профиль</button>
    </nav>
</div>

<main class="profile-page">
    <div class="profile-layout">
        <!-- Десктопный сайдбар -->
        <aside class="profile-sidebar-desktop">
            <div class="user-card">
                <div class="user-avatar"><?= mb_substr($user['name'], 0, 1) ?></div>
                <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
                <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <nav class="profile-nav">
                <button class="tab-btn active" data-tab="orders">📦 История заказов</button>
                <button class="tab-btn" data-tab="favorites">♡ Избранное</button>
                <button class="tab-btn" data-tab="profile">✎ Мой профиль</button>
            </nav>
        </aside>

        <div class="profile-content">
            <div class="tab-panel active" id="tab-orders">
                <h2>История заказов</h2>
                <?php if (empty($orders)): ?>
                    <div class="empty-state">У вас пока нет заказов</div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <div class="order-card" id="order-<?= $order['id'] ?>" onclick="toggleOrder(<?= $order['id'] ?>)">
                        <div class="order-header">
                            <div class="order-number"><?= htmlspecialchars($order['order_number']) ?></div>
                            <div class="order-date"><?= date('d.m.Y', strtotime($order['created_at'])) ?></div>
                            <span class="order-status status-<?= $order['status'] ?>">
                                <?php 
                                $statusMap = ['new' => 'Новый', 'processing' => 'В обработке', 'delivered' => 'Доставлен', 'cancelled' => 'Отменён'];
                                echo $statusMap[$order['status']] ?? $order['status'];
                                ?>
                            </span>
                            <div class="order-total"><?= formatPrice($order['total']) ?></div>
                            <span class="order-chevron">▼</span>
                        </div>
                        <div class="order-items" id="order-items-<?= $order['id'] ?>">
                            <div style="text-align:center; padding:12px;">Загрузка...</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="tab-panel" id="tab-favorites">
                <h2>Избранное</h2>
                <div class="favorites-grid" id="favoritesGrid">
                    <?php if (empty($favorites)): ?>
                        <div class="empty-state" style="grid-column: 1/-1;">В избранном пока ничего нет</div>
                    <?php else: ?>
                        <?php foreach ($favorites as $fav): ?>
                        <div class="fav-card" onclick="location.href='product.php?id=<?= $fav['id'] ?>'">
                            <div class="fav-card-img">
                                <img src="<?= htmlspecialchars($fav['image']) ?>" alt="<?= htmlspecialchars($fav['name']) ?>" onerror="this.src='/uploads/no-image.webp'">
                                <button class="fav-remove" onclick="event.stopPropagation(); removeFromFavorites(<?= $fav['id'] ?>, this)">✕</button>
                            </div>
                            <div class="fav-card-name"><?= htmlspecialchars($fav['name']) ?></div>
                            <div class="fav-card-price"><?= formatPrice($fav['price']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-panel" id="tab-profile">
                <h2>Мой профиль</h2>
                <form class="profile-form" method="POST">
                    <?php if ($success): ?>
                        <div class="success-message">✓ Данные успешно сохранены</div>
                    <?php endif; ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Имя</label>
                            <input type="text" name="name" value="<?= htmlspecialchars(explode(' ', $user['name'])[0] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Фамилия</label>
                            <input type="text" name="surname" value="<?= htmlspecialchars(explode(' ', $user['name'])[1] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Адрес</label>
                        <textarea name="address" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn-save">Сохранить изменения</button>
                </form>
            </div>
        </div>
    </div>
</main>

<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div><div class="footer-logo">Планета <span>Мебели</span></div><p style="font-size: 11px;">Мебель для вашего дома</p><div class="socials"><a href="#" class="social-link">vk</a><a href="#" class="social-link">tg</a><a href="#" class="social-link">in</a><a href="#" class="social-link">yt</a></div></div>
      <div class="fcol"><h4>Каталог</h4><ul><li><a href="catalog.php">Диваны</a></li><li><a href="catalog.php">Кровати</a></li><li><a href="catalog.php">Столы</a></li><li><a href="catalog.php">Шкафы</a></li></ul></div>
      <div class="fcol"><h4>Компания</h4><ul><li><a href="about.php">О нас</a></li><li><a href="delivery.php">Доставка</a></li><li><a href="contacts.php">Контакты</a></li></ul></div>
      <div class="fcol"><h4>Подписка</h4><form class="sub-form" id="subFormFooter"><input type="email" id="subEmailFooter" placeholder="ваш@email.ru"><button type="submit">→</button></form><p id="subMsgFooter" style="font-size:10px;margin-top:5px;color:var(--gold);display:none">✓ Вы подписались!</p><p id="subErrFooter" style="font-size:10px;margin-top:5px;color:#e07070;display:none">Введите email</p></div>
    </div>
    <div class="footer-bottom"><span>© 2025 Планета Мебели</span><span>Политика конфиденциальности</span></div>
  </div>
</footer>

<div class="toast" id="toast"><span class="toast-icon">✓</span><span id="toastMsg"></span></div>

<script>
// Бургер-меню для основной навигации
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

// Выдвигающийся сайдбар для профиля (на мобильных)
const profileToggleBtn = document.getElementById('profileToggleBtn');
const profileSidebarMobile = document.getElementById('profileSidebarMobile');
const profileSidebarOverlay = document.getElementById('profileSidebarOverlay');

function openProfileSidebar() {
    profileSidebarMobile.classList.add('open');
    profileSidebarOverlay.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeProfileSidebar() {
    profileSidebarMobile.classList.remove('open');
    profileSidebarOverlay.classList.remove('open');
    document.body.style.overflow = '';
}

if (profileToggleBtn) {
    profileToggleBtn.addEventListener('click', openProfileSidebar);
}
if (profileSidebarOverlay) {
    profileSidebarOverlay.addEventListener('click', closeProfileSidebar);
}

// Переключение вкладок
function switchTab(tabName) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    
    document.getElementById(`tab-${tabName}`).classList.add('active');
    document.querySelectorAll(`.tab-btn[data-tab="${tabName}"]`).forEach(b => b.classList.add('active'));
    
    // Закрываем мобильный сайдбар после выбора вкладки
    if (window.innerWidth <= 992) {
        closeProfileSidebar();
    }
}

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        switchTab(btn.dataset.tab);
    });
});

function toggleOrder(orderId) {
    const card = document.getElementById('order-' + orderId);
    if (!card) return;
    card.classList.toggle('open');
    
    const itemsDiv = document.getElementById('order-items-' + orderId);
    if (itemsDiv && itemsDiv.innerHTML.includes('Загрузка')) {
        fetch('/api/get-order-items.php?order_id=' + orderId)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.items.length) {
                    itemsDiv.innerHTML = data.items.map(item => `
                        <div class="order-item">
                            <div class="order-item-img" style="background-image: url('${item.image || '/uploads/no-image.webp'}')"></div>
                            <div class="order-item-info">
                                <div class="order-item-name">${escapeHtml(item.product_name)} × ${item.quantity}</div>
                                <div class="order-item-price">${formatPrice(item.price)}</div>
                            </div>
                            <div>${formatPrice(item.price * item.quantity)}</div>
                        </div>
                    `).join('');
                } else {
                    itemsDiv.innerHTML = '<div style="text-align:center; padding:12px;">Нет товаров</div>';
                }
            })
            .catch(err => {
                itemsDiv.innerHTML = '<div style="text-align:center; padding:12px;">Ошибка загрузки</div>';
            });
    }
}

function removeFromFavorites(productId, btn) {
    fetch('/api/favorites.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'remove', product_id: productId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const card = btn.closest('.fav-card');
            if (card) card.remove();
            showToast('Удалено из избранного');
            
            const grid = document.getElementById('favoritesGrid');
            if (grid && grid.children.length === 0) {
                grid.innerHTML = '<div class="empty-state" style="grid-column: 1/-1;">В избранном пока ничего нет</div>';
            }
        }
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
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

function logout() {
    localStorage.removeItem('pm_user');
    localStorage.removeItem('pm_cart');
    window.location.href = 'index.php';
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('pm_cart') || '[]');
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    const el = document.getElementById('cartCount');
    if (el) el.textContent = count;
}

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

document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
    
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        switchTab(tab);
    }
    
    const user = JSON.parse(localStorage.getItem('pm_user') || 'null');
    const profileLink = document.getElementById('profileLink');
    if (profileLink && user) profileLink.textContent = user.name.split(' ')[0];
});

window.toggleOrder = toggleOrder;
window.removeFromFavorites = removeFromFavorites;
window.formatPrice = formatPrice;
window.switchTab = switchTab;
</script>
</body>
</html>
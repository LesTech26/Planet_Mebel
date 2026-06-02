<?php
session_start();

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cartCount = array_sum(array_column($cart, 'quantity'));
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Доставка и оплата — Планета Мебели</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&family=Jost:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
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
        }

        html,
        body {
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
            margin-right: 16px;
        }

        .nav a:hover,
        .nav a.active {
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

        /* DELIVERY PAGE */
        .delivery-page {
            padding-top: 100px;
            max-width: 1440px;
            margin: 0 auto;
            padding-left: 24px;
            padding-right: 24px;
            width: 100%;
        }

        .delivery-hero {
            text-align: center;
            margin-bottom: 60px;
        }

        .delivery-hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(42px, 6vw, 64px);
            font-weight: 400;
            margin-bottom: 20px;
        }

        .delivery-hero h1 em {
            font-style: italic;
            color: var(--walnut);
        }

        .delivery-hero p {
            color: var(--mid);
            max-width: 600px;
            margin: 0 auto;
            font-size: 17px;
        }

        /* DELIVERY GRID */
        .delivery-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            margin-bottom: 70px;
        }

        .delivery-card {
            background: var(--cream);
            padding: 45px 30px;
            text-align: center;
            border-radius: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .delivery-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        }

        .delivery-icon {
            font-size: 56px;
            margin-bottom: 24px;
        }

        .delivery-card h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 500;
            margin-bottom: 14px;
        }

        .delivery-card p {
            font-size: 15px;
            color: var(--mid);
            line-height: 1.55;
        }

        .delivery-card .price {
            font-size: 20px;
            color: var(--walnut);
            font-weight: 500;
            margin-top: 20px;
        }

        /* INFO SECTION */
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 70px;
        }

        .info-block {
            background: var(--cream);
            padding: 45px;
            border-radius: 20px;
        }

        .info-block h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 36px;
            font-weight: 400;
            margin-bottom: 25px;
        }

        .info-block h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 24px 0 10px;
        }

        .info-block p {
            color: var(--mid);
            font-size: 15px;
            line-height: 1.6;
        }

        .info-block ul {
            list-style: none;
        }

        .info-block li {
            padding: 12px 0;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
        }

        .info-block li::before {
            content: "✓";
            color: var(--green);
            font-weight: 500;
            font-size: 16px;
        }

        .payment-icons {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .payment-icon {
            background: #fff;
            padding: 10px 22px;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        /* FAQ SECTION */
        .faq-section {
            margin-bottom: 70px;
        }

        .faq-section h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 42px;
            font-weight: 400;
            margin-bottom: 40px;
            text-align: center;
        }

        .faq-item {
            border-bottom: 1px solid var(--line);
            margin-bottom: 8px;
        }

        .faq-question {
            padding: 22px 0;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            font-weight: 500;
            font-size: 18px;
            transition: color 0.2s;
        }

        .faq-question:hover {
            color: var(--walnut);
        }

        .faq-question span:last-child {
            transition: transform 0.3s;
            font-size: 24px;
            font-weight: 300;
        }

        .faq-answer {
            display: none;
            padding-bottom: 24px;
            color: var(--mid);
            line-height: 1.7;
            font-size: 15px;
        }

        .faq-item.open .faq-answer {
            display: block;
        }

        .faq-item.open .faq-question span:last-child {
            transform: rotate(45deg);
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

        .socials {
            display: flex;
            gap: 10px;
            margin-top: 12px;
        }

        .social-link {
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

        .social-link:hover {
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
        @media (max-width: 992px) {
            .delivery-grid {
                gap: 30px;
            }

            .delivery-card {
                padding: 35px 25px;
            }

            .delivery-card h3 {
                font-size: 24px;
            }

            .info-block {
                padding: 35px;
            }

            .info-block h2 {
                font-size: 32px;
            }
        }

        @media (max-width: 768px) {
            .nav {
                display: none;
            }

            .burger-menu {
                display: flex;
            }

            .delivery-page {
                padding-top: 90px;
                padding-left: 20px;
                padding-right: 20px;
            }

            .delivery-grid {
                grid-template-columns: 1fr;
                gap: 24px;
                margin-bottom: 50px;
            }

            .info-section {
                grid-template-columns: 1fr;
                gap: 24px;
                margin-bottom: 50px;
            }

            .delivery-hero {
                margin-bottom: 40px;
            }

            .delivery-hero h1 {
                font-size: clamp(36px, 5vw, 48px);
            }

            .delivery-card {
                padding: 30px 24px;
            }

            .delivery-card .price {
                font-size: 18px;
            }

            .info-block {
                padding: 30px;
            }

            .info-block h2 {
                font-size: 28px;
            }

            .faq-section h2 {
                font-size: 36px;
                margin-bottom: 30px;
            }

            .faq-question {
                font-size: 16px;
                padding: 18px 0;
            }

            .search-wrap {
                display: none;
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

            .delivery-page {
                padding-left: 16px;
                padding-right: 16px;
            }

            .delivery-card h3 {
                font-size: 22px;
            }

            .delivery-icon {
                font-size: 48px;
            }

            .delivery-card p {
                font-size: 14px;
            }

            .info-block h2 {
                font-size: 26px;
            }

            .info-block h3 {
                font-size: 17px;
            }

            .info-block p,
            .info-block li {
                font-size: 14px;
            }

            .payment-icon {
                padding: 8px 18px;
                font-size: 13px;
            }

            .faq-section h2 {
                font-size: 32px;
            }

            .faq-question {
                font-size: 15px;
            }

            .faq-answer {
                font-size: 14px;
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

            .delivery-page {
                padding-left: 14px;
                padding-right: 14px;
            }

            .delivery-card {
                padding: 25px 20px;
            }

            .info-block {
                padding: 24px;
            }

            .payment-icons {
                gap: 12px;
            }

            .payment-icon {
                padding: 6px 14px;
                font-size: 12px;
            }

            .faq-question {
                font-size: 14px;
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
                <a href="profile.php" class="profile-link"
                    id="profileLink"><?= $userName ? htmlspecialchars($userName) : 'Войти' ?></a>
                <button class="cart-btn" onclick="location.href='cart.php'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.5">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <path d="M16 10a4 4 0 01-8 0" />
                    </svg>
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

    <main class="delivery-page">
        <div class="delivery-hero">
            <h1>Доставка и <em>оплата</em></h1>
            <p>Удобные способы получения заказа и безопасная оплата</p>
        </div>

        <div class="delivery-grid">
            <div class="delivery-card">
                <div class="delivery-icon"><i class="fas fa-truck"></i></div>
                <h3>Курьерская доставка</h3>
                <p>Доставим по указанному адресу в удобное для вас время</p>
                <div class="price">от 500 ₽</div>
                <p style="font-size:13px; margin-top:10px;">Бесплатно при заказе от 10 000 ₽</p>
            </div>
            <div class="delivery-card">
                <div class="delivery-icon"><i class="fas fa-sign-out-alt"></i></div>
                <h3>Самовывоз</h3>
                <p>Заберите заказ из нашего магазина в Орле</p>
                <div class="price">Бесплатно</div>
                <p style="font-size:13px; margin-top:10px;">Ежедневно с 10:00 до 21:00</p>
            </div>
            <div class="delivery-card">
                <div class="delivery-icon"><i class="fas fa-box"></i></div>
                <h3>Транспортная компания</h3>
                <p>Доставка в любой регион России через СДЭК, ПЭК, Деловые Линии</p>
                <div class="price">по тарифам ТК</div>
                <p style="font-size:13px; margin-top:10px;">Отправка в день заказа</p>
            </div>
        </div>

        <div class="info-section">
            <div class="info-block">
                <h2>Способы оплаты</h2>
                <div class="payment-icons">
                    <span class="payment-icon"><i class="fab fa-cc-visa fa-3x"></i></span>
                    <span class="payment-icon"><i class="fab fa-cc-mastercard fa-3x"></i></span>
                    <span class="payment-icon"><i class="fas fa-credit-card fa-3x"></i></span>
                    <span class="payment-icon"><i class="fas fa-mobile-alt fa-3x"></i></span>
                    <span class="payment-icon"><i class="fas fa-bolt fa-3x"></i></span>
                </div>
                <h3>Оплата онлайн</h3>
                <p>Безопасные платежи через банковские карты. Все транзакции защищены шифрованием SSL.</p>
                <h3>Наличные курьеру</h3>
                <p>Оплата при получении заказа. Проверьте товар до оплаты.</p>
                <h3>Безналичный расчёт</h3>
                <p>Для юридических лиц — счёт с НДС. Отправляем документы на email.</p>
            </div>
            <div class="info-block">
                <h2>Условия доставки</h2>
                <ul>
                    <li>Доставка по Орлу и Области — 1-2 дня</li>
                    <li>Подъём на этаж — включён в стоимость</li>
                    <li>Занос в квартиру — бесплатно</li>
                    <li>Сборка мебели — от 500 ₽ (по желанию)</li>
                    <li>Вывоз старой мебели — от 1000 ₽</li>
                    <li>Примерка перед покупкой — по запросу</li>
                </ul>
                <h3>Сроки доставки</h3>
                <p>Орёл — 1-2 рабочих дня<br>
                    Орловская область — 2-3 рабочих дня<br>
                    Регионы России — 5-14 дней (зависит от ТК)</p>
            </div>
        </div>

        <div class="faq-section">
            <h2>Часто задаваемые вопросы</h2>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Можно ли вернуть товар?</span>
                    <span>+</span>
                </div>
                <div class="faq-answer">Да, вы можете вернуть товар в течение 7 дней после получения, если он не был в
                    использовании и сохранены все упаковки и бирки. Исключение — мебель, изготовленная на заказ.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Как отследить заказ?</span>
                    <span>+</span>
                </div>
                <div class="faq-answer">После отправки заказа мы пришлём трек-номер на email и в SMS. Вы можете
                    отслеживать статус на сайте транспортной компании.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Есть ли примерка перед покупкой?</span>
                    <span>+</span>
                </div>
                <div class="faq-answer">Да, мы можем привезти образцы тканей и материалов к вам домой или в офис
                    бесплатно. Также вы можете посетить наш магазин в Орле.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Нужна ли предоплата?</span>
                    <span>+</span>
                </div>
                <div class="faq-answer">Предоплата не требуется, если заказ оформлен с доставкой по Орлу. Для регионов
                    возможна предоплата 30% или полная оплата при получении.</div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-inner">
            <div class="footer-grid">
                <div>
                    <div class="footer-logo">Планета <span>Мебели</span></div>
                    <p style="font-size: 13px; margin-bottom: 12px;">Мебель для вашего дома</p>
                    <div class="socials">
                        <a href="#" class="social-link">vk</a>
                        <a href="#" class="social-link">tg</a>
                    </div>
                </div>
                <div class="fcol">
                    <h4>Каталог</h4>
                    <ul>
                        <li><a href="catalog.php">Диваны</a></li>
                        <li><a href="catalog.php">Кровати</a></li>
                        <li><a href="catalog.php">Столы</a></li>
                        <li><a href="catalog.php">Шкафы</a></li>
                    </ul>
                </div>
                <div class="fcol">
                    <h4>Компания</h4>
                    <ul>
                        <li><a href="about.php">О нас</a></li>
                        <li><a href="delivery.php">Доставка</a></li>
                        <li><a href="contacts.php">Контакты</a></li>
                    </ul>
                </div>
                <div class="fcol">
                    <h4>Подписка</h4>
                    <form class="sub-form" id="subFormFooter">
                        <input type="email" id="subEmailFooter" placeholder="ваш@email.ru">
                        <button type="submit">→</button>
                    </form>
                    <p id="subMsgFooter" style="font-size:12px;margin-top:8px;color:var(--gold);display:none">✓ Вы
                        подписались!</p>
                    <p id="subErrFooter" style="font-size:12px;margin-top:8px;color:#e07070;display:none">Введите email
                    </p>
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
        mobileNavOverlay?.addEventListener('click', function (e) {
            if (e.target === mobileNavOverlay) closeMobileMenu();
        });

        // Функции корзины
        const getCart = () => JSON.parse(localStorage.getItem('pm_cart') || '[]');
        const saveCart = c => localStorage.setItem('pm_cart', JSON.stringify(c));

        function updateCartCount() {
            const n = getCart().reduce((s, i) => s + i.quantity, 0);
            const el = document.getElementById('cartCount');
            if (el) el.textContent = n;
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

        function toggleFaq(el) {
            el.closest('.faq-item').classList.toggle('open');
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
            updateCartCount();
            const user = JSON.parse(localStorage.getItem('pm_user') || 'null');
            const pl = document.getElementById('profileLink');
            if (pl && user) pl.textContent = user.name?.split(' ')[0] || user.name;
        });
    </script>
</body>

</html>
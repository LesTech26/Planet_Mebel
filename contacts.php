<?php
session_start();

require_once __DIR__ . '/includes/db.php';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cartCount = array_sum(array_column($cart, 'quantity'));
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

$success = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    $errors = [];
    
    if (empty($name)) $errors[] = 'Имя обязательно';
    if (empty($email)) $errors[] = 'Email обязателен';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email';
    if (empty($message)) $errors[] = 'Сообщение обязательно';
    
    if (empty($errors)) {
        try {
            $db = db();
            $stmt = $db->prepare("
                INSERT INTO messages (name, email, subject, message, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $email, $subject, $message]);
            $success = true;
            $_POST = [];
        } catch (PDOException $e) {
            $error = true;
        }
    } else {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Контакты — Планета Мебели</title>
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
            --red: #B04040;
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

        /* CONTACTS PAGE */
        .contacts-page {
            padding-top: 100px;
            max-width: 1440px;
            margin: 0 auto;
            padding-left: 24px;
            padding-right: 24px;
            width: 100%;
        }

        .contacts-hero {
            text-align: center;
            margin-bottom: 60px;
        }

        .contacts-hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(42px, 6vw, 64px);
            font-weight: 400;
            margin-bottom: 20px;
        }

        .contacts-hero h1 em {
            font-style: italic;
            color: var(--walnut);
        }

        .contacts-hero p {
            color: var(--mid);
            max-width: 600px;
            margin: 0 auto;
            font-size: 17px;
        }

        /* CONTACTS GRID */
        .contacts-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-bottom: 60px;
        }

        .contact-card {
            background: var(--cream);
            padding: 40px 25px;
            text-align: center;
            border-radius: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .contact-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        }

        .contact-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .contact-card h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 500;
            margin-bottom: 14px;
        }

        .contact-card p {
            margin-bottom: 8px;
            font-size: 15px;
        }

        .contact-card a {
            color: var(--walnut);
            text-decoration: none;
            transition: color 0.2s;
        }

        .contact-card a:hover {
            text-decoration: underline;
        }

        /* MAP SECTION - карта подстраивается под высоту address-info */
        .map-section {
            display: flex;
            gap: 40px;
            margin-bottom: 60px;
            align-items: stretch;
        }

        .map-container {
            flex: 1;
            border-radius: 20px;
            overflow: hidden;
            min-height: 480px;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        .address-info {
            width: 420px;
            background: var(--cream);
            padding: 35px;
            border-radius: 20px;
            flex-shrink: 0;
        }

        .address-info h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 400;
            margin-bottom: 24px;
        }

        .address-item {
            margin-bottom: 24px;
        }

        .address-item strong {
            display: block;
            font-size: 12px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--mid);
            margin-bottom: 8px;
        }

        .address-item p {
            font-size: 16px;
        }

        .work-hours {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--line);
        }

        .work-hours h3 {
            font-size: 22px;
            margin-bottom: 16px;
        }

        .work-hours p {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 15px;
        }

        /* FEEDBACK FORM */
        .feedback-form {
            background: var(--cream);
            padding: 50px;
            margin-bottom: 60px;
            border-radius: 20px;
        }

        .feedback-form h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 38px;
            font-weight: 400;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--mid);
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 14px;
            border: 1px solid var(--line);
            background: var(--warm);
            font-family: 'Jost', sans-serif;
            font-size: 15px;
            border-radius: 12px;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--walnut);
        }

        .btn-submit {
            background: var(--walnut);
            color: #fff;
            border: none;
            padding: 16px 40px;
            font-size: 13px;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            cursor: pointer;
            border-radius: 40px;
            transition: background 0.2s;
            width: 100%;
            font-weight: 500;
        }

        .btn-submit:hover {
            background: var(--walnut-l);
        }

        .success-message {
            color: var(--green);
            margin-bottom: 24px;
            padding: 14px;
            background: rgba(74, 124, 89, 0.1);
            border-radius: 12px;
            text-align: center;
            font-size: 15px;
        }

        .error-message {
            color: var(--red);
            margin-bottom: 24px;
            padding: 14px;
            background: rgba(176, 64, 64, 0.1);
            border-radius: 12px;
            text-align: center;
            font-size: 15px;
        }

        /* SOCIAL LINKS */
        .social-links-section {
            text-align: center;
            margin-bottom: 50px;
        }

        .social-links-section h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            font-weight: 400;
            margin-bottom: 24px;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .social-link {
            width: 52px;
            height: 52px;
            background: var(--cream);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--walnut);
            font-size: 24px;
            transition: all 0.2s;
        }

        .social-link:hover {
            background: var(--walnut);
            color: #fff;
            transform: translateY(-3px);
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
        @media (max-width: 1100px) {
            .contacts-grid {
                gap: 25px;
            }

            .contact-card {
                padding: 30px 20px;
            }

            .map-section {
                gap: 30px;
            }
            
            .address-info {
                width: 380px;
                padding: 30px;
            }
        }

        @media (max-width: 950px) {
            .contacts-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 25px;
            }

            .map-section {
                flex-direction: column;
            }

            .map-container {
                width: 100%;
                min-height: 400px;
            }

            .address-info {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .nav {
                display: none;
            }

            .burger-menu {
                display: flex;
            }

            .contacts-page {
                padding-top: 90px;
                padding-left: 20px;
                padding-right: 20px;
            }

            .contacts-hero {
                margin-bottom: 40px;
            }

            .contacts-hero h1 {
                font-size: clamp(36px, 5vw, 48px);
            }

            .contact-card {
                padding: 25px 20px;
            }

            .contact-icon {
                font-size: 40px;
            }

            .contact-card h3 {
                font-size: 20px;
            }

            .address-info {
                padding: 30px;
            }

            .address-info h3 {
                font-size: 24px;
            }

            .feedback-form {
                padding: 35px;
            }

            .feedback-form h2 {
                font-size: 32px;
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

            .contacts-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .contacts-page {
                padding-left: 16px;
                padding-right: 16px;
            }

            .contact-card {
                padding: 24px 20px;
            }

            .contact-icon {
                font-size: 36px;
            }

            .contact-card h3 {
                font-size: 18px;
            }

            .contact-card p {
                font-size: 14px;
            }

            .address-info {
                padding: 24px;
            }

            .address-info h3 {
                font-size: 22px;
            }

            .address-item p {
                font-size: 14px;
            }

            .work-hours p {
                font-size: 13px;
            }

            .feedback-form {
                padding: 25px;
            }

            .feedback-form h2 {
                font-size: 28px;
            }

            .btn-submit {
                padding: 14px 30px;
                font-size: 12px;
            }

            .social-link {
                width: 44px;
                height: 44px;
                font-size: 20px;
            }

            .social-links-section h3 {
                font-size: 24px;
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

            .contacts-page {
                padding-left: 14px;
                padding-right: 14px;
            }

            .contacts-hero h1 {
                font-size: clamp(30px, 6vw, 38px);
            }

            .contacts-hero p {
                font-size: 14px;
            }

            .contact-card {
                padding: 20px 16px;
            }

            .contact-icon {
                font-size: 32px;
            }

            .map-container {
                min-height: 280px;
            }

            .address-info {
                padding: 20px;
            }

            .address-info h3 {
                font-size: 20px;
            }

            .feedback-form {
                padding: 20px;
            }

            .feedback-form h2 {
                font-size: 26px;
            }

            .form-group input,
            .form-group textarea,
            .form-group select {
                padding: 12px;
                font-size: 14px;
            }

            .social-link {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }
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

    <main class="contacts-page">
        <div class="contacts-hero">
            <h1>Наши <em>контакты</em></h1>
            <p>Мы всегда на связи и готовы ответить на ваши вопросы</p>
        </div>

        <div class="contacts-grid">
            <div class="contact-card">
                <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                <h3>Телефон</h3>
                <p><a href="tel:+78005553535">+7 (800) 555-35-35</a></p>
                <p><a href="tel:+74862234567">+7 (4862) 23-45-67</a></p>
                <p style="font-size: 13px; margin-top: 8px;">Ежедневно 9:00–21:00</p>
            </div>
            <div class="contact-card">
                <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                <h3>Email</h3>
                <p><a href="mailto:info@planeta-mebeli.ru">info@planeta-mebeli.ru</a></p>
                <p><a href="mailto:orel@planeta-mebeli.ru">orel@planeta-mebeli.ru</a></p>
                <p style="font-size: 13px; margin-top: 8px;">Ответ в течение 2 часов</p>
            </div>
            <div class="contact-card">
                <div class="contact-icon"><i class="fab fa-telegram"></i></div>
                <h3>Мессенджеры</h3>
                <p><a href="#">WhatsApp: +7 (999) 123-45-67</a></p>
                <p><a href="#">Telegram: @planeta_mebeli</a></p>
                <p style="font-size: 13px; margin-top: 8px;">Онлайн-консультация</p>
            </div>
            <div class="contact-card">
                <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                <h3>Адрес</h3>
                <p>г. Орёл, улица Лескова, 19А</p>
                <p>ТЦ «Протон», 1 этаж</p>
                <p style="font-size: 13px; margin-top: 8px;">Магазин на карте</p>
            </div>
        </div>

        <div class="map-section">
            <div class="map-container">
                <iframe
                    src="https://yandex.ru/map-widget/v1/?um=constructor%3A88011f845e521729d0ff087a69c73b52933ce56c7f3d2d2af46fdd7d123df4a0&amp;source=constructor"
                    allowfullscreen></iframe>
            </div>
            <div class="address-info">
                <h3>Магазин в Орле</h3>
                <div class="address-item">
                    <strong>Адрес</strong>
                    <p>г. Орёл, улица Лескова, 19А<br>ТЦ «Протон», 1 этаж</p>
                </div>
                <div class="address-item">
                    <strong>Как добраться</strong>
                    <p>Остановка «ТЦ Протон». Вход со стороны парковки, 1 этаж</p>
                </div>
                <div class="work-hours">
                    <h3>Режим работы</h3>
                    <p><span>Понедельник — Пятница:</span><span>10:00 — 20:00</span></p>
                    <p><span>Суббота:</span><span>10:00 — 19:00</span></p>
                    <p><span>Воскресенье:</span><span>10:00 — 18:00</span></p>
                </div>
                <div class="work-hours" style="margin-top: 16px;">
                    <h3>Склад и пункт самовывоза</h3>
                    <p><span>г. Орёл, улица Лескова, 19А</span></p>
                    <p><span>Пн-Пт 9:00 — 18:00</span></p>
                </div>
            </div>
        </div>

        <div class="feedback-form">
            <h2>Написать нам</h2>
            <?php if ($success): ?>
                <div class="success-message"><i class="fas fa-check-circle"></i> Сообщение отправлено! Мы ответим вам в ближайшее время.</div>
            <?php endif; ?>
            <?php if ($error && !$success): ?>
                <div class="error-message"><i class="fas fa-exclamation-triangle"></i> Ошибка! Попробуйте позже.</div>
            <?php endif; ?>
            <form method="POST" id="contactForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Ваше имя *</label>
                        <input type="text" name="name" id="contactName" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" id="contactEmail" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Тема обращения</label>
                    <select name="subject" id="contactSubject">
                        <option value="">Выберите тему...</option>
                        <option value="order" <?= ($_POST['subject'] ?? '') == 'order' ? 'selected' : '' ?>>Вопрос о заказе</option>
                        <option value="delivery" <?= ($_POST['subject'] ?? '') == 'delivery' ? 'selected' : '' ?>>Доставка</option>
                        <option value="product" <?= ($_POST['subject'] ?? '') == 'product' ? 'selected' : '' ?>>Товар</option>
                        <option value="cooperation" <?= ($_POST['subject'] ?? '') == 'cooperation' ? 'selected' : '' ?>>Сотрудничество</option>
                        <option value="other" <?= ($_POST['subject'] ?? '') == 'other' ? 'selected' : '' ?>>Другое</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Сообщение *</label>
                    <textarea name="message" id="contactMessage" rows="4" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn-submit">Отправить сообщение</button>
            </form>
        </div>

        <div class="social-links-section">
            <h3>Мы в соцсетях</h3>
            <div class="social-links">
                <a href="#" class="social-link"><i class="fab fa-vk"></i></a>
                <a href="#" class="social-link"><i class="fab fa-telegram"></i></a>
            </div>
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
                    <form class="sub-form" id="subForm">
                        <input type="email" id="subEmail" placeholder="ваш@email.ru">
                        <button type="submit">→</button>
                    </form>
                    <p id="subMsg" style="font-size:12px;margin-top:8px;color:var(--gold);display:none">✓ Вы
                        подписались!</p>
                    <p id="subErr" style="font-size:12px;margin-top:8px;color:#e07070;display:none">Введите email</p>
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

        // Корзина
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

        // Валидация формы
        document.getElementById('contactForm')?.addEventListener('submit', function (e) {
            const name = document.getElementById('contactName').value.trim();
            const email = document.getElementById('contactEmail').value.trim();
            const message = document.getElementById('contactMessage').value.trim();

            if (!name || !email || !message) {
                e.preventDefault();
                showToast('Заполните все обязательные поля');
                return false;
            }

            if (!email.includes('@') || !email.includes('.')) {
                e.preventDefault();
                showToast('Введите корректный email');
                return false;
            }
        });

        // Подписка
        document.getElementById('subForm')?.addEventListener('submit', async e => {
            e.preventDefault();
            const email = document.getElementById('subEmail').value.trim();
            const msg = document.getElementById('subMsg');
            const err = document.getElementById('subErr');
            if (msg) msg.style.display = 'none';
            if (err) err.style.display = 'none';
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                if (err) err.style.display = 'block';
                return;
            }
            if (msg) msg.style.display = 'block';
            document.getElementById('subEmail').value = '';
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
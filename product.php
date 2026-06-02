<?php
session_start();

// Подключаем функции
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

// Обработка выхода из аккаунта
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$productId = (int)($_GET['id'] ?? 0);
$product = getProduct($productId);

if (!$product) {
    header('Location: catalog.php');
    exit;
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cartCount = array_sum(array_column($cart, 'quantity'));
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

// Получаем дополнительные изображения
$additionalImages = [];
if (!empty($product['images'])) {
    $additionalImages = json_decode($product['images'], true);
    if (!is_array($additionalImages)) {
        $additionalImages = [];
    }
}

$galleryImages = [$product['image']];
foreach ($additionalImages as $img) {
    if (!empty($img) && $img !== $product['image']) {
        $galleryImages[] = $img;
    }
}

// Характеристики
$specs = [];
if (!empty($product['specs'])) {
    $lines = explode("\n", $product['specs']);
    foreach ($lines as $line) {
        if (strpos($line, ':') !== false) {
            list($key, $value) = explode(':', $line, 2);
            $specs[trim($key)] = trim($value);
        }
    }
}

if (empty($specs)) {
    $specs = [
        'Ширина' => '220 см',
        'Глубина' => '90 см',
        'Высота' => '85 см',
        'Тип механизма' => 'Еврокнижка',
        'Наполнитель' => 'Пружинный блок + ППУ'
    ];
}

$colors = [
    ['name' => 'Серый', 'hex' => '#9BA7A0'],
    ['name' => 'Бежевый', 'hex' => '#C9B99A'],
    ['name' => 'Синий', 'hex' => '#5A7A99']
];

$materials = ['Велюр', 'Рогожка', 'Экокожа'];

// Сопутствующие товары
$crossSell = getProducts(['limit' => 4]);
$crossSell = array_filter($crossSell, function($p) use ($productId) {
    return $p['id'] != $productId;
});
$crossSell = array_slice($crossSell, 0, 4);

$reviews = [
    ['id' => 1, 'name' => 'Алина К.', 'rating' => 5, 'date' => '15 мая 2025', 'text' => 'Отличный товар! Качество превосходное. Рекомендую!'],
    ['id' => 2, 'name' => 'Михаил С.', 'rating' => 4, 'date' => '2 апреля 2025', 'text' => 'Хороший товар за свои деньги. Вполне доволен покупкой.'],
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
<title><?= htmlspecialchars($product['name']) ?> — Планета Мебели</title>
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
    --red: #B04040;
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
.nav a { text-decoration: none; color: var(--charcoal); font-size: 15px; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 500; transition: color 0.2s; white-space: nowrap; margin-right: 16px;}
.nav a:hover { color: var(--walnut); }

/* БУРГЕР-МЕНЮ */
.burger-menu { display: none; flex-direction: column; justify-content: space-between; width: 30px; height: 21px; cursor: pointer; z-index: 210; }
.burger-menu span { display: block; width: 100%; height: 2px; background: var(--charcoal); transition: all 0.3s ease; border-radius: 2px; }
.burger-menu.active span:nth-child(1) { transform: translateY(9px) rotate(45deg); }
.burger-menu.active span:nth-child(2) { opacity: 0; }
.burger-menu.active span:nth-child(3) { transform: translateY(-9px) rotate(-45deg); }

/* МОБИЛЬНОЕ МЕНЮ */
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

/* PRODUCT PAGE */
.product-page { padding-top: 100px; max-width: 1440px; margin: 0 auto; padding-left: 24px; padding-right: 24px; width: 100%; }
.product-layout { display: grid; grid-template-columns: 1fr 500px; gap: 60px; margin-bottom: 70px; }
.product-gallery { position: sticky; top: 100px; }
.gallery-main { width: 100%; aspect-ratio: 1; overflow: hidden; background: var(--cream); margin-bottom: 20px; border-radius: 20px; cursor: pointer; }
.gallery-main img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
.gallery-main:hover img { transform: scale(1.05); }
.gallery-thumbs { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 12px; }
.thumb { width: 90px; height: 90px; cursor: pointer; border: 2px solid transparent; border-radius: 12px; transition: all 0.2s; overflow: hidden; }
.thumb img { width: 100%; height: 100%; object-fit: cover; }
.thumb.active { border-color: var(--walnut); transform: scale(1.02); }
.product-category { font-size: 13px; letter-spacing: 0.15em; text-transform: uppercase; color: var(--walnut); margin-bottom: 12px; font-weight: 500; }
.product-name { font-family: 'Cormorant Garamond', serif; font-size: 42px; font-weight: 400; margin-bottom: 12px; }
.product-article { font-size: 14px; color: var(--mid); margin-bottom: 24px; }
.product-price { display: flex; align-items: baseline; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
.price-current { font-family: 'Cormorant Garamond', serif; font-size: 38px; font-weight: 500; color: var(--charcoal); }
.price-old { font-size: 20px; color: var(--mid); text-decoration: line-through; }
.price-save { font-size: 13px; color: var(--green); background: rgba(74,124,89,0.1); padding: 4px 12px; border-radius: 30px; }
.product-stock { display: flex; align-items: center; gap: 10px; font-size: 14px; margin-bottom: 28px; }
.stock-dot { width: 10px; height: 10px; border-radius: 50%; }
.stock-dot.in { background: var(--green); }
.product-options { margin-bottom: 24px; }
.options-label { font-size: 12px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--mid); margin-bottom: 12px; font-weight: 500; }
.color-list { display: flex; gap: 14px; flex-wrap: wrap; }
.color-option { width: 38px; height: 38px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; transition: all 0.2s; }
.color-option.active { border-color: var(--walnut); transform: scale(1.1); }
.material-list { display: flex; gap: 12px; flex-wrap: wrap; }
.material-option { padding: 10px 20px; border: 1px solid var(--line); background: transparent; cursor: pointer; font-family: 'Jost', sans-serif; font-size: 14px; border-radius: 40px; transition: all 0.2s; font-weight: 500; }
.material-option.active { background: var(--walnut); border-color: var(--walnut); color: #fff; }
.product-actions { display: flex; gap: 16px; align-items: center; margin-bottom: 40px; flex-wrap: wrap; }
.quantity-selector { display: flex; align-items: center; border: 1px solid var(--line); border-radius: 40px; overflow: hidden; }
.qty-btn { width: 46px; height: 52px; background: none; border: none; font-size: 22px; cursor: pointer; transition: background 0.2s; }
.qty-btn:hover { background: var(--cream); }
#qtyValue { width: 60px; text-align: center; border: none; background: transparent; font-size: 18px; font-weight: 500; }
.btn-cart { flex: 1; height: 52px; background: var(--walnut); color: #fff; border: none; font-size: 13px; letter-spacing: 0.15em; text-transform: uppercase; cursor: pointer; border-radius: 40px; transition: background 0.2s; font-weight: 500; }
.btn-cart:hover { background: var(--walnut-l); }
.btn-fav { width: 52px; height: 52px; border: 1px solid var(--line); background: none; font-size: 22px; cursor: pointer; border-radius: 50%; transition: all 0.2s; }
.btn-fav.active { border-color: var(--walnut); color: var(--walnut); background: rgba(124,92,62,0.05); }
.specs-table { width: 100%; border-collapse: collapse; }
.specs-table tr { border-bottom: 1px solid var(--line); }
.specs-table td { padding: 14px 0; font-size: 15px; }
.specs-table td:first-child { color: var(--mid); width: 40%; font-weight: 500; }

/* REVIEWS */
.product-reviews { margin-top: 70px; padding-top: 50px; border-top: 1px solid var(--line); }
.product-reviews h2 { font-family: 'Cormorant Garamond', serif; font-size: 34px; font-weight: 400; margin-bottom: 30px; }
.review-item { padding: 24px 0; border-bottom: 1px solid var(--line); }
.review-header { display: flex; align-items: center; gap: 16px; margin-bottom: 12px; flex-wrap: wrap; }
.review-avatar { width: 48px; height: 48px; border-radius: 50%; background: var(--walnut); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 500; font-size: 18px; margin-right: 16px;}
.review-name { font-weight: 500; font-size: 16px; }
.review-rating { color: var(--gold); font-size: 14px; }
.review-date { font-size: 12px; color: var(--mid); margin-left: auto; }
.review-text { font-size: 15px; line-height: 1.6; color: var(--mid); }
.btn-show-form { background: none; border: 1px solid var(--walnut); padding: 12px 28px; cursor: pointer; border-radius: 40px; margin-top: 24px; font-size: 13px; letter-spacing: 0.1em; text-transform: uppercase; transition: all 0.2s; font-weight: 500; }
.btn-show-form:hover { background: var(--walnut); color: #fff; }
.review-form { margin-top: 30px; padding: 30px; background: var(--cream); border-radius: 20px; }
.review-form input, .review-form select, .review-form textarea { width: 100%; margin-bottom: 16px; padding: 14px; border: 1px solid var(--line); background: var(--warm); font-family: 'Jost', sans-serif; border-radius: 12px; font-size: 15px; }
.btn-submit { background: var(--walnut); color: #fff; border: none; padding: 14px 32px; cursor: pointer; font-size: 13px; letter-spacing: 0.12em; text-transform: uppercase; border-radius: 40px; font-weight: 500; transition: background 0.2s; }
.btn-submit:hover { background: var(--walnut-l); }

/* CROSS SELL */
.cross-sell { margin-top: 70px; overflow: hidden; width: 100%; }
.cross-sell h2 { font-family: 'Cormorant Garamond', serif; font-size: 34px; font-weight: 400; margin-bottom: 32px; }
.cross-swiper { overflow: visible !important; width: 100%; }
.cross-swiper .swiper-wrapper { display: flex; }
.cross-swiper .swiper-slide { width: 280px; flex-shrink: 0; }
.cross-card { cursor: pointer; transition: transform 0.3s; }
.cross-card:hover { transform: translateY(-6px); }
.cross-card-img { aspect-ratio: 3/4; overflow: hidden; background: var(--cream); margin-bottom: 16px; border-radius: 16px; }
.cross-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
.cross-card:hover .cross-card-img img { transform: scale(1.05); }
.cross-card-cat { font-size: 11px; color: var(--mid); text-transform: uppercase; letter-spacing: 0.1em; }
.cross-card-name { font-family: 'Cormorant Garamond', serif; font-size: 18px; font-weight: 500; margin: 8px 0 6px; }
.cross-card-price { color: var(--walnut); font-size: 16px; font-weight: 500; }
.swiper-nav { display: flex; gap: 14px; margin-top: 28px; justify-content: center; }
.swiper-btn { width: 46px; height: 46px; border: 1px solid var(--line); background: transparent; cursor: pointer; font-size: 18px; border-radius: 50%; transition: all 0.2s; }
.swiper-btn:hover { background: var(--walnut); color: #fff; }

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
@media (max-width: 1200px) {
    .product-layout { grid-template-columns: 1fr 450px; gap: 40px; }
    .cross-swiper .swiper-slide { width: 260px; }
}

@media (max-width: 992px) {
    .product-layout { grid-template-columns: 1fr; gap: 40px; }
    .product-gallery { position: static; max-width: 500px; margin: 0 auto; }
    .product-info { max-width: 600px; margin: 0 auto; }
}

@media (max-width: 768px) {
    .nav { display: none; }
    .burger-menu { display: flex; }
    .header { height: 65px; }
    .hinner { padding: 0 16px; gap: 12px; }
    .logo { font-size: 20px; }
    .search-wrap { display: none; }
    .profile-link span { display: none; }
    .profile-link i { display: inline-block; }
    .product-page { padding-top: 80px; padding-left: 16px; padding-right: 16px; }
    .product-name { font-size: 28px; }
    .price-current { font-size: 28px; }
    .product-reviews h2, .cross-sell h2 { font-size: 28px; }
    .gallery-main { border-radius: 16px; }
    .thumb { width: 65px; height: 65px; }
    .qty-btn { width: 40px; height: 46px; font-size: 18px; }
    #qtyValue { width: 50px; font-size: 16px; }
    .btn-cart { height: 46px; font-size: 12px; }
    .btn-fav { width: 46px; height: 46px; font-size: 20px; }
    .cross-swiper .swiper-slide { width: 220px; }
    .cross-card-name { font-size: 16px; }
    .cross-card-price { font-size: 14px; }
    .product-layout { margin-bottom: 40px; }
    .product-reviews { margin-top: 40px; padding-top: 30px; }
    .cross-sell { margin-top: 40px; }
    .review-form { padding: 20px; }
    .footer-inner { padding: 35px 20px 20px; }
}

@media (max-width: 550px) {
    .product-page { padding-left: 12px; padding-right: 12px; }
    .product-name { font-size: 24px; }
    .price-current { font-size: 24px; }
    .price-old { font-size: 16px; }
    .product-category { font-size: 11px; }
    .thumb { width: 55px; height: 55px; }
    .qty-btn { width: 36px; height: 42px; }
    #qtyValue { width: 45px; }
    .cross-swiper .swiper-slide { width: 190px; }
    .cross-card-name { font-size: 14px; }
    .cross-card-cat { font-size: 9px; }
    .cross-card-price { font-size: 13px; }
    .specs-table td { font-size: 13px; padding: 10px 0; }
    .review-avatar { width: 36px; height: 36px; font-size: 14px; margin-right:12px;}
    .review-name { font-size: 14px; }
    .review-text { font-size: 13px; }
    .swiper-btn { width: 38px; height: 38px; font-size: 14px; }
}

@media (max-width: 480px) {
    .hinner { padding: 0 12px; }
    .cart-count { width: 20px; height: 20px; font-size: 11px; }
    .cart-btn svg { width: 14px; height: 14px; }
    .logo { font-size: 18px; }
    .product-page { padding-top: 75px; }
    .material-list { gap: 8px; }
    .material-option { padding: 8px 16px; font-size: 13px; }
    .color-option { width: 32px; height: 32px; }
    .product-actions { flex-direction: column; gap: 12px; }
    .quantity-selector { width: 100%; justify-content: center; }
    .btn-cart { width: 100%; }
    .btn-fav { position: absolute; right: 20px; top: 20px; }
    .product-info { position: relative; }
    .cross-swiper .swiper-slide { width: 170px; }
    .cross-card-img { border-radius: 12px; }
    .btn-show-form { width: 100%; padding: 10px 20px; font-size: 12px; }
    .footer-grid { grid-template-columns: 1fr; gap: 20px; }
    .footer-bottom { flex-direction: column; text-align: center; gap: 8px; }
}

@media (max-width: 380px) {
    .cross-swiper .swiper-slide { width: 155px; }
    .cross-card-name { font-size: 13px; }
    .thumb { width: 48px; height: 48px; }
    .product-name { font-size: 22px; }
    .price-current { font-size: 22px; }
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
      <?php endif; ?>
      <li><a href="cart.php">Корзина</a></li>
    </ul>
  </div>
</div>

<main class="product-page">
    <div class="product-layout">
        <div class="product-gallery">
            <div class="gallery-main">
                <img id="mainImg" src="<?= getImageUrl($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" onerror="this.src='/uploads/no-image.webp'">
            </div>
            <div class="gallery-thumbs" id="galleryThumbs">
                <?php foreach ($galleryImages as $index => $img): ?>
                <div class="thumb <?= $index === 0 ? 'active' : '' ?>" onclick="setMainImage('<?= getImageUrl($img) ?>', this)">
                    <img src="<?= getImageUrl($img) ?>" alt="Фото <?= $index+1 ?>" onerror="this.src='/uploads/no-image.webp'">
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="product-info">
            <p class="product-category"><?= htmlspecialchars($product['category'] ?? 'Мебель') ?></p>
            <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="product-article">Артикул: <?= htmlspecialchars($product['slug'] ?? $product['id']) ?></p>
            
            <div class="product-price">
                <span class="price-current"><?= formatPrice($product['price']) ?></span>
                <?php if ($product['old_price']): ?>
                    <span class="price-old"><?= formatPrice($product['old_price']) ?></span>
                    <span class="price-save">−<?= round((1 - $product['price'] / $product['old_price']) * 100) ?>%</span>
                <?php endif; ?>
            </div>
            
            <div class="product-stock">
                <span class="stock-dot in"></span>
                <span><?= htmlspecialchars($product['stock_text'] ?? 'В наличии') ?></span>
            </div>

            <div class="product-options">
                <div class="options-label">Цвет:</div>
                <div class="color-list" id="colorList"></div>
            </div>

            <div class="product-options">
                <div class="options-label">Материал:</div>
                <div class="material-list" id="materialList"></div>
            </div>

            <div class="product-actions">
                <div class="quantity-selector">
                    <button class="qty-btn" id="qtyMinus">−</button>
                    <input type="text" id="qtyValue" value="1" readonly>
                    <button class="qty-btn" id="qtyPlus">+</button>
                </div>
                <button class="btn-cart" onclick="addToCart()">В корзину</button>
                <button class="btn-fav" id="favBtn" onclick="toggleFav()">♡</button>
            </div>

            <div class="product-specs">
                <h3 style="font-size: 18px; margin-bottom: 16px;">Характеристики</h3>
                <table class="specs-table">
                    <?php foreach ($specs as $key => $value): ?>
                    <tr><td><?= htmlspecialchars($key) ?></td><td><?= htmlspecialchars($value) ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="product-reviews">
        <h2>Отзывы покупателей</h2>
        <div class="reviews-list" id="reviewsList"></div>
        <button class="btn-show-form" id="showReviewFormBtn">Написать отзыв</button>
        <div class="review-form" id="reviewForm" style="display: none;">
            <input type="text" id="reviewName" placeholder="Ваше имя">
            <select id="reviewRating">
                <option value="5">5 ★</option><option value="4">4 ★</option><option value="3">3 ★</option><option value="2">2 ★</option><option value="1">1 ★</option>
            </select>
            <textarea id="reviewText" rows="3" placeholder="Ваш отзыв"></textarea>
            <button class="btn-submit" id="submitReviewBtn">Отправить</button>
        </div>
    </div>

    <div class="cross-sell">
        <h2>С этим товаром покупают</h2>
        <div class="swiper cross-swiper" id="crossSwiper">
            <div class="swiper-wrapper" id="crossWrapper"></div>
        </div>
        <div class="swiper-nav">
            <button class="swiper-btn" id="crossPrev"><i class="fas fa-chevron-left"></i></button>
            <button class="swiper-btn" id="crossNext"><i class="fas fa-chevron-right"></i></button>
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
      <span>© 2025 Планета Мебели</span>
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

burgerMenu?.addEventListener('click', openMobileMenu);
mobileNavClose?.addEventListener('click', closeMobileMenu);
mobileNavOverlay?.addEventListener('click', function(e) {
    if (e.target === mobileNavOverlay) closeMobileMenu();
});

const colors = <?= json_encode($colors) ?>;
const materials = <?= json_encode($materials) ?>;
const crossProducts = <?= json_encode(array_values($crossSell)) ?>;
const reviews = <?= json_encode($reviews) ?>;

let selectedColor = colors[0]?.name || '';
let selectedMaterial = materials[0] || '';
let qty = 1;

const getCart = () => JSON.parse(localStorage.getItem('pm_cart')||'[]');
const saveCart = c => localStorage.setItem('pm_cart',JSON.stringify(c));
const getFavs = () => JSON.parse(localStorage.getItem('pm_favs')||'[]');
const saveFavs = f => localStorage.setItem('pm_favs',JSON.stringify(f));
const isFav = id => getFavs().some(f=>f.id===id);

function updateCartCount(){
  const n = getCart().reduce((s,i)=>s+i.quantity,0);
  const el = document.getElementById('cartCount');
  if(el) el.textContent = n;
}

function addToCart(){
  const product = { 
      productId: <?= $product['id'] ?>, 
      name: '<?= htmlspecialchars($product['name']) ?>', 
      price: <?= $product['price'] ?>, 
      image: '<?= getImageUrl($product['image']) ?>' 
  };
  const cart = getCart();
  const key = <?= $product['id'] ?> + '__' + selectedColor + '__' + selectedMaterial;
  const ex = cart.find(i=>i._key===key);
  if(ex) ex.quantity += qty;
  else cart.push({
      productId: product.productId, 
      name: product.name, 
      price: product.price, 
      quantity: qty, 
      _key: key, 
      image: product.image, 
      options: {color: selectedColor, material: selectedMaterial}
  });
  saveCart(cart); 
  updateCartCount();
  showToast('«' + product.name + '» добавлен в корзину');
}

function toggleFav(){
  const product = { 
      id: <?= $product['id'] ?>, 
      name: '<?= htmlspecialchars($product['name']) ?>', 
      price: <?= $product['price'] ?>, 
      image: '<?= getImageUrl($product['image']) ?>' 
  };
  const favs = getFavs();
  const btn = document.getElementById('favBtn');
  const idx = favs.findIndex(f=>f.id===product.id);
  if(idx>-1) {
      favs.splice(idx,1);
      btn.classList.remove('active');
      showToast('«' + product.name + '» удалён из избранного');
  } else {
      favs.push(product);
      btn.classList.add('active');
      showToast('«' + product.name + '» добавлен в избранное');
  }
  saveFavs(favs);
}

function showToast(msg){
  const t=document.getElementById('toast'); 
  const m=document.getElementById('toastMsg');
  if(m) m.textContent=msg;
  if(t){ t.classList.add('show'); clearTimeout(t._tm); t._tm=setTimeout(()=>t.classList.remove('show'),3000); }
}

function setMainImage(url, el){
  document.getElementById('mainImg').src = url;
  document.querySelectorAll('.thumb').forEach(t=>t.classList.remove('active'));
  el.classList.add('active');
}

function initOptions(){
  const colorList = document.getElementById('colorList');
  if(colorList && colors.length){
    colorList.innerHTML = colors.map((c,i) => `<div class="color-option ${i===0?'active':''}" style="background:${c.hex}" title="${c.name}" onclick="selectColor('${c.name}', this)"></div>`).join('');
  }
  const materialList = document.getElementById('materialList');
  if(materialList && materials.length){
    materialList.innerHTML = materials.map((m,i) => `<button class="material-option ${i===0?'active':''}" onclick="selectMaterial('${m}', this)">${m}</button>`).join('');
  }
}

function selectColor(color, el){
  selectedColor = color;
  document.querySelectorAll('.color-option').forEach(opt=>opt.classList.remove('active'));
  el.classList.add('active');
}

function selectMaterial(material, el){
  selectedMaterial = material;
  document.querySelectorAll('.material-option').forEach(opt=>opt.classList.remove('active'));
  el.classList.add('active');
}

function renderReviews(){
  const container = document.getElementById('reviewsList');
  if(!container) return;
  if(!reviews.length){ container.innerHTML = '<p>Пока нет отзывов. Будьте первым!</p>'; return; }
  container.innerHTML = reviews.map(r => `<div class="review-item"><div class="review-header"><div class="review-avatar">${r.name.charAt(0)}</div><div class="review-name">${r.name}</div><div class="review-rating">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</div><div class="review-date">${r.date}</div></div><div class="review-text">${r.text}</div></div>`).join('');
}

function getImageUrl(image){
  if(!image || image==='') return '/uploads/no-image.webp';
  if(image.indexOf('http://')===0 || image.indexOf('https://')===0) return image;
  if(image.indexOf('/uploads/')===0) return image;
  return '/uploads/products/'+image;
}

function initCrossSell(){
  const container = document.getElementById('crossWrapper');
  if(!container) return;
  if(!crossProducts.length){ container.innerHTML = '<div class="swiper-slide" style="width:100%; text-align:center;">Нет товаров</div>'; return; }
  container.innerHTML = crossProducts.map(p => `<div class="swiper-slide"><div class="cross-card" onclick="location.href='product.php?id=${p.id}'"><div class="cross-card-img"><img src="${getImageUrl(p.image)}" alt="${p.name}" onerror="this.src='/uploads/no-image.webp'"></div><p class="cross-card-cat">${p.category || 'Мебель'}</p><div class="cross-card-name">${p.name}</div><div class="cross-card-price">${Number(p.price).toLocaleString('ru-RU')} ₽</div></div></div>`).join('');
  new Swiper('#crossSwiper', { 
      slidesPerView: 'auto', 
      spaceBetween: 20, 
      navigation: { prevEl: '#crossPrev', nextEl: '#crossNext' },
      breakpoints: {
          320: { spaceBetween: 12 },
          480: { spaceBetween: 16 },
          768: { spaceBetween: 20 }
      }
  });
}

document.getElementById('qtyMinus')?.addEventListener('click', () => { if(qty>1){ qty--; document.getElementById('qtyValue').value = qty; } });
document.getElementById('qtyPlus')?.addEventListener('click', () => { if(qty<99){ qty++; document.getElementById('qtyValue').value = qty; } });

document.getElementById('showReviewFormBtn')?.addEventListener('click', () => { const f = document.getElementById('reviewForm'); f.style.display = f.style.display === 'none' ? 'block' : 'none'; });
document.getElementById('submitReviewBtn')?.addEventListener('click', () => { const n = document.getElementById('reviewName')?.value; const t = document.getElementById('reviewText')?.value; if(n && t){ showToast('Спасибо за отзыв! Он будет опубликован после модерации.'); document.getElementById('reviewForm').style.display = 'none'; } else { showToast('Заполните имя и текст отзыва'); } });

document.getElementById('subFormFooter')?.addEventListener('submit', async e=>{ e.preventDefault(); const email=document.getElementById('subEmailFooter').value.trim(); const msg=document.getElementById('subMsgFooter'); const err=document.getElementById('subErrFooter'); if(msg) msg.style.display='none'; if(err) err.style.display='none'; if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){ if(err) err.style.display='block'; return; } if(msg) msg.style.display='block'; document.getElementById('subEmailFooter').value=''; showToast('Вы успешно подписались на новости!'); });

document.addEventListener('DOMContentLoaded', () => {
  initOptions();
  renderReviews();
  initCrossSell();
  updateCartCount();
  const favBtn = document.getElementById('favBtn');
  if(favBtn && isFav(<?= $product['id'] ?>)) favBtn.classList.add('active');
  
  const user = JSON.parse(localStorage.getItem('pm_user') || 'null');
  const profileLink = document.getElementById('profileLink');
  if(profileLink && user) profileLink.innerHTML = user.name?.split(' ')[0] + ' <i class="fas fa-chevron-down"></i>';
});

window.setMainImage = setMainImage;
window.selectColor = selectColor;
window.selectMaterial = selectMaterial;
window.addToCart = addToCart;
window.toggleFav = toggleFav;
</script>
</body>
</html>
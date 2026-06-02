<?php
session_start();

require_once __DIR__ . '/includes/db.php';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cartCount = array_sum(array_column($cart, 'quantity'));
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

$success = false;
$error = false;

// Получаем соединение с БД через функцию db()
$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $request_type = $_POST['request_type'] ?? '';
    $message = trim($_POST['message'] ?? '');
    
    $errors = [];
    
    if (empty($name)) $errors[] = 'Имя обязательно';
    if (empty($email)) $errors[] = 'Email обязателен';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email';
    
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO designer_applications (name, company, email, phone, request_type, message, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $company, $email, $phone, $request_type, $message]);
            $success = true;
            
            // Очищаем форму
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
<title>Дизайнерам — Планета Мебели</title>
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
.nav a { text-decoration: none; color: var(--charcoal); font-size: 15px; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 500; transition: color 0.2s; white-space: nowrap; margin-right: 16px; }
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

/* DESIGNERS PAGE */
.designers-page { padding-top: 90px; max-width: 1440px; margin: 0 auto; padding-left: 24px; padding-right: 24px; width: 100%; }
.designers-hero { text-align: center; margin-bottom: 60px; }
.designers-hero h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(38px, 5vw, 56px); font-weight: 400; margin-bottom: 20px; }
.designers-hero h1 em { font-style: italic; color: var(--walnut); }
.designers-hero p { color: var(--mid); max-width: 650px; margin: 0 auto; font-size: 16px; line-height: 1.6; }

/* BENEFITS GRID */
.benefits-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; margin-bottom: 60px; }
.benefit-card { text-align: center; padding: 35px 25px; background: var(--cream); border-radius: 16px; transition: transform 0.3s; }
.benefit-card:hover { transform: translateY(-6px); }
.benefit-icon { font-size: 48px; margin-bottom: 20px; }
.benefit-title { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 500; margin-bottom: 12px; }
.benefit-desc { font-size: 14px; color: var(--mid); line-height: 1.6; }

/* PARTNERSHIP FORM */
.partnership-form { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; background: var(--cream); padding: 56px; margin-bottom: 60px; border-radius: 20px; }
.form-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(32px, 4vw, 38px); font-weight: 400; margin-bottom: 24px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-size: 12px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--mid); margin-bottom: 8px; font-weight: 500; }
.form-group input, .form-group textarea, .form-group select { width: 100%; padding: 14px; border: 1px solid var(--line); background: var(--warm); font-family: 'Jost', sans-serif; font-size: 15px; border-radius: 10px; transition: border-color 0.2s; }
.form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: var(--walnut); }
.btn-submit { background: var(--walnut); color: #fff; border: none; padding: 16px 36px; font-size: 13px; letter-spacing: 0.15em; text-transform: uppercase; cursor: pointer; border-radius: 40px; transition: background 0.2s; width: 100%; font-weight: 500; }
.btn-submit:hover { background: var(--walnut-l); }
.success-message { color: var(--green); margin-bottom: 24px; padding: 14px; background: rgba(74,124,89,0.1); border-radius: 10px; text-align: center; font-size: 14px; }
.error-message { color: var(--red); margin-bottom: 24px; padding: 14px; background: rgba(176,64,64,0.1); border-radius: 10px; text-align: center; font-size: 14px; }

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
@media (max-width: 1000px) {
    .benefits-grid { grid-template-columns: repeat(2, 1fr); gap: 25px; }
    .partnership-form { grid-template-columns: 1fr; padding: 35px; gap: 40px; }
}
@media (max-width: 900px) {
    .designers-page { padding-left: 20px; padding-right: 20px; }
}
@media (max-width: 768px) {
    .nav { display: none; }
    .burger-menu { display: flex; }
    .designers-page { padding-top: 80px; }
    .benefits-grid { gap: 20px; }
    .benefit-card { padding: 25px 20px; }
    .benefit-icon { font-size: 36px; }
    .benefit-title { font-size: 20px; }
}
@media (max-width: 600px) {
    .logo { font-size: 20px; }
    .profile-link { display: none; }
    .cart-btn span:not(.cart-count) { display: none; }
    .search-wrap { display: none; }
    .benefits-grid { grid-template-columns: 1fr; }
    .designers-hero h1 { font-size: 32px; }
    .form-title { font-size: 28px; }
    .partnership-form { padding: 25px; }
}
@media (max-width: 480px) {
    .hinner { padding: 0 14px; }
    .cart-count { width: 22px; height: 22px; font-size: 12px; }
    .designers-page { padding-left: 14px; padding-right: 14px; }
    .btn-submit { padding: 14px 24px; font-size: 12px; }
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
      <li><a href="designers.php" class="active">Дизайнерам</a></li>
      <li><a href="about.php">О нас</a></li>
    </ul>
    
    <div class="burger-menu" id="burgerMenu">
      <span></span>
      <span></span>
      <span></span>
    </div>
    
    <div class="hactions">
      <a href="profile.php" class="profile-link" id="profileLink"><?= $userName ? htmlspecialchars($userName) : 'Войти' ?></a>
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
      <li><a href="profile.php">Личный кабинет</a></li>
      <li><a href="cart.php">Корзина</a></li>
    </ul>
  </div>
</div>

<main class="designers-page">
    <div class="designers-hero">
        <h1>Сотрудничество с <em>дизайнерами</em></h1>
        <p>Вместе мы создаём пространства, которые вдохновляют</p>
    </div>

    <div class="benefits-grid">
        <div class="benefit-card">
            <div class="benefit-icon"><i class="fas fa-trophy"></i></div>
            <h3 class="benefit-title">Индивидуальные условия</h3>
            <p class="benefit-desc">Специальные цены и условия для профессиональных дизайнеров и архитекторов</p>
        </div>
        <div class="benefit-card">
            <div class="benefit-icon"><i class="fas fa-drafting-compass"></i></div>
            <h3 class="benefit-title">Техническая поддержка</h3>
            <p class="benefit-desc">Помощь в подборе, 3D-модели, чертежи и спецификации</p>
        </div>
        <div class="benefit-card">
            <div class="benefit-icon"><i class="fas fa-couch"></i></div>
            <h3 class="benefit-title">Бесплатная доставка</h3>
            <p class="benefit-desc">Доставка образцов и заказов по Орлу и области</p>
        </div>
        <div class="benefit-card">
            <div class="benefit-icon"><i class="fas fa-camera"></i></div>
            <h3 class="benefit-title">Портфолио</h3>
            <p class="benefit-desc">Публикация ваших проектов на нашем сайте и в соцсетях</p>
        </div>
    </div>

    <div class="partnership-form">
        <div>
            <h2 class="form-title">Стать партнёром</h2>
            <p style="color: var(--mid); margin-bottom: 24px; font-size: 15px;">Заполните форму, и наш менеджер свяжется с вами в течение 24 часов</p>
            <div style="margin-top: 32px;">
                <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; margin-bottom: 18px; font-weight: 500;">Или свяжитесь с нами напрямую:</h3>
                <p style="margin-bottom: 8px;"><a href="mailto:design@planeta-mebeli.ru" style="color: var(--walnut); text-decoration: none; font-size: 16px;">design@planeta-mebeli.ru</a></p>
                <p><a href="tel:+74862234567" style="color: var(--walnut); text-decoration: none; font-size: 16px;">+7 (4862) 23-45-67</a></p>
            </div>
        </div>
        <form method="POST" id="designerForm">
            <?php if ($success): ?>
            <div class="success-message"><i class="fas fa-check-circle"></i> ✓ Заявка отправлена! Мы свяжемся с вами в ближайшее время.</div>
            <?php endif; ?>
            <?php if ($error && !$success): ?>
            <div class="error-message"><i class="fas fa-exclamation-triangle"></i> Ошибка! Проверьте правильность заполнения формы.</div>
            <?php endif; ?>
            <div class="form-group">
                <label>Ваше имя *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Компания / Студия</label>
                <input type="text" name="company" value="<?= htmlspecialchars($_POST['company'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Телефон</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Тип сотрудничества</label>
                <select name="request_type">
                    <option value="">Выберите...</option>
                    <option value="interior" <?= ($_POST['request_type'] ?? '') == 'interior' ? 'selected' : '' ?>>Интерьер-дизайнер</option>
                    <option value="architect" <?= ($_POST['request_type'] ?? '') == 'architect' ? 'selected' : '' ?>>Архитектор</option>
                    <option value="studio" <?= ($_POST['request_type'] ?? '') == 'studio' ? 'selected' : '' ?>>Дизайн-студия</option>
                    <option value="other" <?= ($_POST['request_type'] ?? '') == 'other' ? 'selected' : '' ?>>Другое</option>
                </select>
            </div>
            <div class="form-group">
                <label>Комментарий</label>
                <textarea name="message" rows="3" placeholder="Расскажите о себе и ваших проектах..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn-submit">Отправить заявку</button>
        </form>
    </div>
</main>

<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div>
        <div class="footer-logo">Планета <span>Мебели</span></div>
        <p style="font-size: 13px;">Мебель для вашего дома</p>
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

const getCart = () => JSON.parse(localStorage.getItem('pm_cart')||'[]');
const saveCart = c => localStorage.setItem('pm_cart',JSON.stringify(c));
const getFavs = () => JSON.parse(localStorage.getItem('pm_favs')||'[]');
const saveFavs = f => localStorage.setItem('pm_favs',JSON.stringify(f));

function updateCartCount(){
  const n = getCart().reduce((s,i)=>s+i.quantity,0);
  const el = document.getElementById('cartCount');
  if(el) el.textContent = n;
}

function showToast(msg){
  const t=document.getElementById('toast'); 
  const m=document.getElementById('toastMsg');
  if(m) m.textContent=msg;
  if(t){ t.classList.add('show'); clearTimeout(t._tm); t._tm=setTimeout(()=>t.classList.remove('show'),3000); }
}

// Валидация формы
document.getElementById('designerForm')?.addEventListener('submit', function(e) {
    const name = this.querySelector('input[name="name"]').value.trim();
    const email = this.querySelector('input[name="email"]').value.trim();
    
    if (!name || !email) {
        e.preventDefault();
        showToast('Заполните обязательные поля (Имя и Email)');
        return false;
    }
    
    if (!email.includes('@') || !email.includes('.')) {
        e.preventDefault();
        showToast('Введите корректный email');
        return false;
    }
});

// Подписка
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
  showToast('Вы успешно подписались на новости!');
});

document.addEventListener('DOMContentLoaded', () => {
  updateCartCount();
  const user=JSON.parse(localStorage.getItem('pm_user')||'null');
  const pl=document.getElementById('profileLink');
  if(pl && user) pl.textContent = user.name.split(' ')[0];
});
</script>
</body>
</html>
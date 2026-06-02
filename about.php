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
<title>О компании — Планета Мебели</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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

/* ABOUT PAGE */
.about-page { padding-top: 90px; max-width: 1440px; margin: 0 auto; padding-left: 24px; padding-right: 24px; width: 100%; }
.about-hero { text-align: center; margin-bottom: 60px; }
.about-hero h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(38px, 5vw, 56px); font-weight: 400; margin-bottom: 20px; }
.about-hero h1 em { font-style: italic; color: var(--walnut); }
.about-hero p { color: var(--mid); max-width: 700px; margin: 0 auto; font-size: 16px; line-height: 1.6; }

/* MISSION SECTION */
.mission-section { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; margin-bottom: 60px; align-items: center; }
.mission-text h2 { font-family: 'Cormorant Garamond', serif; font-size: clamp(32px, 4vw, 42px); font-weight: 400; margin-bottom: 28px; }
.mission-text p { color: var(--mid); line-height: 1.8; margin-bottom: 18px; font-size: 16px; }
.mission-img { width: 100%; aspect-ratio: 4/3; border-radius: 20px; overflow: hidden; }
.mission-img img { width: 100%; height: 100%; object-fit: cover; }

/* VALUES GRID */
.values-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; margin-bottom: 60px; }
.value-card { text-align: center; padding: 36px; background: var(--cream); border-radius: 20px; transition: transform 0.3s; }
.value-card:hover { transform: translateY(-6px); }
.value-icon { font-size: 48px; margin-bottom: 20px; color: var(--walnut); }
.value-title { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 500; margin-bottom: 14px; }
.value-desc { font-size: 14px; color: var(--mid); line-height: 1.6; }

/* STATS GRID */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; margin-bottom: 60px; background: var(--cream); padding: 56px 48px; text-align: center; border-radius: 24px; }
.stat-number { font-family: 'Cormorant Garamond', serif; font-size: 56px; font-weight: 500; color: var(--walnut); }
.stat-label { font-size: 13px; color: var(--mid); text-transform: uppercase; letter-spacing: 0.1em; font-weight: 500; }

/* TEAM GRID */
.team-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; margin-bottom: 60px; }
.team-card { text-align: center; transition: transform 0.3s; }
.team-card:hover { transform: translateY(-6px); }
.team-photo { width: 100%; aspect-ratio: 1; background-size: cover; background-position: center; border-radius: 50%; margin-bottom: 18px; }
.team-name { font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 500; margin-bottom: 6px; }
.team-role { font-size: 13px; color: var(--mid); }

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
@media (max-width: 900px) {
    .mission-section { grid-template-columns: 1fr; gap: 40px; }
    .values-grid { grid-template-columns: 1fr; gap: 25px; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 25px; padding: 40px 30px; }
    .team-grid { grid-template-columns: repeat(2, 1fr); gap: 30px; }
    .about-page { padding-left: 20px; padding-right: 20px; }
}
@media (max-width: 768px) {
    .nav { display: none; }
    .burger-menu { display: flex; }
    .about-page { padding-top: 80px; }
    .about-hero { margin-bottom: 40px; }
    .mission-text h2 { font-size: 32px; }
    .stat-number { font-size: 42px; }
}
@media (max-width: 600px) {
    .logo { font-size: 20px; }
    .profile-link { display: none; }
    .cart-btn span:not(.cart-count) { display: none; }
    .search-wrap { display: none; }
    .stats-grid { grid-template-columns: 1fr; gap: 20px; }
    .team-grid { grid-template-columns: 1fr; gap: 25px; }
    .about-hero h1 { font-size: 32px; }
    .mission-text p { font-size: 14px; }
    .value-card { padding: 28px; }
}
@media (max-width: 480px) {
    .hinner { padding: 0 14px; }
    .cart-count { width: 22px; height: 22px; font-size: 12px; }
    .about-page { padding-left: 14px; padding-right: 14px; }
    .mission-section { gap: 25px; }
    .values-grid { gap: 20px; }
    .value-card { padding: 22px; }
    .stats-grid { padding: 30px 20px; }
    .stat-number { font-size: 36px; }
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
      <li><a href="about.php" class="active">О нас</a></li>
    </ul>
    
    <!-- Бургер-меню для мобильных -->
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

<main class="about-page">
    <div class="about-hero">
        <h1>О <em>компании</em></h1>
        <p>С 2015 года создаём мебель, которая становится частью семейных историй</p>
    </div>

    <div class="mission-section">
        <div class="mission-text">
            <h2>Наша миссия</h2>
            <p>Мы верим, что качественная мебель — это инвестиция в комфорт и уют вашего дома. «Планета Мебели» создаёт изделия, которые радуют долгие годы, сочетая безупречное качество, стильный дизайн и доступные цены.</p>
            <p>Каждый наш проект начинается с изучения потребностей современного человека: мы создаём не просто мебель, а продуманные решения для жизни, работы и отдыха.</p>
        </div>
        <div class="mission-img">
            <img src="/uploads/mebel.jpg" alt="Наша мастерская" onerror="this.src='/uploads/no-image.webp'">
        </div>
    </div>

    <div class="values-grid">
        <div class="value-card">
            <div class="value-icon"><i class="fas fa-tree"></i></div>
            <h3 class="value-title">Натуральные материалы</h3>
            <p class="value-desc">Используем только сертифицированную древесину, экологичные краски и безопасные ткани</p>
        </div>
        <div class="value-card">
            <div class="value-icon"><i class="fas fa-hammer"></i></div>
            <h3 class="value-title">Ручная работа</h3>
            <p class="value-desc">Каждое изделие проходит контроль качества на всех этапах производства</p>
        </div>
        <div class="value-card">
            <div class="value-icon"><i class="fas fa-palette"></i></div>
            <h3 class="value-title">Индивидуальный подход</h3>
            <p class="value-desc">Изготовим мебель по вашим размерам и дизайн-проекту</p>
        </div>
    </div>

    <div class="stats-grid">
        <div>
            <div class="stat-number">10+</div>
            <div class="stat-label">лет на рынке</div>
        </div>
        <div>
            <div class="stat-number">15 000+</div>
            <div class="stat-label">счастливых клиентов</div>
        </div>
        <div>
            <div class="stat-number">250+</div>
            <div class="stat-label">моделей мебели</div>
        </div>
        <div>
            <div class="stat-number">24ч</div>
            <div class="stat-label">сборка заказа</div>
        </div>
    </div>
</main>

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

document.addEventListener('DOMContentLoaded', () => {
  updateCartCount();
  const user=JSON.parse(localStorage.getItem('pm_user')||'null');
  const pl=document.getElementById('profileLink');
  if(pl && user) pl.textContent = user.name.split(' ')[0];
});
</script>
</body>
</html>
<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

$pageTitle = 'Админ-панель';

// Статистика
$productsCount = db()->query("SELECT COUNT(*) as count FROM products")->fetch()['count'];
$ordersCount = db()->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
$messagesCount = db()->query("SELECT COUNT(*) as count FROM messages WHERE is_read = 0")->fetch()['count'];
$designerCount = db()->query("SELECT COUNT(*) as count FROM designer_applications WHERE is_read = 0")->fetch()['count'];

$recentOrders = db()->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Админ-панель — <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            --red: #B04040;
            --blue: #4A7CA0;
            --orange: #E08D3C;
        }
        body { font-family: 'Jost', sans-serif; background: #f5f5f5; color: var(--charcoal); font-weight: 400; font-size: 17px; line-height: 1.5; }
        
        /* Админ хедер */
        .admin-header { background: var(--charcoal); color: #fff; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; position: fixed; top: 0; left: 0; right: 0; z-index: 200; height: 70px; }
        .admin-header a { color: #fff; text-decoration: none; margin-left: 20px; transition: opacity 0.2s; }
        .admin-header a:hover { opacity: 0.8; }
        .burger-menu { display: none; background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; }
        
        /* Контейнер */
        .admin-container { display: flex; margin-top: 70px; min-height: calc(100vh - 70px); }
        
        /* Сайдбар */
        .admin-sidebar { width: 280px; background: #fff; border-right: 1px solid var(--line); padding: 24px; position: fixed; height: calc(100vh - 70px); overflow-y: auto; transition: transform 0.3s ease; z-index: 100; }
        .admin-sidebar a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: var(--charcoal); text-decoration: none; margin-bottom: 8px; border-radius: 12px; transition: all 0.2s; font-size: 15px; font-weight: 500; }
        .admin-sidebar a i { width: 22px; font-size: 16px; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: var(--walnut); color: #fff; }
        
        /* Основной контент */
        .admin-content { flex: 1; margin-left: 280px; padding: 32px; }
        
        /* Сетка статистики */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 48px; }
        .stat-card { background: #fff; padding: 28px 20px; border-radius: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid var(--line); transition: transform 0.2s, box-shadow 0.2s; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
        .stat-icon { font-size: 42px; margin-bottom: 12px; }
        .stat-number { font-size: 36px; font-weight: 700; color: var(--walnut); font-family: 'Cormorant Garamond', serif; }
        .stat-label { font-size: 13px; color: var(--mid); margin-top: 8px; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 500; }
        
        /* Таблица */
        .recent-table { width: 100%; background: #fff; border-radius: 20px; overflow-x: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid var(--line); }
        .recent-table table { width: 100%; border-collapse: collapse; min-width: 600px; }
        .recent-table th, .recent-table td { padding: 16px 20px; text-align: left; border-bottom: 1px solid var(--line); font-size: 14px; }
        .recent-table th { background: var(--cream); font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--mid); }
        .recent-table tr:hover { background: rgba(245,240,232,0.3); }
        
        /* Бейджи статусов */
        .status-badge { display: inline-block; padding: 6px 14px; border-radius: 30px; font-size: 12px; font-weight: 500; }
        .status-new { background: rgba(74,124,160,0.1); color: var(--blue); }
        .status-processing { background: rgba(224,141,60,0.1); color: var(--orange); }
        .status-delivered { background: rgba(74,124,89,0.1); color: var(--green); }
        .status-cancelled { background: rgba(176,64,64,0.1); color: var(--red); }
        
        /* Кнопки */
        .btn { display: inline-block; padding: 8px 16px; background: var(--walnut); color: #fff; text-decoration: none; border-radius: 30px; font-size: 12px; font-weight: 500; transition: background 0.2s; }
        .btn:hover { background: var(--walnut-l); }
        
        h2 { margin-bottom: 20px; font-size: 28px; font-weight: 400; font-family: 'Cormorant Garamond', serif; display: flex; align-items: center; gap: 10px; }
        
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-avatar { width: 36px; height: 36px; background: var(--walnut); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 500; font-size: 16px; }
        
        /* Адаптивность */
        @media (max-width: 992px) {
            .admin-content { padding: 24px; }
            .stats-grid { gap: 16px; }
            .stat-card { padding: 20px 16px; }
            .stat-number { font-size: 30px; }
            .recent-table th, .recent-table td { padding: 12px 16px; }
        }
        
        @media (max-width: 768px) {
            .burger-menu { display: block; }
            .admin-sidebar { transform: translateX(-100%); position: fixed; top: 70px; left: 0; width: 280px; height: calc(100vh - 70px); z-index: 200; }
            .admin-sidebar.mobile-open { transform: translateX(0); }
            .admin-content { margin-left: 0; padding: 20px; }
            .admin-header { padding: 12px 20px; height: 65px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 32px; }
            h2 { font-size: 24px; }
            .user-avatar { width: 32px; height: 32px; font-size: 14px; }
        }
        
        @media (max-width: 600px) {
            .admin-content { padding: 16px; }
            .stats-grid { gap: 12px; }
            .stat-card { padding: 16px 12px; }
            .stat-icon { font-size: 32px; }
            .stat-number { font-size: 26px; }
            .stat-label { font-size: 11px; }
            .recent-table th, .recent-table td { padding: 10px 12px; font-size: 12px; }
            h2 { font-size: 22px; margin-bottom: 16px; }
            .btn { padding: 6px 12px; font-size: 11px; }
        }
        
        @media (max-width: 480px) {
            .admin-header > div:first-child { font-size: 16px; }
            .user-info span { display: none; }
            .admin-header a { margin-left: 10px; }
            .stats-grid { grid-template-columns: 1fr; }
            .recent-table { border-radius: 16px; }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <button class="burger-menu" id="burgerMenu"><i class="fas fa-bars"></i></button>
            <div style="font-size: 20px; font-weight: 600;">Планета Мебели — Админ-панель</div>
        </div>
        <div class="user-info">
            <div class="user-avatar">
                <?= mb_substr($_SESSION['user_name'] ?? 'А', 0, 1) ?>
            </div>
            <span>Здравствуйте, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Администратор') ?></span>
            <a href="/admin/logout.php"><i class="fas fa-sign-out-alt"></i> Выйти</a>
        </div>
    </div>
    
    <div class="admin-container">
        <div class="admin-sidebar" id="sidebar">
            <a href="/admin/index.php" class="active">Главная</a>
            <a href="/admin/products.php">Товары</a>
            <a href="/admin/orders.php">Заказы</a>
            <a href="/admin/messages.php">Сообщения</a>
        </div>
        
        <div class="admin-content">
            <h2>Статистика</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-couch" style="color: var(--walnut);"></i></div>
                    <div class="stat-number"><?= $productsCount ?></div>
                    <div class="stat-label">Товаров</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-shopping-bag" style="color: var(--blue);"></i></div>
                    <div class="stat-number"><?= $ordersCount ?></div>
                    <div class="stat-label">Заказов</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-envelope" style="color: var(--orange);"></i></div>
                    <div class="stat-number"><?= $messagesCount ?></div>
                    <div class="stat-label">Новых сообщений</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-palette" style="color: var(--green);"></i></div>
                    <div class="stat-number"><?= $designerCount ?></div>
                    <div class="stat-label">Заявок дизайнеров</div>
                </div>
            </div>
            
            <h2>Последние заказы</h2>
            <div class="recent-table">
                <table>
                    <thead>
                        <tr>
                            <th>№ заказа</th>
                            <th>Клиент</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($order['order_number']) ?></strong></td>
                            <td>
                                <i class="fas fa-user" style="color: var(--walnut);"></i> <?= htmlspecialchars($order['fullname']) ?><br>
                                <small style="color: var(--mid);"><i class="fas fa-phone"></i> <?= htmlspecialchars($order['phone']) ?></small>
                            </td>
                            <td><?= formatPrice($order['total']) ?></td>
                            <td>
                                <?php
                                $statusClass = '';
                                $statusText = '';
                                switch ($order['status']) {
                                    case 'new': $statusClass = 'status-new'; $statusText = 'Новый'; break;
                                    case 'processing': $statusClass = 'status-processing'; $statusText = 'В обработке'; break;
                                    case 'delivered': $statusClass = 'status-delivered'; $statusText = 'Доставлен'; break;
                                    case 'cancelled': $statusClass = 'status-cancelled'; $statusText = 'Отменён'; break;
                                    default: $statusClass = 'status-new'; $statusText = 'Новый';
                                }
                                ?>
                                <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td><?= date('d.m.Y', strtotime($order['created_at'])) ?></td>
                            <td><a href="/admin/orders.php" class="btn"><i class="fas fa-eye"></i> Подробнее</a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($recentOrders) === 0): ?>
                        <tr><td colspan="6" style="text-align: center;">Нет заказов</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
    // Бургер-меню
    const burgerMenu = document.getElementById('burgerMenu');
    const sidebar = document.getElementById('sidebar');
    
    burgerMenu?.addEventListener('click', function() {
        sidebar.classList.toggle('mobile-open');
    });
    
    // Закрытие сайдбара при клике вне его на мобильных
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (sidebar && !sidebar.contains(e.target) && burgerMenu && !burgerMenu.contains(e.target)) {
                sidebar.classList.remove('mobile-open');
            }
        }
    });
    </script>
</body>
</html>
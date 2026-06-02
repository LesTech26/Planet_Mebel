<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

// Обновление статуса
if (isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];
    db()->prepare("UPDATE orders SET status = :status WHERE id = :id")->execute(['status' => $status, 'id' => $orderId]);
    header('Location: orders.php?updated=1');
    exit;
}

$orders = db()->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();
$orderItems = [];
foreach ($orders as $order) {
    $items = db()->query("SELECT * FROM order_items WHERE order_id = :order_id", ['order_id' => $order['id']])->fetchAll();
    $orderItems[$order['id']] = $items;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Заказы — Админ-панель</title>
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
        
        /* Таблицы */
        .orders-table { background: #fff; border-radius: 20px; overflow-x: auto; margin-top: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid var(--line); }
        .orders-table table { width: 100%; border-collapse: collapse; min-width: 1000px; }
        .orders-table th, .orders-table td { padding: 16px 20px; text-align: left; border-bottom: 1px solid var(--line); font-size: 14px; vertical-align: top; }
        .orders-table th { background: var(--cream); font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--mid); }
        
        /* Бейджи статусов */
        .status-badge { display: inline-block; padding: 6px 14px; border-radius: 30px; font-size: 12px; font-weight: 500; }
        .status-new { background: rgba(74,124,160,0.1); color: var(--blue); }
        .status-processing { background: rgba(224,141,60,0.1); color: var(--orange); }
        .status-delivered { background: rgba(74,124,89,0.1); color: var(--green); }
        .status-cancelled { background: rgba(176,64,64,0.1); color: var(--red); }
        
        /* Селект статуса */
        .status-select { padding: 8px 14px; border-radius: 30px; border: 1px solid var(--line); font-family: 'Jost', sans-serif; font-size: 13px; background: #fff; cursor: pointer; transition: all 0.2s; }
        .status-select:focus { outline: none; border-color: var(--walnut); }
        
        /* Кнопки */
        .btn { display: inline-block; padding: 8px 16px; background: var(--walnut); color: #fff; text-decoration: none; border-radius: 30px; font-size: 12px; font-weight: 500; border: none; cursor: pointer; transition: background 0.2s; }
        .btn:hover { background: var(--walnut-l); }
        .btn-detail { background: var(--blue); }
        .btn-detail:hover { background: #3a6a8a; }
        
        h2 { margin-bottom: 20px; font-size: 28px; font-weight: 400; font-family: 'Cormorant Garamond', serif; display: flex; align-items: center; gap: 10px; }
        
        .order-items { font-size: 13px; color: var(--mid); line-height: 1.6; }
        .order-customer { line-height: 1.6; }
        .order-customer small { font-size: 12px; color: var(--mid); display: block; margin-top: 4px; }
        
        /* Модальное окно */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: #fff; border-radius: 24px; max-width: 550px; width: 90%; max-height: 85vh; overflow-y: auto; padding: 32px; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--line); }
        .modal-header h3 { font-size: 24px; font-weight: 500; font-family: 'Cormorant Garamond', serif; }
        .modal-close { background: none; border: none; font-size: 28px; cursor: pointer; color: var(--mid); transition: color 0.2s; }
        .modal-close:hover { color: var(--red); }
        .detail-row { margin-bottom: 16px; }
        .detail-label { font-weight: 600; color: var(--mid); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .detail-value { font-size: 16px; color: var(--charcoal); }
        .items-list { list-style: none; margin-top: 8px; }
        .items-list li { padding: 8px 0; border-bottom: 1px solid var(--line); font-size: 14px; }
        
        /* Уведомление */
        .success-message { position: fixed; top: 85px; right: 24px; background: var(--green); color: #fff; padding: 14px 24px; border-radius: 50px; z-index: 1001; display: none; font-size: 14px; font-weight: 500; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .success-message.show { display: block; animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
        
        /* Адаптивность */
        @media (max-width: 992px) {
            .admin-content { padding: 24px; }
            .orders-table th, .orders-table td { padding: 12px 16px; }
        }
        
        @media (max-width: 768px) {
            .burger-menu { display: block; }
            .admin-sidebar { transform: translateX(-100%); position: fixed; top: 70px; left: 0; width: 280px; height: calc(100vh - 70px); z-index: 200; }
            .admin-sidebar.mobile-open { transform: translateX(0); }
            .admin-content { margin-left: 0; padding: 20px; }
            .admin-header { padding: 12px 20px; height: 65px; }
            h2 { font-size: 24px; }
            .modal-content { padding: 24px; }
            .modal-header h3 { font-size: 22px; }
        }
        
        @media (max-width: 600px) {
            .admin-content { padding: 16px; }
            .orders-table th, .orders-table td { padding: 10px 12px; font-size: 12px; }
            h2 { font-size: 22px; margin-bottom: 16px; }
            .btn { padding: 6px 12px; font-size: 11px; }
            .status-select { padding: 6px 10px; font-size: 11px; }
            .order-items { font-size: 11px; }
            .order-customer small { font-size: 10px; }
            .detail-value { font-size: 14px; }
            .items-list li { font-size: 12px; }
        }
        
        @media (max-width: 480px) {
            .admin-header > div:first-child { font-size: 16px; }
            .admin-header a { margin-left: 12px; font-size: 14px; }
            .orders-table { border-radius: 16px; }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <button class="burger-menu" id="burgerMenu"><i class="fas fa-bars"></i></button>
            <div style="font-size: 20px; font-weight: 600;">Планета Мебели — Админ-панель</div>
        </div>
        <div><a href="/admin/logout.php"><i class="fas fa-sign-out-alt"></i> Выйти</a></div>
    </div>
    
    <div class="admin-container">
        <div class="admin-sidebar" id="sidebar">
            <a href="/admin/index.php">Главная</a>
            <a href="/admin/products.php">Товары</a>
            <a href="/admin/orders.php" class="active">Заказы</a>
            <a href="/admin/messages.php">Сообщения</a>
        </div>
        
        <div class="admin-content">
            <h2><i class="fas fa-shopping-bag" style="color: var(--walnut);"></i> Заказы</h2>
            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>№ заказа</th>
                            <th>Клиент</th>
                            <th>Товары</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($order['order_number']) ?></strong></td>
                            <td class="order-customer">
                                <i class="fas fa-user" style="color: var(--walnut);"></i> <?= htmlspecialchars($order['fullname']) ?><br>
                                <small><i class="fas fa-phone"></i> <?= htmlspecialchars($order['phone']) ?></small><br>
                                <small><i class="fas fa-envelope"></i> <?= htmlspecialchars($order['email']) ?></small>
                             </td>
                            <td class="order-items">
                                <?php foreach ($orderItems[$order['id']] as $item): ?>
                                    • <?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?><br>
                                <?php endforeach; ?>
                             </td>
                            <td><?= formatPrice($order['total']) ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="new" <?= $order['status'] == 'new' ? 'selected' : '' ?>>Новый</option>
                                        <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>В обработке</option>
                                        <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Доставлен</option>
                                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Отменён</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                                <?php
                                $statusClass = '';
                                switch ($order['status']) {
                                    case 'new': $statusClass = 'status-new'; break;
                                    case 'processing': $statusClass = 'status-processing'; break;
                                    case 'delivered': $statusClass = 'status-delivered'; break;
                                    case 'cancelled': $statusClass = 'status-cancelled'; break;
                                }
                                ?>
                                <span class="status-badge <?= $statusClass ?>" style="margin-top: 8px; display: inline-block;">
                                    <?= $order['status'] == 'new' ? 'Новый' : ($order['status'] == 'processing' ? 'В обработке' : ($order['status'] == 'delivered' ? 'Доставлен' : 'Отменён')) ?>
                                </span>
                             </td>
                            <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-detail" onclick='showOrderDetails(<?= htmlspecialchars(json_encode($order, JSON_HEX_TAG)) ?>, <?= htmlspecialchars(json_encode($orderItems[$order['id']], JSON_HEX_TAG)) ?>)'>
                                    <i class="fas fa-eye"></i> Детали
                                </button>
                             </td>
                         </tr>
                        <?php endforeach; ?>
                        <?php if (count($orders) === 0): ?>
                        <tr><td colspan="7" style="text-align: center;">Нет заказов</td></tr>
                        <?php endif; ?>
                    </tbody>
                 </table>
            </div>
        </div>
    </div>
    
    <!-- Модальное окно деталей заказа -->
    <div class="modal" id="orderModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-shopping-bag" style="color: var(--walnut);"></i> <span id="modalOrderNumber"></span></h3>
                <button class="modal-close" onclick="closeOrderModal()">&times;</button>
            </div>
            <div id="modalOrderContent"></div>
        </div>
    </div>
    
    <!-- Уведомление об успехе -->
    <div class="success-message" id="successMessage">
        <i class="fas fa-check-circle"></i> <span id="successText"></span>
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
    
    function showOrderDetails(order, items) {
        const modal = document.getElementById('orderModal');
        
        const deliveryMap = {
            'pickup': 'Самовывоз',
            'courier': 'Курьерская доставка'
        };
        
        const paymentMap = {
            'card': 'Карта онлайн',
            'cash': 'Наличные при получении',
            'bank': 'Безналичный расчёт'
        };
        
        const statusMap = {
            'new': 'Новый',
            'processing': 'В обработке',
            'delivered': 'Доставлен',
            'cancelled': 'Отменён'
        };
        
        let itemsHtml = '';
        for (let item of items) {
            itemsHtml += `<li><i class="fas fa-tag" style="color: var(--walnut);"></i> ${escapeHtml(item.product_name)} × ${item.quantity} — ${formatPrice(item.price)}</li>`;
        }
        
        let html = `
            <div class="detail-row">
                <div class="detail-label">Клиент</div>
                <div class="detail-value">${escapeHtml(order.fullname)}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Телефон</div>
                <div class="detail-value"><a href="tel:${escapeHtml(order.phone)}">${escapeHtml(order.phone)}</a></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Email</div>
                <div class="detail-value"><a href="mailto:${escapeHtml(order.email)}">${escapeHtml(order.email)}</a></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Адрес доставки</div>
                <div class="detail-value">${escapeHtml(order.address || '—')}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Способ доставки</div>
                <div class="detail-value">${deliveryMap[order.delivery_method] || order.delivery_method || '—'}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Способ оплаты</div>
                <div class="detail-value">${paymentMap[order.payment_method] || order.payment_method || '—'}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Комментарий к заказу</div>
                <div class="detail-value">${escapeHtml(order.comment || '—')}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Статус</div>
                <div class="detail-value">${statusMap[order.status] || order.status}</div>
            </div>
            <hr style="margin: 16px 0; border-color: var(--line);">
            <div class="detail-row">
                <div class="detail-label">Товары в заказе</div>
                <ul class="items-list">${itemsHtml}</ul>
            </div>
            <hr style="margin: 16px 0; border-color: var(--line);">
            <div class="detail-row">
                <div class="detail-label">Итого к оплате</div>
                <div class="detail-value" style="font-size: 20px; font-weight: 600; color: var(--walnut);">${formatPrice(order.total)}</div>
            </div>
        `;
        
        document.getElementById('modalOrderNumber').innerText = 'Заказ ' + order.order_number;
        document.getElementById('modalOrderContent').innerHTML = html;
        modal.classList.add('active');
    }
    
    function formatPrice(price) {
        return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
    }
    
    function closeOrderModal() {
        document.getElementById('orderModal').classList.remove('active');
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
    
    // Закрытие модального окна по клику вне его
    document.getElementById('orderModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeOrderModal();
    });
    
    // Показ сообщения об успехе из URL
    const urlParams = new URLSearchParams(window.location.search);
    const successMsg = urlParams.get('updated');
    if (successMsg) {
        const el = document.getElementById('successMessage');
        document.getElementById('successText').textContent = 'Статус заказа обновлён!';
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), 3000);
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    </script>
</body>
</html>
<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

// Отметить сообщение как прочитанное
if (isset($_GET['read'])) {
    $id = (int)$_GET['read'];
    db()->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([$id]);
    header('Location: messages.php');
    exit;
}

// Удалить сообщение
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    db()->prepare("DELETE FROM messages WHERE id = ?")->execute([$id]);
    header('Location: messages.php');
    exit;
}

// Отметить заявку дизайнера как прочитанную
if (isset($_GET['read_designer'])) {
    $id = (int)$_GET['read_designer'];
    db()->prepare("UPDATE designer_applications SET is_read = 1 WHERE id = ?")->execute([$id]);
    header('Location: messages.php');
    exit;
}

// Удалить заявку дизайнера
if (isset($_GET['delete_designer'])) {
    $id = (int)$_GET['delete_designer'];
    db()->prepare("DELETE FROM designer_applications WHERE id = ?")->execute([$id]);
    header('Location: messages.php');
    exit;
}

// Обновить статус заявки
if (isset($_POST['update_status']) && isset($_POST['application_id']) && isset($_POST['status'])) {
    $id = (int)$_POST['application_id'];
    $status = $_POST['status'];
    db()->prepare("UPDATE designer_applications SET status = ?, updated_at = NOW() WHERE id = ?")->execute([$status, $id]);
    header('Location: messages.php?success=Статус обновлён');
    exit;
}

// Получение списка сообщений
$messages = db()->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();

// Получение списка заявок дизайнеров из таблицы designer_applications
$designerRequests = db()->query("SELECT * FROM designer_applications ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Сообщения — Админ-панель</title>
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
        .admin-header a { color: #fff; text-decoration: none; margin-left: 20px; }
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
        .messages-table { background: #fff; border-radius: 20px; overflow-x: auto; margin-top: 24px; margin-bottom: 48px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid var(--line); }
        .messages-table table { width: 100%; border-collapse: collapse; min-width: 900px; }
        .messages-table th, .messages-table td { padding: 16px 20px; text-align: left; border-bottom: 1px solid var(--line); font-size: 14px; }
        .messages-table th { background: var(--cream); font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--mid); }
        .unread { background: #fff8e7; }
        
        /* Кнопки */
        .btn { display: inline-block; padding: 8px 14px; background: var(--walnut); color: #fff; text-decoration: none; border-radius: 30px; font-size: 12px; font-weight: 500; margin-right: 6px; border: none; cursor: pointer; transition: background 0.2s; }
        .btn:hover { background: var(--walnut-l); }
        .btn-view { background: var(--blue); }
        .btn-view:hover { background: #3a6a8a; }
        .btn-danger { background: var(--red); }
        .btn-danger:hover { background: #8a3030; }
        
        h2 { margin-bottom: 20px; font-size: 28px; font-weight: 400; font-family: 'Cormorant Garamond', serif; margin-top: 20px; }
        h2:first-of-type { margin-top: 0; }
        
        /* Бейджи статусов */
        .status-badge { display: inline-block; padding: 6px 14px; border-radius: 30px; font-size: 12px; font-weight: 500; }
        .status-new { background: rgba(74,124,160,0.1); color: var(--blue); }
        .status-in_progress { background: rgba(224,141,60,0.1); color: var(--orange); }
        .status-completed { background: rgba(74,124,89,0.1); color: var(--green); }
        .status-rejected { background: rgba(176,64,64,0.1); color: var(--red); }
        
        /* Модальное окно */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: #fff; border-radius: 24px; max-width: 600px; width: 90%; max-height: 85vh; overflow-y: auto; padding: 32px; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--line); }
        .modal-header h3 { font-size: 24px; font-weight: 500; font-family: 'Cormorant Garamond', serif; }
        .modal-close { background: none; border: none; font-size: 28px; cursor: pointer; color: var(--mid); transition: color 0.2s; }
        .modal-close:hover { color: var(--red); }
        .detail-row { margin-bottom: 20px; }
        .detail-label { font-weight: 600; color: var(--mid); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .detail-value { font-size: 16px; color: var(--charcoal); line-height: 1.5; }
        .status-select { padding: 10px 16px; border: 1px solid var(--line); border-radius: 40px; font-size: 14px; margin-right: 12px; font-family: 'Jost', sans-serif; }
        .form-update { margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--line); display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        
        /* Уведомление */
        .success-message { position: fixed; top: 85px; right: 24px; background: var(--green); color: #fff; padding: 14px 24px; border-radius: 50px; z-index: 1001; display: none; font-size: 14px; font-weight: 500; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .success-message.show { display: block; animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
        
        /* Адаптивность */
        @media (max-width: 992px) {
            .admin-content { padding: 24px; }
            .messages-table th, .messages-table td { padding: 12px 16px; }
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
            .btn, .btn-view, .btn-danger { padding: 6px 12px; font-size: 11px; }
        }
        
        @media (max-width: 600px) {
            .admin-content { padding: 16px; }
            .messages-table th, .messages-table td { padding: 10px 12px; font-size: 12px; }
            h2 { font-size: 22px; margin-bottom: 16px; }
            .detail-value { font-size: 14px; }
            .status-select { padding: 8px 12px; font-size: 13px; }
            .form-update { flex-direction: column; align-items: stretch; }
            .form-update .btn { width: 100%; text-align: center; }
        }
        
        @media (max-width: 480px) {
            .admin-header > div:first-child { font-size: 16px; }
            .admin-header a { margin-left: 12px; font-size: 14px; }
            .messages-table { border-radius: 16px; }
            .messages-table th, .messages-table td { padding: 8px 10px; font-size: 11px; }
            .btn, .btn-view, .btn-danger { padding: 5px 10px; font-size: 10px; }
            .status-badge { padding: 4px 10px; font-size: 10px; }
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
            <a href="/admin/orders.php">Заказы</a>
            <a href="/admin/messages.php" class="active">Сообщения</a>
        </div>
        
        <div class="admin-content">
            <!-- Сообщения с сайта -->
            <h2><i class="fas fa-envelope" style="margin-right: 10px; color: var(--walnut);"></i> Сообщения с сайта</h2>
            <div class="messages-table">
                <table>
                    <thead>
                        <tr><th>ID</th><th>Имя</th><th>Email</th><th>Тема</th><th>Сообщение</th><th>Дата</th><th>Действия</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                        <tr class="<?= $msg['is_read'] ? '' : 'unread' ?>">
                            <td><?= $msg['id'] ?></td>
                            <td><?= htmlspecialchars($msg['name']) ?></td>
                            <td><?= htmlspecialchars($msg['email']) ?></td>
                            <td><?= htmlspecialchars($msg['subject'] ?: '—') ?></td>
                            <td style="max-width: 300px;"><?= htmlspecialchars(mb_substr($msg['message'], 0, 50)) ?>...</td>
                            <td><?= date('d.m.Y H:i', strtotime($msg['created_at'])) ?></td>
                            <td>
                                <a href="?read=<?= $msg['id'] ?>" class="btn"><i class="fas fa-check"></i> Прочитано</a>
                                <a href="?delete=<?= $msg['id'] ?>" class="btn btn-danger" onclick="return confirm('Удалить?')"><i class="fas fa-trash"></i> Удалить</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($messages) === 0): ?>
                        <tr><td colspan="7" style="text-align: center;">Нет сообщений</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Заявки от дизайнеров -->
            <h2><i class="fas fa-palette" style="margin-right: 10px; color: var(--walnut);"></i> Заявки от дизайнеров</h2>
            <div class="messages-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Компания</th>
                            <th>Email</th>
                            <th>Телефон</th>
                            <th>Тип</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($designerRequests as $req): ?>
                        <tr class="<?= $req['is_read'] ? '' : 'unread' ?>">
                            <td><?= $req['id'] ?></td>
                            <td><?= htmlspecialchars($req['name']) ?></td>
                            <td><?= htmlspecialchars($req['company'] ?: '—') ?></td>
                            <td><?= htmlspecialchars($req['email']) ?></td>
                            <td><?= htmlspecialchars($req['phone'] ?: '—') ?></td>
                            <td>
                                <?php
                                $typeMap = [
                                    'interior' => 'Интерьер-дизайнер',
                                    'architect' => 'Архитектор',
                                    'studio' => 'Дизайн-студия',
                                    'other' => 'Другое'
                                ];
                                echo htmlspecialchars($typeMap[$req['request_type']] ?? ($req['request_type'] ?: '—'));
                                ?>
                            </td>
                            <td>
                                <?php
                                $statusClass = '';
                                $statusText = '';
                                switch ($req['status']) {
                                    case 'new': $statusClass = 'status-new'; $statusText = 'Новая'; break;
                                    case 'in_progress': $statusClass = 'status-in_progress'; $statusText = 'В работе'; break;
                                    case 'completed': $statusClass = 'status-completed'; $statusText = 'Завершена'; break;
                                    case 'rejected': $statusClass = 'status-rejected'; $statusText = 'Отклонена'; break;
                                    default: $statusClass = 'status-new'; $statusText = 'Новая';
                                }
                                ?>
                                <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($req['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-view" onclick='viewDesigner(<?= json_encode($req, JSON_HEX_TAG) ?>)'> Просмотр</button>
                                <a href="?read_designer=<?= $req['id'] ?>" class="btn"><i class="fas fa-check"></i> Прочитано</a>
                                <a href="?delete_designer=<?= $req['id'] ?>" class="btn btn-danger" onclick="return confirm('Удалить заявку?')"> Удалить</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($designerRequests) === 0): ?>
                        <tr><td colspan="9" style="text-align: center;">Нет заявок от дизайнеров</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Модальное окно для просмотра заявки -->
    <div class="modal" id="designerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-palette" style="margin-right: 10px;"></i> Заявка дизайнера</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>

    <!-- Сообщение об успехе -->
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
            if (!sidebar.contains(e.target) && !burgerMenu.contains(e.target)) {
                sidebar.classList.remove('mobile-open');
            }
        }
    });
    
    function showSuccess(message) {
        const el = document.getElementById('successMessage');
        document.getElementById('successText').textContent = message;
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), 3000);
    }
    
    function viewDesigner(req) {
        const modal = document.getElementById('designerModal');
        const modalBody = document.getElementById('modalBody');
        
        const typeMap = {
            'interior': 'Интерьер-дизайнер',
            'architect': 'Архитектор',
            'studio': 'Дизайн-студия',
            'other': 'Другое'
        };
        
        const statusMap = {
            'new': 'Новая',
            'in_progress': 'В работе',
            'completed': 'Завершена',
            'rejected': 'Отклонена'
        };
        
        modalBody.innerHTML = `
            <div class="detail-row">
                <div class="detail-label">Имя</div>
                <div class="detail-value">${escapeHtml(req.name)}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Компания</div>
                <div class="detail-value">${escapeHtml(req.company || 'Не указана')}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Email</div>
                <div class="detail-value"><a href="mailto:${escapeHtml(req.email)}">${escapeHtml(req.email)}</a></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Телефон</div>
                <div class="detail-value">${escapeHtml(req.phone || 'Не указан')}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Тип сотрудничества</div>
                <div class="detail-value">${escapeHtml(typeMap[req.request_type] || req.request_type || 'Не указан')}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Сообщение</div>
                <div class="detail-value">${escapeHtml(req.message || 'Не указано')}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Статус</div>
                <div class="detail-value">${statusMap[req.status] || 'Новая'}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Дата подачи</div>
                <div class="detail-value">${new Date(req.created_at).toLocaleString('ru-RU')}</div>
            </div>
            <form method="POST" class="form-update">
                <input type="hidden" name="application_id" value="${req.id}">
                <select name="status" class="status-select">
                    <option value="new" ${req.status === 'new' ? 'selected' : ''}>Новая</option>
                    <option value="in_progress" ${req.status === 'in_progress' ? 'selected' : ''}>В работе</option>
                    <option value="completed" ${req.status === 'completed' ? 'selected' : ''}>Завершена</option>
                    <option value="rejected" ${req.status === 'rejected' ? 'selected' : ''}>Отклонена</option>
                </select>
                <button type="submit" name="update_status" class="btn"><i class="fas fa-save"></i> Обновить статус</button>
            </form>
        `;
        modal.classList.add('active');
    }
    
    function closeModal() {
        document.getElementById('designerModal').classList.remove('active');
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
    document.getElementById('designerModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    
    // Проверка наличия сообщения об успехе в URL
    const urlParams = new URLSearchParams(window.location.search);
    const successMsg = urlParams.get('success');
    if (successMsg) {
        showSuccess(successMsg);
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    </script>
</body>
</html>